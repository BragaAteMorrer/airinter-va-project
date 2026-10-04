using System.Text.Json;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class HermesEfbBridgeTests
{
    [Fact]
    public void State_request_returns_read_only_camel_case_operational_snapshot()
    {
        var transport = new FakeTransport();
        using var bridge = new HermesEfbBridge(transport, context => new
        {
            hermesConnected = true,
            flight = new { phase = "CRUISE" },
            contextOperation = context?.OperationId
        });

        using var contextDocument = JsonDocument.Parse("""
        {
          "operation_id": "op_test_123",
          "pirep_id": "pirep_test_123",
          "flight_ident": "IT123",
          "departure": "lfpo",
          "arrival": "lfqq",
          "aircraft_registration": "f-ggge",
          "aircraft_icao": "a320",
          "passengers": 128,
          "flight_level": 330,
          "cost_index": "25",
          "block_fuel": 12400,
          "ofp_source": "simbrief",
          "dispatch_status": "ready",
          "weather": {
            "status": "AVAILABLE",
            "summary": {"arrival_runway": "25", "sigmet_count": 1},
            "stations": {"arrival": {"icao": "LFQQ", "metar": {"category": "VFR"}}}
          }
        }
        """);

        bridge.UpdateContext(contextDocument.RootElement.Clone());
        transport.Receive("""{"version":1,"action":"state","requestId":"req-1"}""");

        var sent = Assert.Single(transport.Sent);
        Assert.Equal(HermesEfbBridge.StateEvent, sent.EventName);

        using var response = JsonDocument.Parse(sent.Payload);
        var root = response.RootElement;
        Assert.True(root.GetProperty("ok").GetBoolean());
        Assert.Equal("req-1", root.GetProperty("requestId").GetString());
        Assert.Equal("op_test_123", root.GetProperty("context").GetProperty("operationId").GetString());
        Assert.Equal("LFPO", root.GetProperty("context").GetProperty("departure").GetString());
        Assert.Equal("F-GGGE", root.GetProperty("context").GetProperty("aircraftRegistration").GetString());
        Assert.Equal("25", root.GetProperty("context").GetProperty("weather").GetProperty("summary").GetProperty("arrival_runway").GetString());
        Assert.Equal(1, root.GetProperty("context").GetProperty("weather").GetProperty("summary").GetProperty("sigmet_count").GetInt32());
        Assert.Equal("CRUISE", root.GetProperty("state").GetProperty("flight").GetProperty("phase").GetString());
        Assert.False(root.TryGetProperty("OperationId", out _));
        Assert.Equal(1, bridge.RequestsHandled);
    }

    [Fact]
    public void Write_action_is_rejected_by_the_bridge()
    {
        var transport = new FakeTransport();
        using var bridge = new HermesEfbBridge(transport, _ => new { ok = true });

        transport.Receive("""{"version":1,"action":"file-pirep","requestId":"dangerous"}""");

        using var response = JsonDocument.Parse(Assert.Single(transport.Sent).Payload);
        var root = response.RootElement;
        Assert.False(root.GetProperty("ok").GetBoolean());
        Assert.Equal("READ_ONLY_BRIDGE", root.GetProperty("error").GetString());
        Assert.Equal("dangerous", root.GetProperty("requestId").GetString());
    }

    [Fact]
    public void Unsupported_protocol_version_is_rejected_without_calling_state_factory()
    {
        var transport = new FakeTransport();
        var calls = 0;
        using var bridge = new HermesEfbBridge(transport, _ =>
        {
            calls++;
            return new { ok = true };
        });

        transport.Receive("""{"version":99,"action":"state"}""");

        using var response = JsonDocument.Parse(Assert.Single(transport.Sent).Payload);
        Assert.Equal("UNSUPPORTED_PROTOCOL", response.RootElement.GetProperty("error").GetString());
        Assert.Equal(0, calls);
    }

    [Fact]
    public void Weather_context_rejects_non_object_payloads()
    {
        var transport = new FakeTransport();
        using var bridge = new HermesEfbBridge(transport, _ => new { ok = true });
        using var contextDocument = JsonDocument.Parse("""{"operation_id":"op_weather","weather":"invalid"}""");

        Assert.Throws<InvalidOperationException>(() => bridge.UpdateContext(contextDocument.RootElement.Clone()));
    }

    [Fact]
    public void Clearing_context_removes_operation_data()
    {
        var transport = new FakeTransport();
        using var bridge = new HermesEfbBridge(transport, context => new
        {
            hasContext = context is not null
        });

        using var contextDocument = JsonDocument.Parse("""{"operation_id":"op_clear_me"}""");
        bridge.UpdateContext(contextDocument.RootElement.Clone());
        bridge.UpdateContext(null);

        transport.Receive("""{"version":1,"action":"state"}""");

        using var response = JsonDocument.Parse(Assert.Single(transport.Sent).Payload);
        Assert.Equal(JsonValueKind.Null, response.RootElement.GetProperty("context").ValueKind);
        Assert.False(response.RootElement.GetProperty("state").GetProperty("hasContext").GetBoolean());
    }

    private sealed class FakeTransport : IHermesEfbTransport
    {
        public bool CommBusAvailable { get; set; } = true;
        public DateTimeOffset? LastCommBusRequestAt { get; private set; }
        public event Action<string>? CommBusMessageReceived;
        public List<(string EventName, string Payload)> Sent { get; } = [];

        public bool TrySendCommBus(string eventName, string payload)
        {
            if (!CommBusAvailable) return false;
            Sent.Add((eventName, payload));
            return true;
        }

        public void Receive(string payload)
        {
            LastCommBusRequestAt = DateTimeOffset.UtcNow;
            CommBusMessageReceived?.Invoke(payload);
        }
    }
}
