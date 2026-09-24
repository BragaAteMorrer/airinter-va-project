using System.Text.Json;
using System.Text.Json.Serialization;

namespace Promethee;

public sealed record PresenceHeartbeat(
    [property: JsonPropertyName("simulator")] string Simulator,
    [property: JsonPropertyName("connector")] string? Connector,
    [property: JsonPropertyName("phase")] string? Phase,
    [property: JsonPropertyName("lat")] double? Latitude,
    [property: JsonPropertyName("lon")] double? Longitude,
    [property: JsonPropertyName("altitude_msl")] double? AltitudeMslFeet,
    [property: JsonPropertyName("hermes_version")] string HermesVersion,
    [property: JsonPropertyName("recording")] bool Recording,
    [property: JsonPropertyName("client_state")] string ClientState);

public interface IPresenceTransport
{
    bool Connected { get; }
    Task<JsonElement> Send(string path, object? body = null);
}

public sealed class PhpVmsPresenceTransport(PhpVmsClient client) : IPresenceTransport
{
    public bool Connected => client.Connected;
    public Task<JsonElement> Send(string path, object? body = null) => client.Send(path, body);
}

/// <summary>
/// Ephemeral Air Inter Network heartbeat. Presence is deliberately not queued:
/// when Hermès or the network disappears, Prométhée must naturally age the
/// crew offline instead of replaying stale "online" heartbeats later.
/// </summary>
public sealed class HermesPresence
{
    public static readonly TimeSpan HeartbeatInterval = TimeSpan.FromSeconds(15);

    private readonly IPresenceTransport transport;
    private readonly Func<DateTimeOffset> clock;
    private string? lastOperationId;
    private DateTimeOffset nextHeartbeatAt = DateTimeOffset.MinValue;
    private JsonElement? lastNetwork;

    public DateTimeOffset? LastHeartbeatAt { get; private set; }
    public string? LastError { get; private set; }
    public JsonElement? LastNetwork => lastNetwork?.Clone();

    public HermesPresence(PhpVmsClient client)
        : this(new PhpVmsPresenceTransport(client), null) {}

    public HermesPresence(IPresenceTransport transport, Func<DateTimeOffset>? clock = null)
    {
        this.transport = transport;
        this.clock = clock ?? (() => DateTimeOffset.UtcNow);
    }

    public async Task<JsonElement?> HeartbeatIfDueAsync(string operationId, PresenceHeartbeat heartbeat)
    {
        if (!transport.Connected || string.IsNullOrWhiteSpace(operationId)) return LastNetwork;

        var now = clock();
        if (string.Equals(lastOperationId, operationId, StringComparison.Ordinal)
            && now < nextHeartbeatAt) return LastNetwork;

        try
        {
            await HeartbeatNowAsync(operationId, heartbeat);
            return LastNetwork;
        }
        catch (Exception exception) when (exception is InvalidOperationException or HttpRequestException or TaskCanceledException)
        {
            LastError = exception.Message;
            nextHeartbeatAt = now.Add(HeartbeatInterval);
            return LastNetwork;
        }
    }

    public async Task<JsonElement> HeartbeatNowAsync(string operationId, PresenceHeartbeat heartbeat)
    {
        if (!transport.Connected) throw new InvalidOperationException("Prométhée hors ligne.");
        if (string.IsNullOrWhiteSpace(operationId) || operationId.Length > 128)
            throw new InvalidOperationException("Opération de présence invalide.");

        var response = await transport.Send(
            $"v1/operations/{Uri.EscapeDataString(operationId)}/presence/heartbeat",
            heartbeat);

        var now = clock();
        lastOperationId = operationId;
        LastHeartbeatAt = now;
        nextHeartbeatAt = now.Add(HeartbeatInterval);
        LastError = null;

        if (response.ValueKind == JsonValueKind.Object
            && response.TryGetProperty("network", out var network))
            lastNetwork = network.Clone();

        return response;
    }

    public async Task<JsonElement> RefreshNetworkAsync()
    {
        if (!transport.Connected) throw new InvalidOperationException("Prométhée hors ligne.");

        var network = await transport.Send("v1/network/presence");
        lastNetwork = network.Clone();
        LastError = null;
        return network;
    }

    public static string SimulatorId(SimulatorConnectorHub hub)
    {
        var active = hub.Active?.Descriptor.Kind ?? SimulatorKind.Unknown;
        if (active == SimulatorKind.MicrosoftFlightSimulator)
        {
            var detected = SimulatorDetector.DetectRunning()
                .Where(x => x.Kind == SimulatorKind.MicrosoftFlightSimulator)
                .Select(x => x.DisplayName)
                .ToArray();
            if (detected.Any(x => x.Contains("2024", StringComparison.Ordinal))) return "msfs2024";
            if (detected.Any(x => x.Contains("2020", StringComparison.Ordinal))) return "msfs2020";
            return "unknown";
        }

        return active switch
        {
            SimulatorKind.FlightSimulator2004 => "fs2004",
            SimulatorKind.FlightSimulatorX => "fsx",
            SimulatorKind.Prepar3D => "p3d",
            SimulatorKind.XPlane => "xplane",
            _ => "unknown"
        };
    }
}
