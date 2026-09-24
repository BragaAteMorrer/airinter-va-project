using System.Text.Json;
using System.Text.Json.Serialization;

namespace Promethee;

public sealed record DatalinkMessage(
    string Id,
    string OperationId,
    string Direction,
    string Category,
    string Priority,
    string Body,
    bool RequiresAck,
    string Status,
    string SenderLabel,
    DateTimeOffset CreatedAt,
    DateTimeOffset? AcknowledgedAt = null,
    string? ClientMessageId = null,
    string? ReplyTo = null,
    bool LocalPending = false);

public sealed record DatalinkSnapshot(
    string OperationId,
    IReadOnlyList<DatalinkMessage> Messages,
    int PendingOutbound,
    int PendingAcks,
    int PendingRequiredAcks,
    string SyncState,
    DateTimeOffset? LastSuccessfulSyncAt,
    string? Error);

internal sealed record PendingDatalinkSend(
    string OperationId,
    string ClientMessageId,
    string Category,
    string Priority,
    string Body,
    bool RequiresAck,
    string? ReplyTo,
    DateTimeOffset CreatedAt);

internal sealed record PendingDatalinkAck(
    string OperationId,
    string MessageId,
    DateTimeOffset QueuedAt);

internal sealed record DatalinkStoreState(
    List<DatalinkMessage>? Messages = null,
    List<PendingDatalinkSend>? Outbox = null,
    List<PendingDatalinkAck>? Acks = null);

public interface IDatalinkTransport
{
    bool Connected { get; }
    Task<JsonElement> Send(string path, object? body = null);
}

public sealed class PhpVmsDatalinkTransport(PhpVmsClient client) : IDatalinkTransport
{
    public bool Connected => client.Connected;
    public Task<JsonElement> Send(string path, object? body = null) => client.Send(path, body);
}

/// <summary>
/// Local-first Hermès datalink. Outbound messages and acknowledgements are
/// persisted before network I/O, making retries idempotent through
/// client_message_id and server-side ACK idempotency.
/// </summary>
public sealed class HermesDatalink
{
    private readonly IDatalinkTransport transport;
    private readonly string folder;
    private readonly object gate = new();
    private readonly SemaphoreSlim networkGate = new(1, 1);
    private List<DatalinkMessage> messages = [];
    private List<PendingDatalinkSend> outbox = [];
    private List<PendingDatalinkAck> pendingAcks = [];

    public DateTimeOffset? LastSuccessfulSyncAt { get; private set; }
    public string? LastError { get; private set; }

    public HermesDatalink(PhpVmsClient client, string? storageFolder = null)
        : this(new PhpVmsDatalinkTransport(client), storageFolder)
    {
    }

    public HermesDatalink(IDatalinkTransport transport, string? storageFolder = null)
    {
        this.transport = transport;
        folder = storageFolder ?? Path.Combine(
            Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
            "AirInter", "Promethee");
        Directory.CreateDirectory(folder);
        Load();
    }

    public DatalinkSnapshot Local(string operationId)
    {
        lock (gate) return Snapshot(operationId, transport.Connected ? "LOCAL" : "OFFLINE");
    }

    public async Task<DatalinkSnapshot> SyncAsync(string operationId)
    {
        ValidateOperation(operationId);
        await networkGate.WaitAsync();
        try {
            if (!transport.Connected) {
                LastError = "Prométhée hors ligne — messages conservés localement.";
                return Local(operationId);
            }

            try {
                await FlushOutbox(operationId);
                await FlushAcks(operationId);

                var payload = await transport.Send($"v1/operations/{Uri.EscapeDataString(operationId)}/datalink");
                MergeServerPayload(payload);
                LastSuccessfulSyncAt = DateTimeOffset.UtcNow;
                LastError = null;
                Save();
                lock (gate) return Snapshot(operationId, "SYNCED");
            } catch (Exception exception) when (exception is InvalidOperationException or HttpRequestException or TaskCanceledException)
            {
                LastError = exception.Message;
                Save();
                lock (gate) return Snapshot(operationId, "DEGRADED");
            }
        } finally {
            networkGate.Release();
        }
    }

