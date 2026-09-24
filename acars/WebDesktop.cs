using System.Net.Http;
using System.Reflection;
using System.Text.Json;
using System.IO;
using System.Diagnostics;
using System.Windows;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.Wpf;
using System.Windows.Media.Imaging;
using Promethee;
using Promethee.Acars;

namespace PrometheeDesktop;

public static class WebDesktop
{
    [STAThread] public static void Main(string[] args) => new Application().Run(new PrometheeWindow());
}

public sealed class PrometheeWindow : Window
{
    private readonly PhpVmsClient client = new(); private readonly SimulatorConnectorHub sim = new(
        new SimConnectReader(),
        new XPlaneUdpConnector(),
        new FsuipcConnector(SimulatorKind.FlightSimulator2004, "Microsoft Flight Simulator 2004"),
        new FsuipcConnector(SimulatorKind.FlightSimulatorX, "Microsoft Flight Simulator X"),
        new FsuipcConnector(SimulatorKind.Prepar3D, "Prepar3D")); private readonly FlightRecorder recorder = new();
    private readonly TelemetryService telemetry; private readonly WebView2 web = new(); private bool ticking;
    public PrometheeWindow()
    {
        telemetry = new(sim, recorder, client); Title = "Hermès ACARS — Air Inter";
        Icon = BitmapFrame.Create(new Uri("pack://application:,,,/assets/hermes.ico", UriKind.Absolute));
        Width=1280; Height=840; MinWidth=900; MinHeight=620; WindowStartupLocation=WindowStartupLocation.CenterScreen; WindowState=WindowState.Maximized; Content=web;
        Loaded += async (_, _) => { await StartAsync(); await CheckForUpdatesAsync(); }; Closed += (_, _) => sim.Dispose();
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
    private async Task CheckForUpdatesAsync()
    {
        try {
            using var http = new HttpClient { Timeout = TimeSpan.FromSeconds(8) };
            var release = await UpdateService.CheckAsync(http, new Uri(ServerConfiguration.Get()));
            if (release is null || !UpdateService.IsNewer(release.Version)) return;

            var notes = string.IsNullOrWhiteSpace(release.Notes) ? "" : "\n\n" + release.Notes.Trim();
            if (notes.Length > 900) notes = notes[..900] + "…";
            var prompt = $"Une nouvelle version d’Hermès est disponible.\n\nVersion installée : {UpdateService.CurrentVersion}\nNouvelle version : {release.Version}\nCanal : {release.Channel}{notes}\n\nTélécharger et installer maintenant ?";
            var buttons = release.Mandatory ? MessageBoxButton.OKCancel : MessageBoxButton.YesNo;
            var result = MessageBox.Show(this, prompt, release.Mandatory ? "Mise à jour Hermès requise" : "Mise à jour Hermès disponible", buttons, MessageBoxImage.Information);
            var accepted = release.Mandatory ? result == MessageBoxResult.OK : result == MessageBoxResult.Yes;
            if (!accepted) return;

            Title = $"Hermès ACARS — téléchargement de la version {release.Version}…";
            var installer = await UpdateService.DownloadAndVerifyAsync(release);
            Title = "Hermès ACARS — Air Inter";
            UpdateService.LaunchInstaller(installer);
            Application.Current.Shutdown();
        } catch (Exception exception) {
            Trace.WriteLine($"Hermès update check failed: {exception}");
            Title = "Hermès ACARS — Air Inter";
        }
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
        if (route.StartsWith("/api/v1/", StringComparison.Ordinal)) {
            var remote = route["/api/".Length..];
            if (body.HasValue && body.Value.ValueKind != JsonValueKind.Null) {
                return await client.Send(remote + uri.Query, body.Value);
            }
            return await client.Send(remote + uri.Query);
        }

        return route switch {
            "/api/status" => Status(), "/api/about" => About(), "/api/login" => await Login(body),
            "/api/start" => Start(body), "/api/pause" => Pause(), "/api/resume" => Resume(),
            "/api/recovery" => Recovery(), "/api/recovery/resume" => ResumeRecovery(), "/api/recovery/abandon" => AbandonRecovery(),
            "/api/sync" => new { sent=await telemetry.SyncNow() }, "/api/report" => Report(), "/api/review" => recorder.GetReview() ?? throw new InvalidOperationException("Aucun vol en cours."), "/api/capabilities" => sim.AircraftCapabilities ?? throw new InvalidOperationException("Aucun profil de capacités avion disponible."), "/api/file" => await File(),
            "/api/history" => recorder.History, "/api/diagnostics" => Diagnostics(), "/api/update/check" => await CheckUpdateStatusAsync(), "/api/open-external" => OpenExternal(body),
            _ => throw new InvalidOperationException("Commande ACARS inconnue.") };
    }
    private async Task<object> CheckUpdateStatusAsync()
    {
        try {
            using var http = new HttpClient { Timeout = TimeSpan.FromSeconds(8) };
            var release = await UpdateService.CheckAsync(http, new Uri(ServerConfiguration.Get()));
            if (release is null) return new { ok = true, updateAvailable = false, currentVersion = UpdateService.CurrentVersion };
            return new {
                ok = true,
                updateAvailable = UpdateService.IsNewer(release.Version),
                currentVersion = UpdateService.CurrentVersion,
                latestVersion = release.Version,
                channel = release.Channel,
                releaseUrl = release.ReleaseUrl
            };
        } catch (Exception exception) {
            return new { ok = false, updateAvailable = false, currentVersion = UpdateService.CurrentVersion, error = exception.Message };
        }
    }

    private object OpenExternal(JsonElement? body)
    {
        var raw = body?.GetProperty("url").GetString() ?? throw new InvalidOperationException("URL externe manquante.");
        if (!Uri.TryCreate(raw, UriKind.Absolute, out var uri) || uri.Scheme != Uri.UriSchemeHttps
            || (uri.Host != "www.simbrief.com" && uri.Host != "dispatch.simbrief.com"))
            throw new InvalidOperationException("Hermès refuse d’ouvrir cette URL externe.");
        Process.Start(new ProcessStartInfo(uri.AbsoluteUri) { UseShellExecute = true });
        return new { ok = true };
    }
    private object Status() => new {
        connected=client.Connected,
        sim=sim.Status,
        detectedSimulators=SimulatorDetector.DetectRunning(),
        activeConnector=sim.Active is null ? null : new {
            id=sim.Active.Descriptor.ConnectorId,
            name=sim.Active.Descriptor.DisplayName,
            state=sim.Active.ConnectionState.ToString(),
            capabilities=sim.Active.Descriptor.Capabilities.ToString(),
            experimental=sim.Active.Descriptor.IsExperimental
        },
        connectors=sim.Connectors,
        simLinkState=sim.LinkState,
        simLostAt=sim.LostAt,
        simRecoveredAt=sim.RecoveredAt,
        latest=sim.LatestSnapshot,
        aircraftCapabilities=sim.AircraftCapabilities,
        flight=recorder.Flight,
        review=recorder.GetReview(),
        track=recorder.Track,
        pending=recorder.Pending.Count+recorder.PendingEvents.Count,
        recoveryAvailable=recorder.RecoveryAvailable,
        recovery=recorder.GetRecoveryInfo(),
        syncState=telemetry.SyncState,
        lastSuccessfulSyncAt=telemetry.LastSuccessfulSyncAt,
        nextSyncAttemptAt=telemetry.NextSyncAttemptAt,
        syncFailures=telemetry.ConsecutiveFailures,
        syncError=telemetry.LastSyncError,
        remoteConfiguration=recorder.RemoteConfiguration,
        warning=recorder.Warning
    };
    private object About() => new {
        version=Assembly.GetExecutingAssembly().GetCustomAttribute<AssemblyInformationalVersionAttribute>()?.InformationalVersion ?? "dev",
        product="Hermès — Air Inter Virtual Airlines"
    };
    private object Diagnostics() => new {
        generatedAt=DateTimeOffset.UtcNow, configuredServer=ServerConfiguration.Get(), serverSource=ServerConfiguration.Source(), loginEndpoint=ServerConfiguration.Get() + "/api/acars/session", activeServer=client.Server, connected=client.Connected,
        simulator=sim.Status, detectedSimulators=SimulatorDetector.DetectRunning(),
        activeConnector=sim.Active?.Descriptor, connectors=sim.Connectors,
        simLinkState=sim.LinkState, simLostAt=sim.LostAt, simRecoveredAt=sim.RecoveredAt,
        latest=sim.LatestSnapshot, aircraftCapabilities=sim.AircraftCapabilities, flight=recorder.Flight, review=recorder.GetReview(),
        pendingPositions=recorder.Pending.Count, pendingEvents=recorder.PendingEvents.Count,
        syncState=telemetry.SyncState, lastSuccessfulSyncAt=telemetry.LastSuccessfulSyncAt,
        nextSyncAttemptAt=telemetry.NextSyncAttemptAt, syncFailures=telemetry.ConsecutiveFailures,
        syncError=telemetry.LastSyncError,
        remoteConfiguration=recorder.RemoteConfiguration, warning=recorder.Warning
    };
    private async Task<object> Login(JsonElement? body)
    {
        await client.SignIn(ServerConfiguration.Get(), body!.Value.GetProperty("login").GetString() ?? "", body.Value.GetProperty("password").GetString() ?? "");
        var user = await client.Send("v1/me");
        return new { user, configuration = await LoadRemoteConfiguration() };
    }
    private async Task<RemoteAcarsConfiguration> LoadRemoteConfiguration()
    {
        RemoteAcarsConfiguration configuration;
        try {
            configuration = RemoteAcarsConfiguration.Parse(await client.Send("v1/hermes/configuration"));
        } catch (InvalidOperationException) {
            // The public site can be upgraded independently of the desktop
            // client. Authentication must remain usable during that rollout.
            configuration = RemoteAcarsConfiguration.Default;
        }
        recorder.ApplyRemoteConfiguration(configuration);
        return configuration;
    }
    private object Start(JsonElement? body) {
        var snapshot = sim.LatestSnapshot ?? throw new InvalidOperationException("Le simulateur n’est pas encore connecté.");
        if (snapshot.OnGround != true)
            throw new InvalidOperationException("START FLIGHT refusé : l’avion doit être au sol.");
        if (snapshot.ParkingBrake == false)
            throw new InvalidOperationException("START FLIGHT refusé : serrez le frein de parc.");
        if (snapshot.EnginesRunning?.Any(running => running) == true)
            throw new InvalidOperationException("START FLIGHT refusé : arrêtez les moteurs avant de commencer la préparation ACARS.");
        var value = body!.Value;
        var operationId = value.TryGetProperty("operationId", out var operation) ? operation.GetString() : null;
        recorder.Start(client.Server, value.GetProperty("pirepId").GetString() ?? "", snapshot, operationId);
        return new { ok=true, operationId };
    }
    private object Pause() { recorder.Pause(); return new {ok=true}; }
    private object Resume()
    {
        if (!client.Connected) throw new InvalidOperationException("Reconnectez-vous à votre compte Air Inter avant de reprendre.");
        if (sim.LatestSnapshot is null) throw new InvalidOperationException("Le simulateur doit être reconnecté avant de reprendre l’enregistrement.");
        recorder.Resume(client.Server);
        return new {ok=true};
    }

    private object Recovery() => new {
        available = recorder.RecoveryAvailable,
        info = recorder.GetRecoveryInfo(),
        flight = recorder.RecoveryAvailable ? recorder.Flight : null,
        timeline = recorder.RecoveryAvailable ? recorder.Flight?.Timeline : null,
        journal = recorder.RecoveryAvailable ? recorder.Flight?.Journal : null,
        observations = recorder.RecoveryAvailable ? recorder.Flight?.Observations : null,
        review = recorder.RecoveryAvailable ? recorder.GetReview() : null,
        track = recorder.RecoveryAvailable ? recorder.Track : []
    };

    private object ResumeRecovery()
    {
        if (!recorder.RecoveryAvailable) throw new InvalidOperationException("Aucun vol interrompu à reprendre.");
        if (!client.Connected) throw new InvalidOperationException("Connectez-vous à votre compte Air Inter avant de reprendre ce vol.");
        if (sim.LatestSnapshot is null) throw new InvalidOperationException("Reconnectez le simulateur avant de reprendre ce vol.");
        recorder.Resume(client.Server);
        return new { ok = true, flight = recorder.Flight, simulator = sim.Status };
    }

    private object AbandonRecovery()
    {
        recorder.AbandonRecovery();
        return new { ok = true, archived = true };
    }

    private object Report() => recorder.GetReview() ?? throw new InvalidOperationException("Aucun vol en cours.");
    private async Task<object> File()
    {
        await TelemetryService.SendPending(client, recorder);
        var f = recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours.");
        if (f.Phase != "IN") throw new InvalidOperationException("Attendez l’arrivée au parking avant de déposer le PIREP.");
        var review = recorder.GetReview() ?? throw new InvalidOperationException("Flight Review indisponible.");
        await client.Send($"pireps/{Uri.EscapeDataString(f.PirepId)}/file", new {
            distance=Math.Round(f.Distance,2),
            flight_time=Math.Max(1,(int)Math.Round(f.AirborneSeconds/60)),
            fuel_used=Math.Round(f.FuelUsed),
            block_time=Math.Max(1,(int)Math.Round(((f.BlockOn ?? DateTimeOffset.UtcNow)-f.BlockOff!.Value).TotalMinutes)),
            block_off_time=f.BlockOff,
            block_on_time=f.BlockOn,
            created_at=f.BlockOn,
            landing_rate=f.LandingRate
        });
        recorder.Complete();
        return new { ok=true, review };
    }
    private static string UserMessage(Exception e) => e is InvalidOperationException ? e.Message : "Une erreur inattendue est survenue. Réessayez plus tard.";
}
