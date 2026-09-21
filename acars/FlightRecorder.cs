using System.Text.Json;
using System.IO;

namespace Promethee;

public record FlightState(string Server, string PirepId, DateTimeOffset Started, double InitialFuel,
    string Phase = "OUT", DateTimeOffset? BlockOff = null, DateTimeOffset? Takeoff = null,
    DateTimeOffset? Landing = null, DateTimeOffset? BlockOn = null, double Distance = 0,
    double FuelUsed = 0, double AirborneSeconds = 0, double? LandingRate = null, bool Recording = true)
{
    public List<FlightIssue> Issues { get; init; } = [];
    public List<PhaseEntry> Timeline { get; init; } = [];
    public List<FlightJournalEntry> Journal { get; init; } = [];
}
public record Envelope(Sample Sample);
public record AcarsEvent(Guid EventId, string Name, DateTimeOffset OccurredAt, double Lat, double Lon);
public record FlightIssue(DateTimeOffset OccurredAt, string Code, string Message, string Severity = "warning");
public record PhaseEntry(DateTimeOffset OccurredAt, string Name);
public record FlightJournalEntry(DateTimeOffset OccurredAt, string Name, double? Value = null);
public record AcarsRules(double TaxiSpeed = 35, double HardLandingRate = 600);
public record TrackPoint(DateTimeOffset RecordedAt, double Lat, double Lon, double Altitude);
public record FlightSummary(string PirepId, DateTimeOffset CompletedAt, double Distance, int AirborneMinutes,
    int BlockMinutes, double FuelUsed, double? LandingRate, IReadOnlyList<FlightIssue> Issues);

public sealed class FlightRecorder
{
    private TimeSpan positionInterval = TimeSpan.FromSeconds(15);
    private static readonly TimeSpan InConfirmation = TimeSpan.FromSeconds(15);
    public readonly object Gate = new();
    public readonly SemaphoreSlim NetworkGate = new(1, 1);
    private readonly string folder;
    private Sample? previous;
    private DateTimeOffset? lastQueuedAt;
    private DateTimeOffset? parkedSince;
    private readonly FlightTrackingEngine tracking = new();
    public FlightState? Flight { get; private set; }
    public List<TrackPoint> Track { get; private set; } = [];
    public List<FlightSummary> History { get; private set; } = [];
    public AcarsRules Rules { get; private set; } = new();
    public List<Envelope> Pending { get; private set; } = [];
    public List<AcarsEvent> PendingEvents { get; private set; } = [];
    public string? Warning { get; private set; }
    public RemoteAcarsConfiguration? RemoteConfiguration { get; private set; }

    public FlightRecorder()
    {
        folder = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "AirInter", "Promethee");
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
                if (Flight is not null) Flight = Flight with { Recording = false };
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

    public void Start(string server, string id, Sample sample) { lock (Gate) {
        if (Flight is not null) throw new InvalidOperationException("Terminez le rapport en cours avant un nouveau départ.");
        if (!sample.OnGround) throw new InvalidOperationException("L'ACARS doit être démarré au sol, avant le départ du poste.");
        tracking.Arm();
        Flight = new(server, id, sample.RecordedAt, sample.Fuel, Phase: "BOARDING", BlockOff: sample.RecordedAt) { Timeline = [new(sample.RecordedAt, "OUT"), new(sample.RecordedAt, "BOARDING")] }; previous = sample; Pending = []; PendingEvents = []; Track = [];
        QueueEvent("OUT", sample); QueuePosition(sample); Save();
    }}
    public void Start(string server, string id, AircraftSnapshot snapshot)
    {
        if (!TryToLegacySample(snapshot, out var sample))
            throw new InvalidOperationException("Le connecteur ne fournit pas encore les données minimales pour démarrer le vol.");
        Start(server, id, sample);
    }
    public void Resume(string server) { lock (Gate) {
        if (Flight is null || Flight.Server != server) throw new InvalidOperationException("Le serveur ne correspond pas au vol enregistré.");
        tracking.Arm();
        Flight = Flight with { Recording = true, BlockOff = Flight.BlockOff ?? Flight.Started }; previous = null; parkedSince = null; Save();
    }}
    public void Pause() { lock (Gate) { if (Flight is not null) Flight = Flight with { Recording = false }; previous = null; parkedSince = null; Save(); }}

    /// <summary>New connector boundary. Legacy recorder logic remains intact during migration.</summary>
    public void Capture(AircraftSnapshot snapshot)
    {
        if (TryToLegacySample(snapshot, out var sample)) Capture(sample);
    }

