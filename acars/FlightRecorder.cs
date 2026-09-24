using System.Text.Json;
using System.IO;

namespace Promethee;

public record FlightState(string Server, string PirepId, DateTimeOffset Started, double InitialFuel,
    string Phase = "OUT", DateTimeOffset? BlockOff = null, DateTimeOffset? Takeoff = null,
    DateTimeOffset? Landing = null, DateTimeOffset? BlockOn = null, double Distance = 0,
    double FuelUsed = 0, double AirborneSeconds = 0, double? LandingRate = null, bool Recording = true, string? OperationId = null)
{
    public List<FlightIssue> Issues { get; init; } = [];
    public List<FdmObservation> Observations { get; init; } = [];
    public List<PhaseEntry> Timeline { get; init; } = [];
    public List<FlightJournalEntry> Journal { get; init; } = [];
}
public record Envelope(Sample Sample, AircraftSnapshot? Snapshot = null);
public record AcarsEvent(Guid EventId, string Name, DateTimeOffset OccurredAt, double Lat, double Lon);
public record FlightIssue(DateTimeOffset OccurredAt, string Code, string Message, string Severity = "warning");
public record PhaseEntry(DateTimeOffset OccurredAt, string Name);
public record FlightJournalEntry(DateTimeOffset OccurredAt, string Name, double? Value = null);
public record AcarsRules(double TaxiSpeed = 35, double HardLandingRate = 600);
public record TrackPoint(DateTimeOffset RecordedAt, double Lat, double Lon, double Altitude);
public record FlightSummary(string PirepId, DateTimeOffset CompletedAt, double Distance, int AirborneMinutes,
    int BlockMinutes, double FuelUsed, double? LandingRate, IReadOnlyList<FlightIssue> Issues,
    IReadOnlyList<FdmObservation>? Observations = null);
public record FlightRecoveryInfo(
    string PirepId, string? OperationId, string Server, string Phase, DateTimeOffset Started,
    DateTimeOffset? LastTrackAt, double Distance, int AirborneMinutes, double FuelUsed,
    double? LandingRate, int PendingMessages, int TrackPoints);

public sealed class FlightRecorder
{
    private TimeSpan positionInterval = TimeSpan.FromSeconds(15);
    public readonly object Gate = new();
    public readonly SemaphoreSlim NetworkGate = new(1, 1);
    private readonly string folder;
    private Sample? previous;
    private AircraftSnapshot? previousSnapshot;
    private DateTimeOffset? lastQueuedAt;
    private readonly FlightTrackingEngine tracking = new();
    private readonly FlightDataMonitor fdm = new();
    private bool recoveryRequired;
    public FlightState? Flight { get; private set; }
    public List<TrackPoint> Track { get; private set; } = [];
    public List<FlightSummary> History { get; private set; } = [];
    public AcarsRules Rules { get; private set; } = new();
    public List<Envelope> Pending { get; private set; } = [];
    public List<AcarsEvent> PendingEvents { get; private set; } = [];
    public string? Warning { get; private set; }
    public RemoteAcarsConfiguration? RemoteConfiguration { get; private set; }
    public bool RecoveryAvailable { get { lock (Gate) return recoveryRequired && Flight is not null && !Flight.Recording; } }

