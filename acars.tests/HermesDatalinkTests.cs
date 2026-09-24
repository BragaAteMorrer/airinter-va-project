using System.Text.Json;
using Promethee;
using Xunit;

namespace Promethee.Acars.Tests;

public sealed class HermesDatalinkTests
{
    [Fact]
    public async Task Offline_send_is_persisted_and_replayed_once_after_reconnect()
    {
        var folder = TempFolder();
        var transport = new FakeTransport { Connected = false };
        var datalink = new HermesDatalink(transport, folder);

        var queued = await datalink.SendAsync("op_123", "Request weather", "WEATHER", "HIGH", true);

        Assert.Equal(1, queued.PendingOutbound);
        var local = Assert.Single(queued.Messages);
        Assert.True(local.LocalPending);
        Assert.Equal("QUEUED", local.Status);

        var recovered = new HermesDatalink(transport, folder).Local("op_123");
        Assert.Equal(1, recovered.PendingOutbound);
        Assert.Single(recovered.Messages);

        transport.Connected = true;
        var synced = await new HermesDatalink(transport, folder).SyncAsync("op_123");

        Assert.Equal(0, synced.PendingOutbound);
        var canonical = Assert.Single(synced.Messages);
        Assert.False(canonical.LocalPending);
        Assert.Equal("SENT", canonical.Status);
        Assert.Equal("COCKPIT_TO_OPS", canonical.Direction);
        Assert.Equal(1, transport.MessagePostCount);

        await new HermesDatalink(transport, folder).SyncAsync("op_123");
        Assert.Equal(1, transport.MessagePostCount);
    }

    [Fact]
    public async Task Ack_is_queued_offline_and_is_idempotent_after_reconnect()
    {
        var folder = TempFolder();
        var transport = new FakeTransport { Connected = true };
        transport.SeedIncoming("op_456", "msg-ops-1", "Return to stand.", requiresAck: true);
        var datalink = new HermesDatalink(transport, folder);

        var initial = await datalink.SyncAsync("op_456");
        Assert.Equal(1, initial.PendingRequiredAcks);

        transport.Connected = false;
        var queuedAck = await datalink.AcknowledgeAsync("op_456", "msg-ops-1");
        Assert.Equal(1, queuedAck.PendingAcks);
        Assert.Equal("ACK_QUEUED", Assert.Single(queuedAck.Messages).Status);

        transport.Connected = true;
        var synced = await datalink.SyncAsync("op_456");
        Assert.Equal(0, synced.PendingAcks);
        Assert.Equal(0, synced.PendingRequiredAcks);
        Assert.NotNull(Assert.Single(synced.Messages).AcknowledgedAt);
        Assert.Equal(1, transport.AckPostCount);

        await datalink.SyncAsync("op_456");
        Assert.Equal(1, transport.AckPostCount);
    }

    [Fact]
    public async Task Reply_to_is_preserved_through_local_queue_and_server_canonicalization()
    {
        var folder = TempFolder();
        var transport = new FakeTransport { Connected = false };
        var datalink = new HermesDatalink(transport, folder);

        var local = await datalink.SendAsync("op_789", "Roger.", "CREW", "NORMAL", false, "msg-parent");

        Assert.Equal("msg-parent", Assert.Single(local.Messages).ReplyTo);

        transport.Connected = true;
        var synced = await datalink.SyncAsync("op_789");
        Assert.Equal("msg-parent", Assert.Single(synced.Messages).ReplyTo);
    }

    private static string TempFolder()
    {
        var folder = Path.Combine(Path.GetTempPath(), "AirInter-Hermes-Datalink-Tests", Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(folder);
        return folder;
    }

    private sealed class FakeTransport : IDatalinkTransport
    {
        private readonly List<Dictionary<string, object?>> serverMessages = [];
        public bool Connected { get; set; }
        public int MessagePostCount { get; private set; }
        public int AckPostCount { get; private set; }

        public void SeedIncoming(string operationId, string id, string body, bool requiresAck)
        {
            serverMessages.Add(new Dictionary<string, object?> {
                ["id"] = id,
                ["operation_id"] = operationId,
                ["pilot_id"] = 1,
                ["direction"] = "OPS_TO_COCKPIT",
                ["category"] = "OPS",
                ["priority"] = "HIGH",
                ["body"] = body,
                ["requires_ack"] = requiresAck,
                ["status"] = "SENT",
                ["sender_label"] = "AIR INTER OPS",
                ["client_message_id"] = null,
                ["reply_to"] = null,
                ["created_at"] = DateTimeOffset.Parse("2026-09-24T20:00:00Z").ToString("O"),
                ["acknowledged_at"] = null,
            });
        }

        public Task<JsonElement> Send(string path, object? body = null)
        {
            if (!Connected) throw new InvalidOperationException("offline");

            if (body is null && path.EndsWith("/datalink", StringComparison.Ordinal))
                return Task.FromResult(JsonSerializer.SerializeToElement(new { messages = serverMessages }));

            if (body is not null && path.EndsWith("/datalink", StringComparison.Ordinal))
            {
                MessagePostCount++;
                var payload = JsonSerializer.SerializeToElement(body);
                var operationId = path.Split('/')[2];
                var clientId = payload.GetProperty("client_message_id").GetString();
                var existing = serverMessages.FirstOrDefault(x => Equals(x["client_message_id"], clientId));
                if (existing is null) {
                    existing = new Dictionary<string, object?> {
                        ["id"] = "srv-" + clientId,
                        ["operation_id"] = operationId,
                        ["pilot_id"] = 1,
                        ["direction"] = "COCKPIT_TO_OPS",
                        ["category"] = payload.GetProperty("category").GetString(),
                        ["priority"] = payload.GetProperty("priority").GetString(),
                        ["body"] = payload.GetProperty("body").GetString(),
                        ["requires_ack"] = payload.GetProperty("requires_ack").GetBoolean(),
                        ["status"] = "SENT",
                        ["sender_label"] = "IT199",
                        ["client_message_id"] = clientId,
                        ["reply_to"] = payload.TryGetProperty("reply_to", out var reply) && reply.ValueKind == JsonValueKind.String ? reply.GetString() : null,
                        ["created_at"] = DateTimeOffset.Parse("2026-09-24T20:05:00Z").ToString("O"),
                        ["acknowledged_at"] = null,
                    };
                    serverMessages.Add(existing);
                }
                return Task.FromResult(JsonSerializer.SerializeToElement(new { message = existing }));
            }

            if (body is not null && path.EndsWith("/ack", StringComparison.Ordinal))
            {
                AckPostCount++;
                var messageId = path.Split('/')[4];
                var message = serverMessages.Single(x => Equals(x["id"], messageId));
                message["acknowledged_at"] ??= DateTimeOffset.Parse("2026-09-24T20:06:00Z").ToString("O");
                message["status"] = "ACKNOWLEDGED";
                return Task.FromResult(JsonSerializer.SerializeToElement(new { message }));
            }

            throw new InvalidOperationException("Unexpected fake datalink route: " + path);
        }
    }
}