    public async Task<DatalinkSnapshot> SendAsync(
        string operationId,
        string body,
        string category = "CREW",
        string priority = "NORMAL",
        bool requiresAck = false,
        string? replyTo = null)
    {
        ValidateOperation(operationId);
        body = body?.Trim() ?? "";
        if (body.Length is < 1 or > 2000)
            throw new InvalidOperationException("Le message datalink doit contenir entre 1 et 2000 caractères.");

        category = NormalizeChoice(category, ["OPS", "DISPATCH", "WEATHER", "SYSTEM", "CREW"], "CREW");
        priority = NormalizeChoice(priority, ["NORMAL", "HIGH", "URGENT"], "NORMAL");

        var clientMessageId = Guid.NewGuid().ToString();
        var queuedAt = DateTimeOffset.UtcNow;
        var pending = new PendingDatalinkSend(
            operationId, clientMessageId, category, priority, body, requiresAck, replyTo, queuedAt);
        var local = new DatalinkMessage(
            "local-" + clientMessageId,
            operationId,
            "COCKPIT_TO_OPS",
            category,
            priority,
            body,
            requiresAck,
            "QUEUED",
            "COCKPIT",
            queuedAt,
            null,
            clientMessageId,
            replyTo,
            true);

        lock (gate) {
            outbox.Add(pending);
            messages.RemoveAll(x => x.Id == local.Id);
            messages.Add(local);
            Trim();
            SaveUnsafe();
        }

        return await SyncAsync(operationId);
    }

    public async Task<DatalinkSnapshot> AcknowledgeAsync(string operationId, string messageId)
    {
        ValidateOperation(operationId);
        if (string.IsNullOrWhiteSpace(messageId))
            throw new InvalidOperationException("Message datalink invalide.");

        lock (gate) {
            var index = messages.FindIndex(x => x.Id == messageId && x.OperationId == operationId);
            if (index < 0) throw new InvalidOperationException("Message datalink introuvable.");
            var message = messages[index];
            if (message.Direction != "OPS_TO_COCKPIT")
                throw new InvalidOperationException("Seuls les messages reçus d’OPS peuvent être acquittés.");
            if (!message.RequiresAck || message.AcknowledgedAt is not null)
                return Snapshot(operationId, transport.Connected ? "LOCAL" : "OFFLINE");

            if (!pendingAcks.Any(x => x.OperationId == operationId && x.MessageId == messageId))
                pendingAcks.Add(new(operationId, messageId, DateTimeOffset.UtcNow));
            messages[index] = message with { Status = "ACK_QUEUED", LocalPending = true };
            SaveUnsafe();
        }

        return await SyncAsync(operationId);
    }

    private async Task FlushOutbox(string operationId)
    {
        List<PendingDatalinkSend> pending;
        lock (gate) pending = outbox.Where(x => x.OperationId == operationId).OrderBy(x => x.CreatedAt).ToList();

        foreach (var item in pending)
        {
            var response = await transport.Send(
                $"v1/operations/{Uri.EscapeDataString(operationId)}/datalink",
                new {
                    body = item.Body,
                    category = item.Category,
                    priority = item.Priority,
                    requires_ack = item.RequiresAck,
                    client_message_id = item.ClientMessageId,
                    reply_to = item.ReplyTo
                });

            var canonical = ParseMessage(MessageElement(response));
            lock (gate) {
                outbox.RemoveAll(x => x.OperationId == item.OperationId && x.ClientMessageId == item.ClientMessageId);
                messages.RemoveAll(x => x.OperationId == item.OperationId
                    && (x.Id == "local-" + item.ClientMessageId || x.ClientMessageId == item.ClientMessageId));
                messages.Add(canonical);
                Trim();
                SaveUnsafe();
            }
        }
    }

    private async Task FlushAcks(string operationId)
    {
        List<PendingDatalinkAck> pending;
        lock (gate) pending = pendingAcks.Where(x => x.OperationId == operationId).OrderBy(x => x.QueuedAt).ToList();

        foreach (var item in pending)
        {
            var response = await transport.Send(
                $"v1/operations/{Uri.EscapeDataString(operationId)}/datalink/{Uri.EscapeDataString(item.MessageId)}/ack",
                new { });

            var canonical = ParseMessage(MessageElement(response));
            lock (gate) {
                pendingAcks.RemoveAll(x => x.OperationId == item.OperationId && x.MessageId == item.MessageId);
                messages.RemoveAll(x => x.Id == canonical.Id);
                messages.Add(canonical);
                Trim();
                SaveUnsafe();
            }
        }
    }

