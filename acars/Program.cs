using System.Text.Json;
using System.Net;
using System.Net.Sockets;
using Microsoft.AspNetCore.Builder;
using Promethee;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;

var configuredUrl = Environment.GetEnvironmentVariable("PROMETHEE_ACARS_URL");
var builder = WebApplication.CreateBuilder(new WebApplicationOptions {
    Args = args,
    ContentRootPath = AppContext.BaseDirectory,
    WebRootPath = Path.Combine(AppContext.BaseDirectory, "wwwroot")
});
builder.WebHost.UseUrls(configuredUrl ?? FindAvailableLocalUrl());
builder.Services.AddSingleton<PhpVmsClient>();
builder.Services.AddSingleton<SimConnectReader>();
builder.Services.AddSingleton<FlightRecorder>();
builder.Services.AddHostedService<TelemetryWorker>();
var app = builder.Build();

// The client is a local web application; open its cockpit automatically for pilots.
if (!string.Equals(Environment.GetEnvironmentVariable("PROMETHEE_ACARS_NO_BROWSER"), "1", StringComparison.Ordinal)) {
    app.Lifetime.ApplicationStarted.Register(() => {
        try { System.Diagnostics.Process.Start(new System.Diagnostics.ProcessStartInfo(app.Urls.First()) { UseShellExecute = true }); }
        catch { /* A browser is optional; the URL is still shown in the console. */ }
    });
}

app.UseDefaultFiles();
app.UseStaticFiles();

app.MapGet("/api/status", (PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) =>
{
    lock (recorder.Gate) {
        return Results.Json(new {
            connected = client.Connected,
            server = client.Server,
            sim = sim.Status,
            latest = sim.Latest,
            flight = recorder.Flight,
            track = recorder.Track,
            pending = recorder.Pending.Count + recorder.PendingEvents.Count,
            recoveryAvailable = recorder.RecoveryAvailable,
            recovery = recorder.GetRecoveryInfo(),
            warning = recorder.Warning
        });
    }
});

app.MapPost("/api/login", async (LoginRequest input, PhpVmsClient client, FlightRecorder recorder) => {
    var user = await client.SignIn(ServerConfiguration.Get(), input.Login, input.Password);
    var configuration = await LoadRemoteConfiguration(client, recorder);
    return Results.Json(new { user, configuration });
});

app.MapGet("/api/user", async (PhpVmsClient client) => Results.Json(await client.Send("user")));
app.MapGet("/api/bids", async (PhpVmsClient client) => Results.Json(await client.Send("user/bids")));
app.MapGet("/api/operations", async (HttpRequest request, PhpVmsClient client) =>
    Results.Json(await client.Send("acars/operations" + request.QueryString.Value)));
app.MapGet("/api/operations/{id}/aircraft", async (string id, PhpVmsClient client) =>
    Results.Json(await client.Send("acars/operations/" + Uri.EscapeDataString(id) + "/aircraft")));
app.MapGet("/api/flights", async (HttpRequest request, PhpVmsClient client) =>
    Results.Json(await client.Send("flights" + request.QueryString.Value)));
app.MapGet("/api/flights/{id}/aircraft", async (string id, PhpVmsClient client) =>
    Results.Json(await client.Send("flights/" + Uri.EscapeDataString(id) + "/aircraft")));
app.MapPost("/api/flights/{id}/simbrief/session", async (string id, JsonElement body, PhpVmsClient client) =>
    Results.Json(await client.Send("acars/flights/" + Uri.EscapeDataString(id) + "/simbrief/session", body)));
app.MapPost("/api/flights/{id}/simbrief/import", async (string id, JsonElement body, PhpVmsClient client) =>
    Results.Json(await client.Send("acars/flights/" + Uri.EscapeDataString(id) + "/simbrief/import", body)));

app.MapPost("/api/prefile", async (JsonElement body, PhpVmsClient client) =>
    Results.Json(await client.Send("pireps/prefile", body)));

app.MapPost("/api/start", (StartRequest input, PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) =>
{
    if (string.IsNullOrWhiteSpace(input.PirepId)) throw new InvalidOperationException("Saisissez l'identifiant du PIREP pré-déposé.");
    if (sim.Latest is null) throw new InvalidOperationException("Le simulateur ne fournit pas encore de position.");
    recorder.Start(client.Server, input.PirepId.Trim(), sim.Latest);
    return Results.Ok();
});

