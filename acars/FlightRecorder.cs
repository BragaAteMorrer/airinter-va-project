using System.Text.Json;

namespace Promethee;

public record FlightState(string Server, string PirepId, DateTimeOffset Started, double InitialFuel,
    string Phase = "OUT", DateTimeOffset? BlockOff = null, DateTimeOffset? Takeoff = null,
    DateTimeOffset? Landing = null, DateTimeOffset? BlockOn = null, double Distance = 0,
    double FuelUsed = 0, double AirborneSeconds = 0, double? LandingRate = null, bool Recording = true);
public record Envelope(Sample Sample);
public record AcarsEvent(Guid EventId, string Name, DateTimeOffset OccurredAt, double Lat, double Lon);

public sealed class FlightRecorder
{
    private static readonly TimeSpan PositionInterval = TimeSpan.FromSeconds(15);
    private static readonly TimeSpan InConfirmation = TimeSpan.FromSeconds(15);
    public readonly object Gate = new();
    public readonly SemaphoreSlim NetworkGate = new(1, 1);
    private readonly string folder;
    private Sample? previous;
    private DateTimeOffset? lastQueuedAt;
    private DateTimeOffset? parkedSince;
    public FlightState? Flight { get; private set; }
    public List<Envelope> Pending { get; private set; } = [];
    public List<AcarsEvent> PendingEvents { get; private set; } = [];
    public string? Warning { get; private set; }

    public FlightRecorder()
    {
        folder = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "AirInter", "Promethee");
        Directory.CreateDirectory(folder);
        var file = Path.Combine(folder, "state.json");
        if (File.Exists(file)) {
            var saved = JsonSerializer.Deserialize<Saved>(File.ReadAllText(file));
            Flight = saved?.Flight; Pending = saved?.Pending ?? []; PendingEvents = saved?.PendingEvents ?? [];
            if (Flight is not null) Flight = Flight with { Recording = false };
        }
    }

    private record Saved(FlightState? Flight, List<Envelope> Pending, List<AcarsEvent> PendingEvents);
    private void Save()
    {
        var temp = Path.Combine(folder, "state.tmp");
        using (var file = new FileStream(temp, FileMode.Create, FileAccess.Write, FileShare.None)) {
            JsonSerializer.Serialize(file, new Saved(Flight, Pending, PendingEvents)); file.Flush(true);
        }
        File.Move(temp, Path.Combine(folder, "state.json"), true);
    }

    public void Start(string server, string id, Sample sample) { lock (Gate) {
        if (Flight is not null) throw new InvalidOperationException("Terminez le rapport en cours avant un nouveau départ.");
        if (!sample.OnGround) throw new InvalidOperationException("L'ACARS doit être démarré au sol, avant le départ du poste.");
        Flight = new(server, id, sample.RecordedAt, sample.Fuel, BlockOff: sample.RecordedAt); previous = sample; Pending = []; PendingEvents = [];
        QueueEvent("OUT", sample); QueuePosition(sample); Save();
    }}
    public void Resume(string server) { lock (Gate) {
        if (Flight is null || Flight.Server != server) throw new InvalidOperationException("Le serveur ne correspond pas au vol enregistré.");
        Flight = Flight with { Recording = true, BlockOff = Flight.BlockOff ?? Flight.Started }; previous = null; parkedSince = null; Save();
    }}
    public void Pause() { lock (Gate) { if (Flight is not null) Flight = Flight with { Recording = false }; previous = null; parkedSince = null; Save(); }}

    public void Capture(Sample s) { lock (Gate) {
        if (Flight is null || !Flight.Recording) return;
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
                if (previous.OnGround && !s.OnGround && Flight.Phase == "OUT") {
                    Flight = Flight with { Phase = "ENROUTE", Takeoff = s.RecordedAt }; QueueEvent("OFF", s); QueuePosition(s); changed = true;
                }
                if (!previous.OnGround && s.OnGround && Flight.Phase == "ENROUTE") {
                    Flight = Flight with { Phase = "ON", Landing = s.RecordedAt, LandingRate = -Math.Abs(s.TouchdownVelocity * 60) };
                    QueueEvent("ON", s); QueuePosition(s); changed = true;
                }
            } else if (dt > 10) Warning = "Interruption de télémétrie : durée et consommation peuvent être incomplètes.";
        }
        if (Flight.Phase == "ON" && s.OnGround && s.ParkingBrake && s.Gs < 2) {
            parkedSince ??= s.RecordedAt;
            if (s.RecordedAt - parkedSince >= InConfirmation) {
                Flight = Flight with { Phase = "IN", BlockOn = s.RecordedAt }; QueueEvent("IN", s); QueuePosition(s); changed = true;
            }
        } else if (Flight.Phase != "IN") parkedSince = null;
        if (lastQueuedAt is null || s.RecordedAt - lastQueuedAt >= PositionInterval) { QueuePosition(s); changed = true; }
        previous = s;
        if (changed) Save();
    }}

    public void AcknowledgePositions(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); Pending.RemoveAll(x => set.Contains(x.Sample.SampleId)); Save(); }}
    public void AcknowledgeEvents(IEnumerable<Guid> ids) { lock (Gate) { var set = ids.ToHashSet(); PendingEvents.RemoveAll(x => set.Contains(x.EventId)); Save(); }}
    public void Complete() { lock (Gate) {
        if (Flight?.Phase != "IN") throw new InvalidOperationException("Attendez l'événement IN : avion arrêté au parking, frein de parc serré.");
        if (Pending.Count > 0 || PendingEvents.Count > 0) throw new InvalidOperationException("Des messages ACARS restent à synchroniser.");
        Flight = null; previous = null; parkedSince = null; Save();
    }}
    private void QueuePosition(Sample sample) { if (Pending.Any(x => x.Sample.SampleId == sample.SampleId)) return; Pending.Add(new(sample)); lastQueuedAt = sample.RecordedAt; }
    private void QueueEvent(string name, Sample sample) => PendingEvents.Add(new(Guid.NewGuid(), name, sample.RecordedAt, sample.Lat, sample.Lon));
    public static double Distance(double lat1, double lon1, double lat2, double lon2) {
        var r = Math.PI / 180; var a = Math.Pow(Math.Sin((lat2 - lat1) * r / 2), 2) + Math.Cos(lat1 * r) * Math.Cos(lat2 * r) * Math.Pow(Math.Sin((lon2 - lon1) * r / 2), 2);
        return 3440.065 * 2 * Math.Atan2(Math.Sqrt(a), Math.Sqrt(Math.Max(0, 1 - a)));
    }
}