    public void Capture(Sample s) { lock (Gate) {
        if (Flight is null || !Flight.Recording) return;
        var pendingBefore = Pending.Count;
        var eventsBefore = PendingEvents.Count;
        RecordConnectorFacts(tracking.Process(s.ToSnapshot()), s);
        Track.Add(new(s.RecordedAt, s.Lat, s.Lon, s.Altitude));
        if (Track.Count > 720) Track.RemoveRange(0, Track.Count - 720);
        var changed = false;
        if (previous is not null) {
            var dt = (s.RecordedAt - previous.RecordedAt).TotalSeconds;
            if (dt > 0 && dt <= 10) {
                var distance = Flight.Distance; var segment = Distance(previous.Lat, previous.Lon, s.Lat, s.Lon);
                if (segment < Math.Max(1, dt * 1500 / 3600)) distance += segment;
                else Warning = "Déplacement discontinu détecté ; segment exclu de la distance.";
                var fuel = Flight.FuelUsed + Math.Max(0, previous.Fuel - s.Fuel);
                var airborne = Flight.AirborneSeconds + (!previous.OnGround ? dt : 0);
                Flight = Flight with { Distance = distance, FuelUsed = fuel, AirborneSeconds = airborne };
                if (previous.OnGround && s.OnGround && Flight.Phase == "BOARDING" && s.Gs > 0.5) {
                    SetPhase("PUSHBACK", "PUSHBACK", s); changed = true;
                }
                if (s.OnGround && (Flight.Phase == "BOARDING" || Flight.Phase == "PUSHBACK") && s.Gs >= 5) {
                    SetPhase("TAXI_OUT", "TAXI OUT", s); changed = true;
                }
                if (previous.OnGround && !s.OnGround && Flight.Phase is not "LANDING" and not "TAXI_IN") {
                    Flight = Flight with { Phase = "TAKEOFF", Takeoff = s.RecordedAt, Timeline = [.. Flight.Timeline, new(s.RecordedAt, "OFF"), new(s.RecordedAt, "TAKEOFF")] }; QueueEvent("OFF", s); QueueEvent("TAKEOFF", s); QueuePosition(s); changed = true;
                }
                if (Flight.Phase == "TAKEOFF" && (!s.OnGround && (s.Agl >= 500 || !s.GearDown))) { SetPhase("ENROUTE", "ENROUTE", s); changed = true; }
                if (Flight.Phase == "ENROUTE" && !s.OnGround && s.Agl < 10000 && s.Vs < -100) { SetPhase("APPROACH", "APPROACH", s); changed = true; }
                if (Flight.Phase == "APPROACH" && !s.OnGround && s.Agl < 3000 && s.GearDown && s.Flaps > 0) { SetPhase("FINAL", "FINAL", s); changed = true; }
                if (!previous.OnGround && s.OnGround && Flight.Phase is not "LANDING" and not "TAXI_IN" and not "IN") {
                    var rate = -Math.Abs(s.TouchdownVelocity * 60);
                    Flight = Flight with { Phase = "LANDING", Landing = s.RecordedAt, LandingRate = rate, Timeline = [.. Flight.Timeline, new(s.RecordedAt, "ON"), new(s.RecordedAt, "LANDING")] };
                    QueueEvent("ON", s); QueueEvent("LANDING", s); QueuePosition(s);
                    if (Math.Abs(rate) >= Rules.HardLandingRate) AddIssue(s, "HARD_LANDING", $"Atterrissage dur : {Math.Abs(rate):0} ft/min.");
                    changed = true;
                }
            } else if (dt > 10) Warning = "Interruption de télémétrie : durée et consommation peuvent être incomplètes.";
        }
        if (Flight.Phase == "LANDING" && s.OnGround && s.Gs < 30) { SetPhase("TAXI_IN", "TAXI IN", s); changed = true; }
        if (s.OnGround && s.Gs > Rules.TaxiSpeed) AddIssue(s, "TAXI_OVERSPEED", $"Vitesse sol excessive au roulage : {s.Gs:0} kt.");
        if (Flight.Phase == "FINAL" && !s.GearDown) AddIssue(s, "GEAR_UP_FINAL", "Train rentré en finale.");
        if (Flight.Phase == "TAXI_IN" && s.OnGround && s.ParkingBrake && s.Gs < 2) {
            parkedSince ??= s.RecordedAt;
            if (s.RecordedAt - parkedSince >= InConfirmation) {
                Flight = Flight with { Phase = "IN", BlockOn = s.RecordedAt, Timeline = [.. Flight.Timeline, new(s.RecordedAt, "IN")] }; QueueEvent("IN", s); QueuePosition(s); changed = true;
            }
        } else if (Flight.Phase != "IN") parkedSince = null;
        if (lastQueuedAt is null || s.RecordedAt - lastQueuedAt >= positionInterval) { QueuePosition(s); changed = true; }
        previous = s;
        // Persist at the queue cadence (and on phase changes), rather than once
        // per telemetry tick. A restart can therefore lose at most the current
        // unqueued second, never an acknowledged or queued ACARS message.
        if (changed || Pending.Count != pendingBefore || PendingEvents.Count != eventsBefore) Save();
    }}

