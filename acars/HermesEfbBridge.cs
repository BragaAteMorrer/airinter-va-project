using System.Text.Json;

namespace Promethee;

/// <summary>
/// Minimal transport needed by the MSFS 2024 EFB bridge. Keeping this seam
/// separate from SimConnect makes the protocol unit-testable without MSFS.
/// </summary>
public interface IHermesEfbTransport
{
    bool CommBusAvailable { get; }
    DateTimeOffset? LastCommBusRequestAt { get; }
    event Action<string>? CommBusMessageReceived;
    bool TrySendCommBus(string eventName, string payload);
}

public sealed record HermesEfbContext(
    string? OperationId,
    string? PirepId,
    string? FlightIdent,
    string? Departure,
    string? Arrival,
    string? Alternate,
    string? Route,
    string? AircraftRegistration,
    string? AircraftIcao,
    string? AircraftModel,
    int? Passengers,
    int? FlightLevel,
    string? CostIndex,
    double? BlockFuel,
    int? EstimatedTimeEnroute,
    string? OfpSource,
    string? DispatchStatus,
    DateTimeOffset UpdatedAt);

/// <summary>
/// Read-only operational bridge between the native MSFS 2024 EFB app and
/// Hermès. Authentication tokens and write-capable PIREP actions never cross
/// this boundary.
/// </summary>
public sealed class HermesEfbBridge : IDisposable
{
    public const int ProtocolVersion = 1;
    public const string RequestEvent = "AIRINTER_HERMES_EFB_REQUEST";
    public const string StateEvent = "AIRINTER_HERMES_EFB_STATE";

    private readonly IHermesEfbTransport transport;
    private readonly Func<HermesEfbContext?, object> stateFactory;
    private readonly object gate = new();
    private HermesEfbContext? context;

    public int RequestsHandled { get; private set; }
    public DateTimeOffset? LastResponseAt { get; private set; }

    public HermesEfbBridge(IHermesEfbTransport transport, Func<HermesEfbContext?, object> stateFactory)
    {
        this.transport = transport;
        this.stateFactory = stateFactory;
        transport.CommBusMessageReceived += Receive;
    }

    public object UpdateContext(JsonElement? body)
    {
        lock (gate)
        {
            if (!body.HasValue || body.Value.ValueKind is JsonValueKind.Null or JsonValueKind.Undefined)
            {
                context = null;
                return Diagnostics();
            }

            if (body.Value.ValueKind != JsonValueKind.Object)
                throw new InvalidOperationException("Contexte EFB invalide.");

            var value = body.Value;
            context = new(
                Text(value, "operation_id", 128),
                Text(value, "pirep_id", 128),
                Text(value, "flight_ident", 32),
                Upper(value, "departure", 8),
                Upper(value, "arrival", 8),
                Upper(value, "alternate", 8),
                Text(value, "route", 2048),
                Upper(value, "aircraft_registration", 24),
                Upper(value, "aircraft_icao", 12),
                Text(value, "aircraft_model", 96),
                Integer(value, "passengers", 0, 1000),
                Integer(value, "flight_level", 0, 700),
                Upper(value, "cost_index", 16),
                Number(value, "block_fuel", 0, 1_000_000),
                Integer(value, "estimated_time_enroute", 0, 24 * 60 * 60),
                Text(value, "ofp_source", 48),
                Upper(value, "dispatch_status", 48),
                DateTimeOffset.UtcNow);

            return Diagnostics();
        }
    }

    public object Diagnostics()
    {
        lock (gate)
        {
            return new
            {
                protocolVersion = ProtocolVersion,
                commBusAvailable = transport.CommBusAvailable,
                lastRequestAt = transport.LastCommBusRequestAt,
                lastResponseAt = LastResponseAt,
                requestsHandled = RequestsHandled,
                context
            };
        }
    }

    private void Receive(string payload)
    {
        string? requestId = null;
        string action = "state";

        try
        {
            using var document = JsonDocument.Parse(payload);
            var root = document.RootElement;
            if (root.ValueKind == JsonValueKind.Object)
            {
                if (root.TryGetProperty("version", out var version)
                    && version.TryGetInt32(out var requestedVersion)
                    && requestedVersion != ProtocolVersion)
                {
                    Send(new
                    {
                        version = ProtocolVersion,
                        ok = false,
                        error = "UNSUPPORTED_PROTOCOL",
                        requestedVersion
                    });
                    return;
                }

                requestId = Text(root, "requestId", 96);
                action = Text(root, "action", 32)?.ToLowerInvariant() ?? "state";
            }
        }
        catch (JsonException)
        {
            Send(new
            {
                version = ProtocolVersion,
                ok = false,
                error = "INVALID_REQUEST"
            });
            return;
        }

        if (action is not ("state" or "ping"))
        {
            Send(new
            {
                version = ProtocolVersion,
                requestId,
                ok = false,
                error = "READ_ONLY_BRIDGE"
            });
            return;
        }

        HermesEfbContext? current;
        lock (gate) current = context;

        object state;
        try
        {
            state = stateFactory(current);
        }
        catch (Exception exception)
        {
            Send(new
            {
                version = ProtocolVersion,
                requestId,
                ok = false,
                error = "STATE_UNAVAILABLE",
                message = exception.Message
            });
            return;
        }

        Send(new
        {
            version = ProtocolVersion,
            requestId,
            ok = true,
            generatedAt = DateTimeOffset.UtcNow,
            context = current,
            state
        });
    }

    private void Send(object envelope)
    {
        var json = JsonSerializer.Serialize(envelope);
        if (!transport.TrySendCommBus(StateEvent, json)) return;

        lock (gate)
        {
            RequestsHandled++;
            LastResponseAt = DateTimeOffset.UtcNow;
        }
    }

    public void Dispose() => transport.CommBusMessageReceived -= Receive;

    private static string? Text(JsonElement value, string name, int max)
    {
        if (!value.TryGetProperty(name, out var property)
            || property.ValueKind is JsonValueKind.Null or JsonValueKind.Undefined)
            return null;
        if (property.ValueKind != JsonValueKind.String)
            throw new InvalidOperationException($"Champ EFB {name} invalide.");

        var text = property.GetString()?.Trim();
        if (string.IsNullOrWhiteSpace(text)) return null;
        if (text.Length > max) throw new InvalidOperationException($"Champ EFB {name} trop long.");
        return text;
    }

    private static string? Upper(JsonElement value, string name, int max) =>
        Text(value, name, max)?.ToUpperInvariant();

    private static int? Integer(JsonElement value, string name, int min, int max)
    {
        if (!value.TryGetProperty(name, out var property)
            || property.ValueKind is JsonValueKind.Null or JsonValueKind.Undefined)
            return null;
        if (!property.TryGetInt32(out var number) || number < min || number > max)
            throw new InvalidOperationException($"Champ EFB {name} invalide.");
        return number;
    }

    private static double? Number(JsonElement value, string name, double min, double max)
    {
        if (!value.TryGetProperty(name, out var property)
            || property.ValueKind is JsonValueKind.Null or JsonValueKind.Undefined)
            return null;
        if (!property.TryGetDouble(out var number)
            || !double.IsFinite(number)
            || number < min
            || number > max)
            throw new InvalidOperationException($"Champ EFB {name} invalide.");
        return number;
    }
}
