using System.Text.Json;
using System.IO;
using System.Windows;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.Wpf;
using Promethee;

namespace PrometheeDesktop;

public static class WebDesktop
{
    [STAThread] public static void Main(string[] args)
    {
        if (args.Length == 2 && args[0].Equals("--set-server", StringComparison.OrdinalIgnoreCase)) { ServerConfiguration.Set(args[1]); return; }
        new Application().Run(new PrometheeWindow());
    }
}

public sealed class PrometheeWindow : Window
{
    private readonly PhpVmsClient client = new(); private readonly SimConnectReader sim = new(); private readonly FlightRecorder recorder = new();
    private readonly TelemetryService telemetry; private readonly WebView2 web = new(); private bool ticking;
    public PrometheeWindow()
    {
        telemetry = new(sim, recorder, client); Title = "Prométhée ACARS — Air Inter"; Width=1280; Height=840; MinWidth=900; MinHeight=620; Content=web;
        Loaded += async (_, _) => await StartAsync(); Closed += (_, _) => sim.Dispose();
        var timer = new System.Windows.Threading.DispatcherTimer { Interval = TimeSpan.FromSeconds(1) }; timer.Tick += async (_, _) => await Tick(); timer.Start();
    }
    private async Task StartAsync()
    {
        await web.EnsureCoreWebView2Async(); var core=web.CoreWebView2;
        core.Settings.AreDevToolsEnabled=false; core.Settings.AreDefaultContextMenusEnabled=false; core.WebMessageReceived += async (_, e) => await Handle(e.WebMessageAsJson);
        core.SetVirtualHostNameToFolderMapping("promethee.local", Path.Combine(AppContext.BaseDirectory,"wwwroot"), CoreWebView2HostResourceAccessKind.DenyCors);
        core.Navigate("https://promethee.local/index.html");
    }
    private async Task Tick() { if (ticking) return; ticking=true; try { await telemetry.Tick(); } finally { ticking=false; } }
    private async Task Handle(string raw)
    {
        string id=""; try { using var doc=JsonDocument.Parse(raw); var root=doc.RootElement; id=root.GetProperty("id").GetString() ?? ""; var path=root.GetProperty("path").GetString() ?? ""; var body=root.TryGetProperty("body",out var b)?b.Clone():(JsonElement?)null;
            var data=await Route(path,body); Reply(id,true,data); } catch(Exception e) { Reply(id,false,e.Message); }
    }
    private void Reply(string id,bool ok,object data) => web.CoreWebView2.PostWebMessageAsJson(JsonSerializer.Serialize(new {id,ok,data}));
    private async Task<object> Route(string path, JsonElement? body) => path switch {
        "/api/status" => Status(),
        "/api/login" => await Login(body),
        "/api/user" => await client.Send("user"), "/api/bids" => await client.Send("user/bids"),
        "/api/flights" => await client.Send("flights"),
        "/api/prefile" => await client.Send("pireps/prefile", body!.Value),
        "/api/start" => Start(body), "/api/pause" => Pause(), "/api/resume" => Resume(),
        "/api/sync" => new { sent=await TelemetryService.SendPending(client,recorder) },
        "/api/report" => Report(), "/api/file" => await File(), "/api/history" => recorder.History, "/api/diagnostics" => Status(),
        _ => throw new InvalidOperationException("Commande ACARS inconnue.") };
    private object Status() => new { connected=client.Connected, server=ServerConfiguration.Get() ?? "Non configuré", sim=sim.Status, latest=sim.Latest, flight=recorder.Flight, track=recorder.Track, pending=recorder.Pending.Count+recorder.PendingEvents.Count, warning=recorder.Warning };
    private async Task<object> Login(JsonElement? body) { var configured=ServerConfiguration.Get() ?? throw new InvalidOperationException("Le serveur doit être configuré par un administrateur."); var b=body!.Value; return await client.SignIn(configured,b.GetProperty("login").GetString() ?? "",b.GetProperty("password").GetString() ?? ""); }
    private object Start(JsonElement? body) { if(sim.Latest is null) throw new InvalidOperationException("Attendez la position MSFS."); recorder.Start(client.Server,body!.Value.GetProperty("pirepId").GetString() ?? "",sim.Latest); return new { ok=true }; }
    private object Pause() { recorder.Pause(); return new {ok=true}; } private object Resume() { recorder.Resume(client.Server); return new {ok=true}; }
    private object Report() { var f=recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours."); return new {pirepId=f.PirepId,phase=f.Phase,distance=f.Distance,airborneSeconds=f.AirborneSeconds,fuelUsed=f.FuelUsed,landingRate=f.LandingRate,issues=f.Issues}; }
    private async Task<object> File() { await TelemetryService.SendPending(client, recorder); var f=recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours."); if (f.Phase != "IN") throw new InvalidOperationException("Attendez l'événement IN."); await client.Send($"pireps/{Uri.EscapeDataString(f.PirepId)}/file", new { distance=Math.Round(f.Distance,2), flight_time=Math.Max(1,(int)Math.Round(f.AirborneSeconds/60)), fuel_used=Math.Round(f.FuelUsed), block_time=Math.Max(1,(int)Math.Round(((f.BlockOn ?? DateTimeOffset.UtcNow)-f.BlockOff!.Value).TotalMinutes)), block_off_time=f.BlockOff, block_on_time=f.BlockOn, created_at=f.BlockOn, landing_rate=f.LandingRate }); recorder.Complete(); return new {ok=true}; }
}