    public void AcknowledgePositions(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); Pending.RemoveAll(x => set.Contains(x.Sample.SampleId)); Save(); }}
    public void AcknowledgeEvents(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); PendingEvents.RemoveAll(x => set.Contains(x.EventId)); Save(); }}
    public void Complete() { lock (Gate) {
        if (Flight?.Phase != "IN") throw new InvalidOperationException("Attendez l'événement IN : avion arrêté au parking, frein de parc serré.");
        if (Pending.Count > 0 || PendingEvents.Count > 0) throw new InvalidOperationException("Des messages ACARS restent à synchroniser.");
        var flight = Flight;
        var block = flight.BlockOn is null || flight.BlockOff is null ? 0 : (int)Math.Round((flight.BlockOn.Value - flight.BlockOff.Value).TotalMinutes);
        History.Insert(0, new(flight.PirepId, DateTimeOffset.UtcNow, Math.Round(flight.Distance, 2), (int)Math.Round(flight.AirborneSeconds / 60), block, Math.Round(flight.FuelUsed), flight.LandingRate, flight.Issues));
        if (History.Count > 25) History.RemoveRange(25, History.Count - 25);
        SaveHistory(); Flight = null; previous = null; parkedSince = null; Track = []; Save();
    }}
    private void SetPhase(string phase, string eventName, Sample sample) { if (Flight?.Phase == phase) return; Flight = Flight! with { Phase = phase, Timeline = [.. Flight.Timeline, new(sample.RecordedAt, eventName)] }; QueueEvent(eventName, sample); }
    private void AddIssue(Sample sample, string code, string message) {
        if (Flight is null || Flight.Issues.Any(x => x.Code == code)) return;
        Flight = Flight with { Issues = [.. Flight.Issues, new(sample.RecordedAt, code, message)] }; Warning = message; QueueEvent(code, sample);
    }
    private void QueuePosition(Sample sample) { if (Pending.Any(x => x.Sample.SampleId == sample.SampleId)) return; Pending.Add(new(sample)); lastQueuedAt = sample.RecordedAt; }
    private void QueueEvent(string name, Sample sample, double? value = null)
    {
        PendingEvents.Add(new(Guid.NewGuid(), name, sample.RecordedAt, sample.Lat, sample.Lon));
        if (Flight is not null && !Flight.Journal.Any(x => x.Name == name && x.OccurredAt == sample.RecordedAt))
            Flight = Flight with { Journal = [.. Flight.Journal, new(sample.RecordedAt, name, value)] };
    }
    private void RecordConnectorFacts(TrackingDecision decision, Sample sample)
    {
        // Phase transitions are still emitted by the proven legacy state machine.
        // These facts are independent and therefore safe to add during migration.
        foreach (var fact in decision.Events.Where(x => x.Type is "BEACON_ON" or "BEACON_OFF"
            or "PARKING_BRAKE_ON" or "PARKING_BRAKE_OFF" or "GEAR_ON" or "GEAR_OFF"
            or "LANDING_LIGHTS_ON" or "LANDING_LIGHTS_OFF" or "ENGINE_STARTED"
            or "ENGINE_STOPPED" or "TOUCHDOWN" or "SLEW_ACTIVE" or "SIM_RATE_INCREASED"
            or "FUEL_INCREASED" or "TOUCHDOWN_FIRST" or "TOUCHDOWN_BOUNCE"
            or "BOUNCE" or "BOUNCE_COUNT"))
        {
            if (!PendingEvents.Any(x => x.Name == fact.Type && x.OccurredAt == fact.OccurredAt))
                QueueEvent(fact.Type, sample, fact.Value);
        }
    }
    private static bool TryToLegacySample(AircraftSnapshot s, out Sample sample)
    {
        sample = default!;
        if (s.Latitude is null || s.Longitude is null || s.AltitudeMslFeet is null || s.AltitudeAglFeet is null
            || s.IndicatedAirspeedKnots is null || s.GroundSpeedKnots is null || s.VerticalSpeedFeetPerMinute is null
            || s.HeadingDegrees is null || s.FuelWeight is null || s.OnGround is null || s.GearDown is null
            || s.FlapsPercent is null || s.ParkingBrake is null) return false;
        sample = new Sample(s.SampleId, s.RecordedAt, s.Latitude.Value, s.Longitude.Value, s.AltitudeMslFeet.Value,
            s.AltitudeAglFeet.Value, s.IndicatedAirspeedKnots.Value, s.GroundSpeedKnots.Value,
            s.VerticalSpeedFeetPerMinute.Value, s.HeadingDegrees.Value, s.FuelWeight.Value, s.OnGround.Value,
            0, s.GearDown.Value, 0, s.FlapsPercent.Value, false, 0, 0, s.ParkingBrake.Value);
        return true;
    }
    public static double Distance(double lat1, double lon1, double lat2, double lon2) {
        var r = Math.PI / 180; var a = Math.Pow(Math.Sin((lat2 - lat1) * r / 2), 2) + Math.Cos(lat1 * r) * Math.Cos(lat2 * r) * Math.Pow(Math.Sin((lon2 - lon1) * r / 2), 2);
        return 3440.065 * 2 * Math.Atan2(Math.Sqrt(a), Math.Sqrt(Math.Max(0, 1 - a)));
    }
}