app.MapPost("/api/pause", (FlightRecorder recorder) => { recorder.Pause(); return Results.Ok(); });
app.MapPost("/api/resume", (PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) => {
    if (!client.Connected) throw new InvalidOperationException("Connectez-vous à votre compte Air Inter avant de reprendre.");
    if (sim.Latest is null) throw new InvalidOperationException("Reconnectez le simulateur avant de reprendre.");
    recorder.Resume(client.Server);
    return Results.Ok();
});
app.MapGet("/api/recovery", (FlightRecorder recorder) => Results.Json(new {
    available = recorder.RecoveryAvailable,
    info = recorder.GetRecoveryInfo(),
    flight = recorder.RecoveryAvailable ? recorder.Flight : null,
    timeline = recorder.RecoveryAvailable ? recorder.Flight?.Timeline : null,
    journal = recorder.RecoveryAvailable ? recorder.Flight?.Journal : null,
    track = recorder.RecoveryAvailable ? recorder.Track : []
}));
app.MapPost("/api/recovery/resume", (PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) => {
    if (!recorder.RecoveryAvailable) throw new InvalidOperationException("Aucun vol interrompu à reprendre.");
    if (!client.Connected) throw new InvalidOperationException("Connectez-vous à votre compte Air Inter avant de reprendre ce vol.");
    if (sim.Latest is null) throw new InvalidOperationException("Reconnectez le simulateur avant de reprendre ce vol.");
    recorder.Resume(client.Server);
    return Results.Ok(new { flight = recorder.Flight });
});
app.MapPost("/api/recovery/abandon", (FlightRecorder recorder) => {
    recorder.AbandonRecovery();
    return Results.Ok(new { archived = true });
});

app.MapPost("/api/sync", async (PhpVmsClient client, FlightRecorder recorder) =>
{
    var count = await TelemetryWorker.SendPending(client, recorder);
    return Results.Json(new { sent = count });
});

app.MapGet("/api/report", (FlightRecorder recorder) =>
{
    lock (recorder.Gate) {
        var flight = recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours.");
        var block = flight.BlockOff is null ? 0 : (int)Math.Round(((flight.BlockOn ?? DateTimeOffset.UtcNow) - flight.BlockOff.Value).TotalMinutes);
        return Results.Json(new {
            pirepId = flight.PirepId, phase = flight.Phase, distance = Math.Round(flight.Distance, 2),
            airborneMinutes = (int)Math.Round(flight.AirborneSeconds / 60), blockMinutes = block,
            fuelUsed = Math.Round(flight.FuelUsed), landingRate = flight.LandingRate, issues = flight.Issues
        });
    }
});
app.MapGet("/api/history", (FlightRecorder recorder) => Results.Json(recorder.History));
app.MapGet("/api/rules", (FlightRecorder recorder) => Results.Json(recorder.Rules));
app.MapPost("/api/rules", (AcarsRules rules, FlightRecorder recorder) => { recorder.SetRules(rules); return Results.Ok(recorder.Rules); });
app.MapGet("/api/diagnostics", (PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) =>
    Results.Json(new { generatedAt = DateTimeOffset.UtcNow, server = client.Server, connected = client.Connected,
        simulator = sim.Status, latest = sim.Latest, flight = recorder.Flight, pendingPositions = recorder.Pending.Count,
        pendingEvents = recorder.PendingEvents.Count, remoteConfiguration = recorder.RemoteConfiguration, warning = recorder.Warning }));

app.MapPost("/api/file", async (PhpVmsClient client, FlightRecorder recorder) =>
{
    await TelemetryWorker.SendPending(client, recorder);
    FlightState flight;
    lock (recorder.Gate) {
        flight = recorder.Flight ?? throw new InvalidOperationException("Aucun vol en cours.");
        if (recorder.Pending.Count > 0 || recorder.PendingEvents.Count > 0) throw new InvalidOperationException("La télémétrie n'est pas entièrement synchronisée.");
    }
    var report = new Dictionary<string, object> {
        ["distance"] = Math.Round(flight.Distance, 2),
        ["flight_time"] = Math.Max(1, (int)Math.Round(flight.AirborneSeconds / 60)),
        ["fuel_used"] = Math.Round(flight.FuelUsed, 0),
        ["block_time"] = Math.Max(1, (int)Math.Round(((flight.BlockOn ?? DateTimeOffset.UtcNow) - flight.BlockOff!.Value).TotalMinutes)),
        ["block_off_time"] = flight.BlockOff!.Value,
        ["block_on_time"] = flight.BlockOn!.Value,
        ["created_at"] = flight.BlockOn!.Value
    };
    if (flight.LandingRate is not null) report["landing_rate"] = flight.LandingRate.Value;
    await client.Send("pireps/" + Uri.EscapeDataString(flight.PirepId) + "/file", report);
    recorder.Complete();
    return Results.Ok();
});

