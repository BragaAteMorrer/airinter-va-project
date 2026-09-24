using System.Text.Json;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class HermesPresenceTests
{
    [Fact]
    public async Task Heartbeat_is_rate_limited_per_operation()
    {
        var now = DateTimeOffset.Parse("2026-09-25T20:00:00Z");
        var transport = new FakePresenceTransport { Connected = true };
        var presence = new HermesPresence(transport, () => now);
        var heartbeat = H();

        await presence.HeartbeatIfDueAsync("op_123", heartbeat);
        await presence.HeartbeatIfDueAsync("op_123", heartbeat);

        Assert.Equal(1, transport.HeartbeatCount);

        now = now.AddSeconds(15);
        await presence.HeartbeatIfDueAsync("op_123", heartbeat);
        Assert.Equal(2, transport.HeartbeatCount);
    }

    [Fact]
    public async Task Switching_operation_sends_immediately()
    {
        var now = DateTimeOffset.Parse("2026-09-25T20:00:00Z");
        var transport = new FakePresenceTransport { Connected = true };
        var presence = new HermesPresence(transport, () => now);

        await presence.HeartbeatIfDueAsync("op_123", H());
        await presence.HeartbeatIfDueAsync("op_456", H());

        Assert.Equal(2, transport.HeartbeatCount);
    }

    [Fact]
    public async Task Offline_presence_is_not_queued_or_replayed()
    {
        var now = DateTimeOffset.Parse("2026-09-25T20:00:00Z");
        var transport = new FakePresenceTransport { Connected = false };
        var presence = new HermesPresence(transport, () => now);

        await presence.HeartbeatIfDueAsync("op_123", H());
        Assert.Equal(0, transport.HeartbeatCount);

        transport.Connected = true;
        await presence.HeartbeatIfDueAsync("op_123", H());
        Assert.Equal(1, transport.HeartbeatCount);
    }

    [Fact]
    public async Task Heartbeat_response_updates_cached_network()
    {
        var transport = new FakePresenceTransport { Connected = true };
        var presence = new HermesPresence(transport);

        await presence.HeartbeatNowAsync("op_123", H());

        Assert.NotNull(presence.LastHeartbeatAt);
        Assert.True(presence.LastNetwork.HasValue);
        Assert.Equal(1, presence.LastNetwork!.Value.GetProperty("online_count").GetInt32());
    }

    private static PresenceHeartbeat H() => new(
        "msfs2024", "simconnect", "CRUISE", 47.2, 6.1, 37000, "1.9.0", true, "TRACKING");

    private sealed class FakePresenceTransport : IPresenceTransport
    {
        public bool Connected { get; set; }
        public int HeartbeatCount { get; private set; }

        public Task<JsonElement> Send(string path, object? body = null)
        {
            if (!Connected) throw new InvalidOperationException("offline");

            if (path.EndsWith("/presence/heartbeat", StringComparison.Ordinal))
            {
                HeartbeatCount++;
                return Task.FromResult(JsonSerializer.SerializeToElement(new {
                    presence = new { state = "ONLINE" },
                    network = new {
                        contract_version = "1.0",
                        online_count = 1,
                        crews = new[] { new { operation_id = "op_123" } }
                    }
                }));
            }

            if (path == "v1/network/presence")
                return Task.FromResult(JsonSerializer.SerializeToElement(new {
                    contract_version = "1.0",
                    online_count = 1,
                    crews = new[] { new { operation_id = "op_123" } }
                }));

            throw new InvalidOperationException("Unexpected presence route: " + path);
        }
    }
}
