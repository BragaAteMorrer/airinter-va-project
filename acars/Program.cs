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
            pending = recorder.Pending.Count,
            warning = recorder.Warning
        });
    }
});

app.MapPost("/api/config", async (ConfigRequest input, PhpVmsClient client) =>
{
    client.Configure(input.Server, input.ApiKey);
    var user = await client.Send("user");
    return Results.Json(new { user });
});

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

app.MapPost("/api/max-ias", (MaxIasRequest input, FlightRecorder recorder) =>
{
    recorder.MaxIas = input.MaxIas is > 0 and < 2000 ? input.MaxIas : null;
    return Results.Ok();
});

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
        if (recorder.Pending.Count > 0) throw new InvalidOperationException("La télémétrie n'est pas entièrement synchronisée.");
    }
    await client.Send("pireps/" + Uri.EscapeDataString(flight.PirepId) + "/file", new {
        distance = Math.Round(flight.Distance, 2),
        flight_time = Math.Max(1, (int)Math.Round(flight.AirborneSeconds / 60)),
        fuel_used = Math.Round(flight.FuelUsed, 0),
        landing_rate = flight.LandingRate
    });
    recorder.Complete();
    return Results.Ok();
});

app.MapFallbackToFile("index.html");
app.Run();

public sealed record ConfigRequest(string Server, string ApiKey);
public sealed record StartRequest(string PirepId);
public sealed record MaxIasRequest(double? MaxIas);

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
        List<Envelope> pending;
        FlightState? flight;
        lock (recorder.Gate) {
            flight = recorder.Flight;
            pending = recorder.Pending.Take(100).ToList();
        }
        if (flight is null || pending.Count == 0) return 0;
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
                on_ground = x.Sample.OnGround,
                bank = x.Sample.Bank,
                gear_down = x.Sample.GearDown,
                max_ias = x.MaxIas
            })
        });
        recorder.Acknowledge(pending.Select(x => x.Sample.SampleId));
        return pending.Count;
    }
}