app.MapFallbackToFile("index.html");
app.Run();

static string FindAvailableLocalUrl()
{
    for (var port = 1974; port <= 1984; port++) {
        try {
            using var probe = new TcpListener(IPAddress.Loopback, port);
            probe.Start();
            return $"http://127.0.0.1:{port}";
        } catch (SocketException) { }
    }
    throw new InvalidOperationException("Les ports locaux 1974 à 1984 sont déjà utilisés. Fermez une autre instance de Promethee ACARS.");
}

static async Task<RemoteAcarsConfiguration> LoadRemoteConfiguration(PhpVmsClient client, FlightRecorder recorder)
{
    RemoteAcarsConfiguration configuration;
    try {
        configuration = RemoteAcarsConfiguration.Parse(await client.Send("promethee/acars/configuration"));
    } catch (InvalidOperationException) {
        configuration = RemoteAcarsConfiguration.Default;
    }
    recorder.ApplyRemoteConfiguration(configuration);
    return configuration;
}

public sealed record LoginRequest(string Login, string Password);
public sealed record StartRequest(string PirepId);

public sealed class TelemetryWorker(SimConnectReader sim, FlightRecorder recorder, PhpVmsClient client) : BackgroundService
{
    protected override async Task ExecuteAsync(CancellationToken stoppingToken)
    {
        while (!stoppingToken.IsCancellationRequested) {
            sim.Poll();
            if (sim.Latest is not null) recorder.Capture(sim.Latest);
            if (client.Connected) {
                try { await SendPending(client, recorder); } catch { }
            }
            await Task.Delay(1000, stoppingToken);
        }
    }

    public static async Task<int> SendPending(PhpVmsClient client, FlightRecorder recorder)
    {
        await recorder.NetworkGate.WaitAsync();
        try {
        List<Envelope> pending;
        List<AcarsEvent> events;
        FlightState? flight;
        lock (recorder.Gate) {
            flight = recorder.Flight;
            pending = recorder.Pending.Take(30).ToList();
            events = recorder.PendingEvents.Take(20).ToList();
        }
        if (flight is null || (pending.Count == 0 && events.Count == 0)) return 0;
        if (pending.Count > 0) {
            // Promethee keeps the detailed, idempotent telemetry separately.
            // The standard phpVMS ACARS endpoint below remains the live-map
            // source and is intentionally not made dependent on this archive.
            try {
                await client.Send("promethee/pireps/" + Uri.EscapeDataString(flight.PirepId) + "/telemetry", new {
                    samples = pending.Select(x => new {
                        sample_id = x.Sample.SampleId,
                        recorded_at = x.Sample.RecordedAt,
                        lat = x.Sample.Lat,
                        lon = x.Sample.Lon,
                        altitude_msl = x.Sample.Altitude,
                        agl = x.Sample.Agl,
                        ias = x.Sample.Ias,
                        gs = x.Sample.Gs,
                        vs = x.Sample.Vs,
                        heading = x.Sample.Heading,
                        fuel = x.Sample.Fuel,
                        bank = x.Sample.Bank,
                        on_ground = x.Sample.OnGround,
                        gear_down = x.Sample.GearDown,
                        landing_flaps = x.Sample.Flaps > 0,
                        thrust_stable = x.Sample.ThrustStable
                    })
                });
            } catch (InvalidOperationException) {
                // A server can be upgraded independently of the desktop app.
                // Standard ACARS sync is still useful and remains idempotent.
            }
            await client.Send("pireps/" + Uri.EscapeDataString(flight.PirepId) + "/acars/positions", new {
                positions = pending.Select(x => new {
                id = x.Sample.SampleId,
                lat = x.Sample.Lat,
                lon = x.Sample.Lon,
                altitude_msl = x.Sample.Altitude,
                altitude_agl = x.Sample.Agl,
                gs = x.Sample.Gs,
                vs = x.Sample.Vs,
                heading = x.Sample.Heading,
                fuel = x.Sample.Fuel,
                sim_time = x.Sample.RecordedAt,
                created_at = x.Sample.RecordedAt
                })
            });
            recorder.AcknowledgePositions(pending.Select(x => x.Sample.SampleId));
        }
        if (events.Count > 0) {
            await client.Send("pireps/" + Uri.EscapeDataString(flight.PirepId) + "/acars/events", new {
                events = events.Select(x => new { id = x.EventId, @event = x.Name, lat = x.Lat, lon = x.Lon, created_at = x.OccurredAt })
            });
            recorder.AcknowledgeEvents(events.Select(x => x.EventId));
        }
        return pending.Count + events.Count;
        } finally {
            recorder.NetworkGate.Release();
        }
    }
}