    public FlightRecorder(string? storageFolder = null)
    {
        folder = storageFolder ?? Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "AirInter", "Promethee");
        Directory.CreateDirectory(folder);
        var rulesFile = Path.Combine(folder, "rules.json");
        try { if (File.Exists(rulesFile)) Rules = JsonSerializer.Deserialize<AcarsRules>(File.ReadAllText(rulesFile)) ?? new(); } catch (JsonException) { }
        var historyFile = Path.Combine(folder, "history.json");
        try { if (File.Exists(historyFile)) History = JsonSerializer.Deserialize<List<FlightSummary>>(File.ReadAllText(historyFile)) ?? []; }
        catch (JsonException) { Warning = "L'historique local est illisible."; }
        var file = Path.Combine(folder, "state.json");
        if (File.Exists(file)) {
            try {
                var saved = JsonSerializer.Deserialize<Saved>(File.ReadAllText(file));
                Flight = saved?.Flight; Pending = saved?.Pending ?? []; PendingEvents = saved?.PendingEvents ?? [];
                Track = saved?.Track ?? [];
                if (Flight is not null) {
                    Flight = Flight with { Recording = false };
                    recoveryRequired = true;
                }
            } catch (JsonException) {
                // A damaged local cache must not prevent the pilot from opening ACARS.
                Warning = "Le cache local est illisible. Commencez un nouveau vol après avoir vérifié le PIREP.";
            }
        }
    }

    private record Saved(FlightState? Flight, List<Envelope> Pending, List<AcarsEvent> PendingEvents, List<TrackPoint>? Track = null);
    private void SaveHistory() => File.WriteAllText(Path.Combine(folder, "history.json"), JsonSerializer.Serialize(History));
    public void SetRules(AcarsRules rules) { lock (Gate) { if (rules.TaxiSpeed is < 5 or > 100 || rules.HardLandingRate is < 100 or > 2000) throw new InvalidOperationException("Valeurs de règles invalides."); Rules = rules; File.WriteAllText(Path.Combine(folder, "rules.json"), JsonSerializer.Serialize(rules)); } }
    public void ApplyRemoteConfiguration(RemoteAcarsConfiguration configuration)
    {
        lock (Gate) {
            positionInterval = TimeSpan.FromSeconds(configuration.PositionIntervalSeconds);
            RemoteConfiguration = configuration;
        }
    }
    private void Save()
    {
        var temp = Path.Combine(folder, "state.tmp");
        using (var file = new FileStream(temp, FileMode.Create, FileAccess.Write, FileShare.None)) {
            JsonSerializer.Serialize(file, new Saved(Flight, Pending, PendingEvents, Track)); file.Flush(true);
        }
        File.Move(temp, Path.Combine(folder, "state.json"), true);
    }

    public void Start(string server, string id, Sample sample, string? operationId = null) =>
        StartCore(server, id, sample, sample.ToSnapshot(), operationId);

    public void Start(string server, string id, AircraftSnapshot snapshot, string? operationId = null)
    {
        if (!TryToLegacySample(snapshot, out var sample))
            throw new InvalidOperationException("Le connecteur ne fournit pas les données minimales de navigation pour démarrer le vol.");
        StartCore(server, id, sample, snapshot, operationId);
    }

    private void StartCore(string server, string id, Sample sample, AircraftSnapshot snapshot, string? operationId)
    {
        lock (Gate) {
            if (Flight is not null) throw new InvalidOperationException("Terminez le rapport en cours avant un nouveau départ.");
            recoveryRequired = false;
            if (snapshot.OnGround != true) throw new InvalidOperationException("L'ACARS doit être démarré au sol, avant le départ du poste.");
            tracking.Arm(FlightPhase.Boarding);
            fdm.Reset();
            Flight = new(server, id, sample.RecordedAt, sample.Fuel, Phase: "BOARDING", OperationId: operationId)
            {
                Timeline = [new(sample.RecordedAt, "BOARDING")]
            };
            previous = sample;
            previousSnapshot = snapshot;
            Pending = [];
            PendingEvents = [];
            Track = [];
            tracking.Process(snapshot);
            fdm.Process(snapshot, FlightPhase.Boarding, []);
            QueuePosition(sample, snapshot);
            Save();
        }
    }

    public void Resume(string server) { lock (Gate) {
        if (Flight is null || Flight.Server != server) throw new InvalidOperationException("Le serveur ne correspond pas au vol enregistré.");
        tracking.Restore(
            FlightTrackingEngine.ParsePhase(Flight.Phase),
            Flight.Journal.Any(x => x.Name == "ON"));
        fdm.Restore(Flight.Observations);
        recoveryRequired = false;
        Flight = Flight with { Recording = true };
        previous = null;
        previousSnapshot = null;
        Save();
    }}
    public void Pause() { lock (Gate) {
        if (Flight is not null) Flight = Flight with { Recording = false };
        previous = null;
        previousSnapshot = null;
        Save();
    }}

    /// <summary>
    /// Connector boundary. The original snapshot is preserved for FDM so
    /// unavailable optional values remain UNKNOWN instead of becoming legacy 0s.
    /// </summary>
    public void Capture(AircraftSnapshot snapshot)
    {
        if (TryToLegacySample(snapshot, out var sample)) Capture(sample, snapshot);
    }

    public void Capture(Sample sample) => Capture(sample, sample.ToSnapshot());

    private void Capture(Sample s, AircraftSnapshot snapshot) { lock (Gate) {
        if (Flight is null || !Flight.Recording) return;

        var pendingBefore = Pending.Count;
        var eventsBefore = PendingEvents.Count;
        var observationsBefore = Flight.Observations.Count;
        var phaseBefore = Flight.Phase;
        var decision = tracking.Process(snapshot);
        ApplyTrackingDecision(decision, s);
        AddObservations(fdm.Process(snapshot, decision.Phase, decision.Events));

        Track.Add(new(s.RecordedAt, s.Lat, s.Lon, s.Altitude));
        if (Track.Count > 720) Track.RemoveRange(0, Track.Count - 720);

        var changed = Flight.Phase != phaseBefore;
        if (previous is not null) {
            var dt = (s.RecordedAt - previous.RecordedAt).TotalSeconds;
            if (dt > 0 && dt <= 10) {
                var distance = Flight.Distance;
                var segment = Distance(previous.Lat, previous.Lon, s.Lat, s.Lon);
                if (segment < Math.Max(1, dt * 1500 / 3600)) distance += segment;
                else Warning = "Déplacement discontinu détecté ; segment exclu de la distance.";

                var fuel = Flight.FuelUsed + Math.Max(0, previous.Fuel - s.Fuel);
                var airborne = Flight.AirborneSeconds + (!previous.OnGround ? dt : 0);
                Flight = Flight with { Distance = distance, FuelUsed = fuel, AirborneSeconds = airborne };
            } else if (dt > 10) {
                Warning = "Interruption de télémétrie : durée et consommation peuvent être incomplètes.";
            }
        }

        if (Flight.Phase is "PUSHBACK" or "TAXI_OUT" or "TAXI_IN"
            && snapshot.OnGround == true
            && snapshot.GroundSpeedKnots is { } taxiGs
            && taxiGs > Rules.TaxiSpeed)
            AddIssue(s, "TAXI_OVERSPEED", $"Vitesse sol excessive au roulage : {taxiGs:0} kt.");

        if (Flight.Phase == "FINAL" && snapshot.GearDown == false)
            AddIssue(s, "GEAR_UP_FINAL", "Train rentré en finale.");

        if (lastQueuedAt is null || s.RecordedAt - lastQueuedAt >= positionInterval) {
            QueuePosition(s, snapshot);
            changed = true;
        }

        previous = s;
        previousSnapshot = snapshot;
        if (changed || Pending.Count != pendingBefore || PendingEvents.Count != eventsBefore
            || Flight.Observations.Count != observationsBefore) Save();
    }}

    public void AcknowledgePositions(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); Pending.RemoveAll(x => set.Contains(x.Sample.SampleId)); Save(); }}
    public void AcknowledgeEvents(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); PendingEvents.RemoveAll(x => set.Contains(x.EventId)); Save(); }}

    public void RecordLocalOperationalEvent(string name, DateTimeOffset occurredAt, double? value = null)
    {
        lock (Gate) {
            if (Flight is null) return;
            if (Flight.Journal.Any(x => x.Name == name && x.OccurredAt == occurredAt)) return;
            Flight = Flight with { Journal = [.. Flight.Journal, new(occurredAt, name, value)] };
            Save();
        }
    }
    public void Complete() { lock (Gate) {
        if (Flight?.Phase != "IN") throw new InvalidOperationException("Attendez l'événement IN : avion arrêté au parking, frein de parc serré.");
        if (Pending.Count > 0 || PendingEvents.Count > 0) throw new InvalidOperationException("Des messages ACARS restent à synchroniser.");
        var flight = Flight;
        AddObservations(fdm.Flush(previousSnapshot, FlightTrackingEngine.ParsePhase(flight.Phase)));
        flight = Flight!;
        var block = flight.BlockOn is null || flight.BlockOff is null ? 0 : (int)Math.Round((flight.BlockOn.Value - flight.BlockOff.Value).TotalMinutes);
        History.Insert(0, new(flight.PirepId, DateTimeOffset.UtcNow, Math.Round(flight.Distance, 2), (int)Math.Round(flight.AirborneSeconds / 60), block, Math.Round(flight.FuelUsed), flight.LandingRate, flight.Issues, flight.Observations));
        if (History.Count > 25) History.RemoveRange(25, History.Count - 25);
        SaveHistory(); Flight = null; previous = null; previousSnapshot = null; Track = []; recoveryRequired = false; Save();
    }}

    public FlightReview? GetReview()
    {
        lock (Gate) {
            if (Flight is null) return null;
            var block = Flight.BlockOff is null
                ? 0
                : (int)Math.Round(((Flight.BlockOn ?? DateTimeOffset.UtcNow) - Flight.BlockOff.Value).TotalMinutes);
            var observations = Flight.Observations ?? [];
            string? gateStatus(string prefix) => observations
                .LastOrDefault(x => x.Code.StartsWith(prefix, StringComparison.Ordinal))?.Status;
            var maxBank = observations.Where(x => x.Code == "EXCESSIVE_BANK")
                .Select(x => x.Value ?? 0).DefaultIfEmpty(0).Max();
            var maxRate = observations.Where(x => x.Code == "SIM_RATE")
                .Select(x => x.Value ?? 0).DefaultIfEmpty(0).Max();
            return new(
                Flight.PirepId,
                Flight.Phase,
                Flight.Phase == "IN",
                Math.Round(Flight.Distance, 2),
                (int)Math.Round(Flight.AirborneSeconds / 60),
                block,
                Math.Round(Flight.FuelUsed),
                Flight.LandingRate,
                gateStatus("APPROACH_1000_"),
                gateStatus("APPROACH_500_"),
                (int)observations.Where(x => x.Code == "BOUNCE").Select(x => x.Value ?? 0).DefaultIfEmpty(0).Max(),
                observations.Count(x => x.Code == "GO_AROUND"),
                maxBank > 0 ? maxBank : null,
                Math.Round(observations.Where(x => x.Code == "FUEL_ADDED").Sum(x => x.Value ?? 0)),
                maxRate > 0 ? maxRate : null,
                Flight.Issues,
                observations,
                Flight.Timeline);
        }
    }

    public FlightRecoveryInfo? GetRecoveryInfo()
    {
        lock (Gate) {
            if (!recoveryRequired || Flight is null || Flight.Recording) return null;
            return new(
                Flight.PirepId,
                Flight.OperationId,
                Flight.Server,
                Flight.Phase,
                Flight.Started,
                Track.LastOrDefault()?.RecordedAt,
                Math.Round(Flight.Distance, 2),
                (int)Math.Round(Flight.AirborneSeconds / 60),
                Math.Round(Flight.FuelUsed),
                Flight.LandingRate,
                Pending.Count + PendingEvents.Count,
                Track.Count);
        }
    }

    public void AbandonRecovery()
    {
        lock (Gate) {
            if (!recoveryRequired || Flight is null || Flight.Recording)
                throw new InvalidOperationException("Aucun vol interrompu à abandonner.");

            ArchiveRecoveryState();
            Flight = null;
            previous = null;
            previousSnapshot = null;
            lastQueuedAt = null;
            Pending = [];
            PendingEvents = [];
            Track = [];
            recoveryRequired = false;
            Warning = null;
            Save();
        }
    }

    private void ArchiveRecoveryState()
    {
        var statePath = Path.Combine(folder, "state.json");
        if (!File.Exists(statePath)) return;
        var archiveFolder = Path.Combine(folder, "recovery-archive");
        Directory.CreateDirectory(archiveFolder);
        var archivePath = Path.Combine(archiveFolder, $"abandoned-{DateTimeOffset.UtcNow:yyyyMMdd-HHmmss-fff}.json");
        File.Copy(statePath, archivePath, false);

        foreach (var obsolete in Directory.GetFiles(archiveFolder, "abandoned-*.json")
                     .OrderByDescending(File.GetCreationTimeUtc).Skip(5))
            try { File.Delete(obsolete); } catch (IOException) { }
    }
    private static readonly HashSet<string> PhaseTimelineEvents = new(StringComparer.Ordinal) {
        "PUSHBACK", "TAXI_OUT", "TAKEOFF", "CLIMB", "CRUISE", "DESCENT",
        "APPROACH", "FINAL", "LANDING", "TAXI_IN", "IN"
    };

    private void ApplyTrackingDecision(TrackingDecision decision, Sample current)
    {
        if (Flight is null) return;

        var nextPhase = FlightTrackingEngine.ToExternalPhase(decision.Phase);
        if (!string.Equals(Flight.Phase, nextPhase, StringComparison.Ordinal))
            Flight = Flight with { Phase = nextPhase };

        foreach (var fact in decision.Events)
        {
            if (fact.Type == "OUT")
                Flight = Flight with { BlockOff = fact.OccurredAt };
            else if (fact.Type == "OFF")
                Flight = Flight with { Takeoff = fact.OccurredAt };
            else if (fact.Type == "ON")
                Flight = Flight with {
                    Landing = fact.OccurredAt,
                    LandingRate = fact.Value ?? Flight.LandingRate
                };
            else if (fact.Type == "IN")
                Flight = Flight with { BlockOn = fact.OccurredAt };

            if (PhaseTimelineEvents.Contains(fact.Type)
                && !Flight.Timeline.Any(x => x.Name == fact.Type && x.OccurredAt == fact.OccurredAt))
                Flight = Flight with { Timeline = [.. Flight.Timeline, new(fact.OccurredAt, fact.Type)] };

            QueueEvent(fact, current);

            if (fact.Type == "TOUCHDOWN" && fact.Value is { } rate && Math.Abs(rate) >= Rules.HardLandingRate)
                AddIssue(current with {
                    RecordedAt = fact.OccurredAt,
                    Lat = fact.Snapshot.Latitude ?? current.Lat,
                    Lon = fact.Snapshot.Longitude ?? current.Lon
                }, "HARD_LANDING", $"Atterrissage dur : {Math.Abs(rate):0} ft/min.");

            if (fact.Type is "OUT" or "OFF" or "ON" or "IN")
                QueuePosition(fact.Snapshot, current);
        }
    }

    private void AddObservations(IEnumerable<FdmObservation> observations)
    {
        if (Flight is null) return;
        var additions = observations
            .Where(item => !Flight.Observations.Any(existing =>
                existing.Code == item.Code && existing.OccurredAt == item.OccurredAt))
            .ToList();
        if (additions.Count == 0) return;
        Flight = Flight with { Observations = [.. Flight.Observations, .. additions] };
    }

    private void AddIssue(Sample sample, string code, string message) {
        if (Flight is null || Flight.Issues.Any(x => x.Code == code)) return;
        Flight = Flight with { Issues = [.. Flight.Issues, new(sample.RecordedAt, code, message)] };
        Warning = message;
        QueueEvent(code, sample);
    }

    private void QueuePosition(Sample sample, AircraftSnapshot? snapshot = null) {
        if (Pending.Any(x => x.Sample.SampleId == sample.SampleId)) return;
        Pending.Add(new(sample, snapshot));
        lastQueuedAt = sample.RecordedAt;
    }

    private void QueuePosition(AircraftSnapshot snapshot, Sample fallback)
    {
        if (TryToLegacySample(snapshot, out var sample)) QueuePosition(sample, snapshot);
        else QueuePosition(fallback, snapshot);
    }

    private void QueueEvent(string name, Sample sample, double? value = null)
    {
        if (PendingEvents.Any(x => x.Name == name && x.OccurredAt == sample.RecordedAt)) return;
        PendingEvents.Add(new(Guid.NewGuid(), name, sample.RecordedAt, sample.Lat, sample.Lon));
        if (Flight is not null && !Flight.Journal.Any(x => x.Name == name && x.OccurredAt == sample.RecordedAt))
            Flight = Flight with { Journal = [.. Flight.Journal, new(sample.RecordedAt, name, value)] };
    }

    private void QueueEvent(FlightEvent fact, Sample fallback)
    {
        var lat = fact.Snapshot.Latitude ?? fallback.Lat;
        var lon = fact.Snapshot.Longitude ?? fallback.Lon;
        if (PendingEvents.Any(x => x.Name == fact.Type && x.OccurredAt == fact.OccurredAt)) return;

        PendingEvents.Add(new(Guid.NewGuid(), fact.Type, fact.OccurredAt, lat, lon));
        if (Flight is not null && !Flight.Journal.Any(x => x.Name == fact.Type && x.OccurredAt == fact.OccurredAt))
            Flight = Flight with { Journal = [.. Flight.Journal, new(fact.OccurredAt, fact.Type, fact.Value)] };
    }

    private static bool TryToLegacySample(AircraftSnapshot s, out Sample sample)
    {
        sample = default!;
        if (s.Latitude is null || s.Longitude is null || s.AltitudeMslFeet is null || s.AltitudeAglFeet is null
            || s.IndicatedAirspeedKnots is null || s.GroundSpeedKnots is null || s.VerticalSpeedFeetPerMinute is null
            || s.HeadingDegrees is null || s.FuelWeight is null || s.OnGround is null) return false;
        var engines = s.EnginesRunning ?? [];
        sample = new Sample(
            s.SampleId,
            s.RecordedAt,
            s.Latitude.Value,
            s.Longitude.Value,
            s.AltitudeMslFeet.Value,
            s.AltitudeAglFeet.Value,
            s.IndicatedAirspeedKnots.Value,
            s.GroundSpeedKnots.Value,
            s.VerticalSpeedFeetPerMinute.Value,
            s.HeadingDegrees.Value,
            s.FuelWeight.Value,
            s.OnGround.Value,
            s.BankDegrees ?? 0,
            s.GearDown ?? false,
            (s.TouchdownVerticalSpeedFeetPerMinute ?? s.VerticalSpeedFeetPerMinute.Value) / 60d,
            s.FlapsPercent ?? 0,
            false,
            0,
            0,
            s.ParkingBrake ?? false,
            BeaconLight: s.BeaconLight ?? false,
            LandingLight: s.LandingLight ?? false,
            Engine1Running: engines.ElementAtOrDefault(0),
            Engine2Running: engines.ElementAtOrDefault(1),
            Engine3Running: engines.ElementAtOrDefault(2),
            Engine4Running: engines.ElementAtOrDefault(3),
            SlewActive: s.SlewActive ?? false,
            SimulationRate: s.SimulationRate ?? 1d);
        return true;
    }
    public static double Distance(double lat1, double lon1, double lat2, double lon2) {
        var r = Math.PI / 180; var a = Math.Pow(Math.Sin((lat2 - lat1) * r / 2), 2) + Math.Cos(lat1 * r) * Math.Cos(lat2 * r) * Math.Pow(Math.Sin((lon2 - lon1) * r / 2), 2);
        return 3440.065 * 2 * Math.Atan2(Math.Sqrt(a), Math.Sqrt(Math.Max(0, 1 - a)));
    }
}
