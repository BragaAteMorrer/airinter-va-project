using System.Text.Json;
namespace Promethee;
public record FlightState(string Server, string PirepId, DateTimeOffset Started, double InitialFuel,
    double Distance=0, double FuelUsed=0, double AirborneSeconds=0, double? LandingRate=null, bool Recording=true);
public record Envelope(Sample Sample,double? MaxIas);
public sealed class FlightRecorder
{
    public readonly object Gate = new();
    private readonly string folder;
    public FlightState? Flight { get; private set; }
    public List<Envelope> Pending { get; private set; }=[];
    public string? Warning { get; private set; }
    public double? MaxIas { get; set; }
    private Sample? previous;
    public FlightRecorder()
    {
        folder=Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),"AirInter","Promethee");
        Directory.CreateDirectory(folder);
        var file=Path.Combine(folder,"state.json");
        if(File.Exists(file)) {
            var saved=JsonSerializer.Deserialize<Saved>(File.ReadAllText(file));
            Flight=saved?.Flight; Pending=saved?.Pending??[];
            // A crash requires explicit resume; never silently attach telemetry to a stale PIREP.
            if(Flight is not null) Flight=Flight with {Recording=false};
        }
    }
    private record Saved(FlightState? Flight,List<Envelope> Pending);
    private void Save()
    {
        var temp=Path.Combine(folder,"state.tmp");
        using(var file=new FileStream(temp,FileMode.Create,FileAccess.Write,FileShare.None)) {
            JsonSerializer.Serialize(file,new Saved(Flight,Pending));file.Flush(true);
        }
        File.Move(temp,Path.Combine(folder,"state.json"),true);
    }
    public void Start(string server,string id,Sample sample) { lock(Gate) {
        if(Flight is not null) throw new InvalidOperationException("Terminer le rapport en cours avant un nouveau départ.");
        Flight=new(server,id,DateTimeOffset.UtcNow,sample.Fuel);previous=sample;Pending=[];Save();
    }}
    public void Resume(string server) { lock(Gate) {
        if(Flight is null || Flight.Server!=server) throw new InvalidOperationException("Le serveur ne correspond pas au vol enregistré.");
        Flight=Flight with {Recording=true};previous=null;Save();
    }}
    public void Pause() { lock(Gate) { if(Flight is not null) Flight=Flight with {Recording=false};previous=null;Save(); }}
    public void Capture(Sample s) { lock(Gate) {
        if(Flight is null || !Flight.Recording) return;
        var distance=Flight.Distance;var fuel=Flight.FuelUsed;var seconds=Flight.AirborneSeconds;var landing=Flight.LandingRate;
        if(previous is not null) {
            var dt=(s.RecordedAt-previous.RecordedAt).TotalSeconds;
            if(dt>0 && dt<=10) {
                var segment=Distance(previous.Lat,previous.Lon,s.Lat,s.Lon);
                if(segment<Math.Max(1,dt*1500/3600)) distance+=segment;
                else Warning="Déplacement discontinu détecté ; segment exclu de la distance.";
                fuel+=Math.Max(0,previous.Fuel-s.Fuel);
                if(!previous.OnGround) seconds+=dt;
                // MSFS stores the velocity normal to the surface at the last touchdown.
                if(!previous.OnGround && s.OnGround) {
                    landing=-Math.Abs(s.TouchdownVelocity*60);
                }
            } else Warning="Interruption de télémétrie : durée et consommation peuvent être incomplètes.";
        }
        Flight=Flight with {Distance=distance,FuelUsed=fuel,AirborneSeconds=seconds,LandingRate=landing};
        previous=s;Pending.Add(new(s,MaxIas));Save();
    }}
    public void Acknowledge(IEnumerable<Guid> ids) { lock(Gate) { var set=ids.ToHashSet();Pending.RemoveAll(x=>set.Contains(x.Sample.SampleId));Save(); }}
    public void Complete() { lock(Gate) { if(Pending.Count>0) throw new InvalidOperationException("Des échantillons restent à synchroniser.");Flight=null;previous=null;Save(); }}
    public static double Distance(double lat1,double lon1,double lat2,double lon2) {
        var r=Math.PI/180;var a=Math.Pow(Math.Sin((lat2-lat1)*r/2),2)+Math.Cos(lat1*r)*Math.Cos(lat2*r)*Math.Pow(Math.Sin((lon2-lon1)*r/2),2);
        return 3440.065*2*Math.Atan2(Math.Sqrt(a),Math.Sqrt(Math.Max(0,1-a)));
    }
}
