using System.Reflection;
using System.Text.Json;
using System.IO;
using System.Windows;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.Wpf;
using Promethee;

namespace PrometheeDesktop;

public static class WebDesktop
{
    [STAThread] public static void Main(string[] args) => new Application().Run(new PrometheeWindow());
}

public sealed class PrometheeWindow : Window
{
    private readonly PhpVmsClient client = new(); private readonly SimulatorConnectorHub sim = new(new SimConnectReader(), new XPlaneUdpConnector()); private readonly FlightRecorder recorder = new();
    private readonly TelemetryService telemetry; private readonly WebView2 web = new(); private bool ticking;
    public PrometheeWindow()
    {
        telemetry = new(sim, recorder, client); Title = "Hermès ACARS — Air Inter"; Width=1280; Height=840; MinWidth=900; MinHeight=620; Content=web;
        Loaded += async (_, _) => await StartAsync(); Closed += (_, _) => sim.Dispose();
        var timer = new System.Windows.Threading.DispatcherTimer { Interval = TimeSpan.FromSeconds(1) }; timer.Tick += async (_, _) => await Tick(); timer.Start();
    }
    private async Task StartAsync()
    {
        await web.EnsureCoreWebView2Async(); var core=web.CoreWebView2;
        core.Settings.AreDevToolsEnabled=false; core.Settings.AreDefaultContextMenusEnabled=false; core.WebMessageReceived += async (_, e) => await Handle(e.WebMessageAsJson);
        var assets = Path.Combine(AppContext.BaseDirectory,"wwwroot");
        if (!System.IO.File.Exists(Path.Combine(assets, "index.html"))) throw new InvalidOperationException("Ressources ACARS introuvables dans la publication.");
        core.SetVirtualHostNameToFolderMapping("promethee.local", assets, CoreWebView2HostResourceAccessKind.DenyCors);
        core.Navigate("https://promethee.local/index.html");
    }
    private async Task Tick() { if (ticking) return; ticking=true; try { await telemetry.Tick(); } finally { ticking=false; } }
    private async Task Handle(string raw)
    {
        string id=""; try { using var doc=JsonDocument.Parse(raw); var root=doc.RootElement; id=root.GetProperty("id").GetString() ?? ""; var path=root.GetProperty("path").GetString() ?? ""; var body=root.TryGetProperty("body",out var b)?b.Clone():(JsonElement?)null;
            Reply(id,true,await Route(path,body)); } catch(Exception e) { System.Diagnostics.Trace.WriteLine($"ACARS route error: {e}"); Reply(id,false,UserMessage(e)); }
    }
    private void Reply(string id,bool ok,object data) => web.CoreWebView2.PostWebMessageAsJson(JsonSerializer.Serialize(new {id,ok,data}));
    private async Task<object> Route(string path, JsonElement? body)
    {
        var uri = new Uri("https://promethee.local" + path); var route = uri.AbsolutePath;
        const string flightsPrefix = "/api/flights/";
        const string simbriefSessionSuffix = "/simbrief/session";
        const string simbriefImportSuffix = "/simbrief/import";
        if (route.StartsWith(flightsPrefix, StringComparison.Ordinal) && route.EndsWith(simbriefSessionSuffix, StringComparison.Ordinal)) {
            var flightId = route.Substring(flightsPrefix.Length, route.Length - flightsPrefix.Length - simbriefSessionSuffix.Length);
            return await client.Send("acars/flights/" + Uri.EscapeDataString(flightId) + "/simbrief/session", body
                ?? throw new InvalidOperationException("Paramètres SimBrief manquants."));
        }
        if (route.StartsWith(flightsPrefix, StringComparison.Ordinal) && route.EndsWith(simbriefImportSuffix, StringComparison.Ordinal)) {
            var flightId = route.Substring(flightsPrefix.Length, route.Length - flightsPrefix.Length - simbriefImportSuffix.Length);
            return await client.Send("acars/flights/" + Uri.EscapeDataString(flightId) + "/simbrief/import", body
                ?? throw new InvalidOperationException("Paramètres d’import SimBrief manquants."));
        }
        if (route.StartsWith("/api/operations/", StringComparison.Ordinal) && route.EndsWith("/aircraft", StringComparison.Ordinal))
            return await client.Send("promethee/acars/operations/" + Uri.EscapeDataString(route[16..^9]) + "/aircraft");
        if (route.StartsWith("/api/flights/", StringComparison.Ordinal) && route.EndsWith("/aircraft", StringComparison.Ordinal))
            return await client.Send("flights/" + Uri.EscapeDataString(route[13..^9]) + "/aircraft");
        if (route.StartsWith("/api/operations/", StringComparison.Ordinal) && route.EndsWith("/ofp", StringComparison.Ordinal))
            return await client.Send("promethee/acars/operations/" + Uri.EscapeDataString(route[16..^4]) + "/ofp");
        return route switch {
            "/api/status" => Status(), "/api/about" => About(), "/api/login" => await Login(body), "/api/config" => await ConfigureApiKey(body),
            "/api/user" => await client.Send("user"), "/api/bids" => await client.Send("user/bids"),
            "/api/operations" => await client.Send("promethee/acars/operations" + uri.Query), "/api/flights" => await client.Send("flights" + uri.Query),
            "/api/prefile" => await client.Send("pireps/prefile", body!.Value), "/api/start" => Start(body), "/api/pause" => Pause(), "/api/resume" => Resume(),
            "/api/sync" => new { sent=await TelemetryService.SendPending(client,recorder) }, "/api/report" => Report(), "/api/file" => await File(),
            "/api/history" => recorder.History, "/api/diagnostics" => Diagnostics(),
            _ => throw new InvalidOperationException("Commande ACARS inconnue.") };
    }
    private object Status() => new { connected=client.Connected, sim=sim.Status, detectedSimulators=SimulatorDetector.DetectRunning(), latest=sim.LatestSnapshot, flight=recorder.Flight, track=recorder.Track, pending=recorder.Pending.Count+recorder.PendingEvents.Count, remoteConfiguration=recorder.RemoteConfiguration, warning=recorder.Warning };
    private object About() => new { version=Assembly.GetExecutingAssembly().GetCustomAttribute<AssemblyInformationalVersionAttribute>()?.InformationalVersion ?? "dev", serverSource=ServerConfiguration.Source() };
    private object Diagnostics() => new {
        generatedAt=DateTimeOffset.UtcNow, server=client.Server, connected=client.Connected,
        simulator=sim.Status, detectedSimulators=SimulatorDetector.DetectRunning(),
        latest=sim.LatestSnapshot, flight=recorder.Flight,
        pendingPositions=recorder.Pending.Count, pendingEvents=recorder.PendingEvents.Count,
        remoteConfiguration=recorder.RemoteConfiguration, warning=recorder.Warning
    };
    private async Task<object> Login(JsonElement? body)
    {
        var user = await client.SignIn(ServerConfiguration.Get(), body!.Value.GetProperty("login").GetString() ?? "", body.Value.GetProperty("password").GetString() ?? "");
        return new { user, configuration = await LoadRemoteConfiguration() };
    }
    private async Task<object> ConfigureApiKey(JsonElement? body)
    {
        var value=body!.Value; client.ConfigureApiKey(value.GetProperty("server").GetString() ?? "", value.GetProperty("apiKey").GetString() ?? "");
        return new { user = await client.Send("user"), configuration = await LoadRemoteConfiguration() };
    }
    private async Task<RemoteAcarsConfiguration> LoadRemoteConfiguration()
    {
        RemoteAcarsConfiguration configuration;
        try {
            configuration = RemoteAcarsConfiguration.Parse(await client.Send("promethee/acars/configuration"));
        } catch (InvalidOperationException) {
            // The public site can be upgraded independently of the desktop
            // client. Authentication must remain usable during that rollout.
            configuration = RemoteAcarsConfiguration.Default;
        }
        recorder.ApplyRemoteConfiguration(configuration);
        return configuration;
    }
    private object Start(JsonElement? body) { if(sim.LatestSnapshot is null) throw new InvalidOperationException("Le simulateur n’est pas encore connecté."); recorder.Start(client.Server,body!.Value.GetProperty("pirepId").GetString() ?? "",sim.LatestSnapshot); return new { ok=true }; }
    private object Pause() { recorder.Pause(); return new {ok=true}; } private object Resume() { recorder.Resume(client.Server); return new {ok=true}; }
    private object Report() { var f=recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours."); return new {phase=f.Phase,distance=f.Distance,airborneMinutes=(int)Math.Round(f.AirborneSeconds/60)}; }
    private async Task<object> File() { await TelemetryService.SendPending(client, recorder); var f=recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours."); if (f.Phase != "IN") throw new InvalidOperationException("Attendez l’arrivée au parking avant de déposer le PIREP."); await client.Send($"pireps/{Uri.EscapeDataString(f.PirepId)}/file", new { distance=Math.Round(f.Distance,2), flight_time=Math.Max(1,(int)Math.Round(f.AirborneSeconds/60)), fuel_used=Math.Round(f.FuelUsed), block_time=Math.Max(1,(int)Math.Round(((f.BlockOn ?? DateTimeOffset.UtcNow)-f.BlockOff!.Value).TotalMinutes)), block_off_time=f.BlockOff, block_on_time=f.BlockOn, created_at=f.BlockOn, landing_rate=f.LandingRate }); recorder.Complete(); return new {ok=true}; }
    private static string UserMessage(Exception e) => e is InvalidOperationException ? e.Message : "Une erreur inattendue est survenue. Réessayez plus tard.";
}
