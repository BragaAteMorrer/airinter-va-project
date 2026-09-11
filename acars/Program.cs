using System.Text.Json;
using Promethee;

var builder = WebApplication.CreateBuilder(new WebApplicationOptions {
    Args = args,
    ContentRootPath = AppContext.BaseDirectory,
    WebRootPath = Path.Combine(AppContext.BaseDirectory, "wwwroot")
});
builder.WebHost.UseUrls(Environment.GetEnvironmentVariable("PROMETHEE_ACARS_URL") ?? "http://127.0.0.1:1974");
builder.Services.AddSingleton<PhpVmsClient>();
builder.Services.AddSingleton<SimConnectReader>();
builder.Services.AddSingleton<FlightRecorder>();
builder.Services.AddHostedService<TelemetryWorker>();
var app = builder.Build();

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
            pending = recorder.Pending.Count + recorder.PendingEvents.Count,
            warning = recorder.Warning
        });
    }
});

app.MapPost("/api/config", async (ConfigRequest input, PhpVmsClient client) =>
{
    client.ConfigureApiKey(input.Server, input.ApiKey);
    var user = await client.Send("user");
    return Results.Json(new { user });
});
app.MapPost("/api/login", async (LoginRequest input, PhpVmsClient client) =>
    Results.Json(new { user = await client.SignIn(input.Server, input.Login, input.Password) }));

app.MapGet("/api/user", async (PhpVmsClient client) => Results.Json(await client.Send("user")));
app.MapGet("/api/bids", async (PhpVmsClient client) => Results.Json(await client.Send("user/bids")));
app.MapGet("/api/flights", async (string? search, PhpVmsClient client) =>
    Results.Json(await client.Send("flights" + (string.IsNullOrWhiteSpace(search) ? "" : "?search=" + Uri.EscapeDataString(search)))));
app.MapGet("/api/flights/{id}/aircraft", async (string id, PhpVmsClient client) =>
    Results.Json(await client.Send("flights/" + Uri.EscapeDataString(id) + "/aircraft")));

app.MapPost("/api/prefile", async (JsonElement body, PhpVmsClient client) =>
    Results.Json(await client.Send("pireps/prefile", body)));

app.MapPost("/api/start", (StartRequest input, PhpVmsClient client, SimConnectReader sim, FlightRecorder recorder) =>
{
    if (sim.Latest is null) throw new InvalidOperationException("Le simulateur ne fournit pas encore de position.");
    recorder.Start(client.Server, input.PirepId, sim.Latest);
    return Results.Ok();
});

app.MapPost("/api/pause", (FlightRecorder recorder) => { recorder.Pause(); return Results.Ok(); });
app.MapPost("/api/resume", (PhpVmsClient client, FlightRecorder recorder) => { recorder.Resume(client.Server); return Results.Ok(); });

app.MapPost("/api/sync", async (PhpVmsClient client, FlightRecorder recorder) =>
{
    var count = await TelemetryWorker.SendPending(client, recorder);
    return Results.Json(new { sent = count });
});

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

public sealed record ConfigRequest(string Server, string ApiKey);
public sealed record LoginRequest(string Server, string Login, string Password);
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