    private void MergeServerPayload(JsonElement payload)
    {
        if (payload.ValueKind != JsonValueKind.Object
            || !payload.TryGetProperty("messages", out var array)
            || array.ValueKind != JsonValueKind.Array) return;

        lock (gate) {
            foreach (var element in array.EnumerateArray())
            {
                var incoming = ParseMessage(element);
                if (!string.IsNullOrWhiteSpace(incoming.ClientMessageId))
                    messages.RemoveAll(x => x.ClientMessageId == incoming.ClientMessageId);
                messages.RemoveAll(x => x.Id == incoming.Id);
                messages.Add(incoming);
            }
            Trim();
        }
    }

    private DatalinkSnapshot Snapshot(string operationId, string state)
    {
        var operationMessages = messages
            .Where(x => x.OperationId == operationId)
            .OrderBy(x => x.CreatedAt)
            .ToArray();
        var pendingRequired = operationMessages.Count(x =>
            x.Direction == "OPS_TO_COCKPIT" && x.RequiresAck && x.AcknowledgedAt is null);

        return new(
            operationId,
            operationMessages,
            outbox.Count(x => x.OperationId == operationId),
            pendingAcks.Count(x => x.OperationId == operationId),
            pendingRequired,
            state,
            LastSuccessfulSyncAt,
            LastError);
    }

    private static JsonElement MessageElement(JsonElement response)
    {
        if (response.ValueKind == JsonValueKind.Object
            && response.TryGetProperty("message", out var message))
            return message;
        return response;
    }

    private static DatalinkMessage ParseMessage(JsonElement element)
    {
        string str(string name, string fallback = "") =>
            element.TryGetProperty(name, out var value) && value.ValueKind == JsonValueKind.String
                ? value.GetString() ?? fallback
                : fallback;
        bool boolean(string name) =>
            element.TryGetProperty(name, out var value)
            && value.ValueKind is JsonValueKind.True or JsonValueKind.False
            && value.GetBoolean();
        DateTimeOffset? date(string name)
        {
            var value = str(name);
            return DateTimeOffset.TryParse(value, out var parsed) ? parsed : null;
        }

        return new(
            str("id"),
            str("operation_id"),
            str("direction"),
            str("category", "OPS"),
            str("priority", "NORMAL"),
            str("body"),
            boolean("requires_ack"),
            str("status", "SENT"),
            str("sender_label"),
            date("created_at") ?? DateTimeOffset.UtcNow,
            date("acknowledged_at"),
            NullIfEmpty(str("client_message_id")),
            NullIfEmpty(str("reply_to")),
            false);
    }

    private void Load()
    {
        var path = StorePath();
        if (!File.Exists(path)) return;
        try {
            var state = JsonSerializer.Deserialize<DatalinkStoreState>(File.ReadAllText(path));
            messages = state?.Messages ?? [];
            outbox = state?.Outbox ?? [];
            pendingAcks = state?.Acks ?? [];
            Trim();
        } catch (JsonException) {
            messages = [];
            outbox = [];
            pendingAcks = [];
            LastError = "Le cache datalink local était illisible et a été réinitialisé.";
        }
    }

    private void Save()
    {
        lock (gate) SaveUnsafe();
    }

    private void SaveUnsafe()
    {
        var path = StorePath();
        var temp = path + ".tmp";
        File.WriteAllText(temp, JsonSerializer.Serialize(new DatalinkStoreState(messages, outbox, pendingAcks)));
        File.Move(temp, path, true);
    }

    private void Trim()
    {
        if (messages.Count <= 500) return;
        var keep = messages.OrderByDescending(x => x.CreatedAt).Take(500).Select(x => x.Id).ToHashSet();
        messages.RemoveAll(x => !keep.Contains(x.Id));
    }

    private string StorePath() => Path.Combine(folder, "datalink.json");

    private static void ValidateOperation(string operationId)
    {
        if (string.IsNullOrWhiteSpace(operationId) || operationId.Length > 128)
            throw new InvalidOperationException("Opération datalink invalide.");
    }

    private static string NormalizeChoice(string value, IReadOnlyCollection<string> allowed, string fallback)
    {
        value = (value ?? fallback).Trim().ToUpperInvariant();
        return allowed.Contains(value) ? value : fallback;
    }

    private static string? NullIfEmpty(string value) => string.IsNullOrWhiteSpace(value) ? null : value;
}
