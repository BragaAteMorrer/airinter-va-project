using System.Text.Json;
using System.IO;
using System.Net.Http;

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
    DateTimeOffset? SentAt = null,
    DateTimeOffset? DeliveredAt = null,
    DateTimeOffset? ReadAt = null,
    DateTimeOffset? AcknowledgedAt = null,
    string? ClientMessageId = null,
    string? ReplyTo = null,
    bool LocalPending = false);

public sealed record DatalinkSnapshot(
    string OperationId,
    IReadOnlyList<DatalinkMessage> Messages,
    int PendingOutbound,
    int PendingReads,
    int PendingAcks,
    int UnreadCount,
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

internal sealed record PendingDatalinkRead(
    string OperationId,
    string MessageId,
    DateTimeOffset QueuedAt);

internal sealed record PendingDatalinkAck(
    string OperationId,
    string MessageId,
    DateTimeOffset QueuedAt);

internal sealed record DatalinkStoreState(
    List<DatalinkMessage>? Messages = null,
    List<PendingDatalinkSend>? Outbox = null,
    List<PendingDatalinkRead>? Reads = null,
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
/// Local-first Hermès datalink v2. Sends, READ receipts and ACKs are persisted
/// before network I/O. The server remains the canonical source for delivery
/// state while the local cache survives Prométhée/network interruptions.
/// </summary>
public sealed class HermesDatalink
{
    private readonly IDatalinkTransport transport;
    private readonly string folder;
    private readonly object gate = new();
    private readonly SemaphoreSlim networkGate = new(1, 1);
    private List<DatalinkMessage> messages = [];
    private List<PendingDatalinkSend> outbox = [];
    private List<PendingDatalinkRead> pendingReads = [];
    private List<PendingDatalinkAck> pendingAcks = [];

    public DateTimeOffset? LastSuccessfulSyncAt { get; private set; }
    public string? LastError { get; private set; }

    public HermesDatalink(PhpVmsClient client, string? storageFolder = null)
        : this(new PhpVmsDatalinkTransport(client), storageFolder) {}

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
        try
        {
            // Another Hermès instance may have replayed and persisted queued
            // actions while this instance was still alive. Refresh the
            // local-first state before deciding what still needs network I/O,
            // otherwise stale in-memory READ/ACK/outbox entries can be sent twice.
            lock (gate) Load();

            if (!transport.Connected)
            {
                LastError = "Prométhée hors ligne — messages et reçus conservés localement.";
                return Local(operationId);
            }

            try
            {
                await FlushOutbox(operationId);
                await FlushReads(operationId);
                await FlushAcks(operationId);

                var payload = await transport.Send($"v1/operations/{Uri.EscapeDataString(operationId)}/datalink");
                MergeServerPayload(payload);
                LastSuccessfulSyncAt = DateTimeOffset.UtcNow;
                LastError = null;
                Save();
                lock (gate) return Snapshot(operationId, "SYNCED");
            }
            catch (Exception exception) when (exception is InvalidOperationException or HttpRequestException or TaskCanceledException)
            {
                LastError = exception.Message;
                Save();
                lock (gate) return Snapshot(operationId, "DEGRADED");
            }
        }
        finally
        {
            networkGate.Release();
        }
    }

    public async Task<DatalinkSnapshot> SendAsync(
        string operationId,
        string body,
        string category = "CREW",
        string priority = "ROUTINE",
        bool requiresAck = false,
        string? replyTo = null)
    {
        ValidateOperation(operationId);
        body = body?.Trim() ?? "";
        if (body.Length is < 1 or > 2000)
            throw new InvalidOperationException("Le message datalink doit contenir entre 1 et 2000 caractères.");

        category = NormalizeChoice(category, ["OPS", "DISPATCH", "WEATHER", "SYSTEM", "CREW"], "CREW");
        priority = NormalizePriority(priority);

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
            SentAt: null,
            ClientMessageId: clientMessageId,
            ReplyTo: replyTo,
            LocalPending: true);

        lock (gate)
        {
            outbox.Add(pending);
            messages.RemoveAll(x => x.Id == local.Id);
            messages.Add(local);
            Trim();
            SaveUnsafe();
        }

        return await SyncAsync(operationId);
    }

    public async Task<DatalinkSnapshot> MarkReadAsync(string operationId, string messageId)
    {
        ValidateOperation(operationId);
        if (string.IsNullOrWhiteSpace(messageId))
            throw new InvalidOperationException("Message datalink invalide.");

        lock (gate)
        {
            var index = messages.FindIndex(x => x.Id == messageId && x.OperationId == operationId);
            if (index < 0) throw new InvalidOperationException("Message datalink introuvable.");
            var message = messages[index];
            if (message.Direction != "OPS_TO_COCKPIT")
                throw new InvalidOperationException("Seuls les messages reçus d’OPS peuvent être marqués comme lus.");
            if (message.ReadAt is not null || message.AcknowledgedAt is not null)
                return Snapshot(operationId, transport.Connected ? "LOCAL" : "OFFLINE");

            if (!pendingReads.Any(x => x.OperationId == operationId && x.MessageId == messageId))
                pendingReads.Add(new(operationId, messageId, DateTimeOffset.UtcNow));

            messages[index] = message with
            {
                Status = "READ_QUEUED",
                ReadAt = DateTimeOffset.UtcNow,
                LocalPending = true
            };
            SaveUnsafe();
        }

        return await SyncAsync(operationId);
    }

    public async Task<DatalinkSnapshot> AcknowledgeAsync(string operationId, string messageId)
    {
        ValidateOperation(operationId);
        if (string.IsNullOrWhiteSpace(messageId))
            throw new InvalidOperationException("Message datalink invalide.");

        lock (gate)
        {
            var index = messages.FindIndex(x => x.Id == messageId && x.OperationId == operationId);
            if (index < 0) throw new InvalidOperationException("Message datalink introuvable.");
            var message = messages[index];
            if (message.Direction != "OPS_TO_COCKPIT")
                throw new InvalidOperationException("Seuls les messages reçus d’OPS peuvent être acquittés.");
            if (!message.RequiresAck || message.AcknowledgedAt is not null)
                return Snapshot(operationId, transport.Connected ? "LOCAL" : "OFFLINE");

            pendingReads.RemoveAll(x => x.OperationId == operationId && x.MessageId == messageId);
            if (!pendingAcks.Any(x => x.OperationId == operationId && x.MessageId == messageId))
                pendingAcks.Add(new(operationId, messageId, DateTimeOffset.UtcNow));

            messages[index] = message with
            {
                Status = "ACK_QUEUED",
                ReadAt = message.ReadAt ?? DateTimeOffset.UtcNow,
                LocalPending = true
            };
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
                new
                {
                    body = item.Body,
                    category = item.Category,
                    priority = item.Priority,
                    requires_ack = item.RequiresAck,
                    client_message_id = item.ClientMessageId,
                    reply_to = item.ReplyTo
                });

            var canonical = ParseMessage(MessageElement(response));
            lock (gate)
            {
                outbox.RemoveAll(x => x.OperationId == item.OperationId && x.ClientMessageId == item.ClientMessageId);
                messages.RemoveAll(x => x.OperationId == item.OperationId
                    && (x.Id == "local-" + item.ClientMessageId || x.ClientMessageId == item.ClientMessageId));
                messages.Add(canonical);
                Trim();
                SaveUnsafe();
            }
        }
    }

    private async Task FlushReads(string operationId)
    {
        List<PendingDatalinkRead> pending;
        lock (gate) pending = pendingReads.Where(x => x.OperationId == operationId).OrderBy(x => x.QueuedAt).ToList();

        foreach (var item in pending)
        {
            var response = await transport.Send(
                $"v1/operations/{Uri.EscapeDataString(operationId)}/datalink/{Uri.EscapeDataString(item.MessageId)}/read",
                new { });

            var canonical = ParseMessage(MessageElement(response));
            lock (gate)
            {
                pendingReads.RemoveAll(x => x.OperationId == item.OperationId && x.MessageId == item.MessageId);
                messages.RemoveAll(x => x.Id == canonical.Id);
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
            lock (gate)
            {
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

        lock (gate)
        {
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
        var unread = operationMessages.Count(x =>
            x.Direction == "OPS_TO_COCKPIT" && x.ReadAt is null);
        var pendingRequired = operationMessages.Count(x =>
            x.Direction == "OPS_TO_COCKPIT" && x.RequiresAck && x.AcknowledgedAt is null);

        return new(
            operationId,
            operationMessages,
            outbox.Count(x => x.OperationId == operationId),
            pendingReads.Count(x => x.OperationId == operationId),
            pendingAcks.Count(x => x.OperationId == operationId),
            unread,
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

        var createdAt = date("created_at") ?? DateTimeOffset.UtcNow;
        return new(
            str("id"),
            str("operation_id"),
            str("direction"),
            str("category", "OPS"),
            NormalizePriority(str("priority", "ROUTINE")),
            str("body"),
            boolean("requires_ack"),
            str("status", "SENT"),
            str("sender_label"),
            createdAt,
            date("sent_at") ?? createdAt,
            date("delivered_at"),
            date("read_at"),
            date("acknowledged_at"),
            NullIfEmpty(str("client_message_id")),
            NullIfEmpty(str("reply_to")),
            false);
    }

    private void Load()
    {
        var path = StorePath();
        if (!File.Exists(path)) return;
        try
        {
            var state = JsonSerializer.Deserialize<DatalinkStoreState>(File.ReadAllText(path));
            messages = state?.Messages ?? [];
            outbox = state?.Outbox ?? [];
            pendingReads = state?.Reads ?? [];
            pendingAcks = state?.Acks ?? [];
            messages = messages.Select(x => x with { Priority = NormalizePriority(x.Priority) }).ToList();
            Trim();
        }
        catch (Exception exception) when (exception is JsonException or IOException or UnauthorizedAccessException)
        {
            messages = [];
            outbox = [];
            pendingReads = [];
            pendingAcks = [];
            LastError = "Le cache datalink local était illisible ou inaccessible et a été réinitialisé.";
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
        File.WriteAllText(temp, JsonSerializer.Serialize(
            new DatalinkStoreState(messages, outbox, pendingReads, pendingAcks)));
        File.Move(temp, path, true);
    }

    private void Trim()
    {
        if (messages.Count <= 500) return;

        var protectedIds = new HashSet<string>(
            pendingReads.Select(x => x.MessageId)
                .Concat(pendingAcks.Select(x => x.MessageId))
                .Concat(outbox.Select(x => "local-" + x.ClientMessageId))
                .Concat(messages
                    .Where(x => x.LocalPending || x.ReadAt is null || (x.RequiresAck && x.AcknowledgedAt is null))
                    .Select(x => x.Id)),
            StringComparer.Ordinal);

        var keep = new HashSet<string>(protectedIds, StringComparer.Ordinal);
        var room = Math.Max(0, 500 - keep.Count);
        foreach (var message in messages
                     .Where(x => !protectedIds.Contains(x.Id))
                     .OrderByDescending(x => x.CreatedAt)
                     .Take(room))
            keep.Add(message.Id);

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

    private static string NormalizePriority(string value)
    {
        value = (value ?? "ROUTINE").Trim().ToUpperInvariant();
        value = value switch
        {
            "NORMAL" => "ROUTINE",
            "HIGH" => "IMPORTANT",
            _ => value
        };
        return value is "ROUTINE" or "ADVISORY" or "IMPORTANT" or "URGENT" ? value : "ROUTINE";
    }

    private static string? NullIfEmpty(string value) => string.IsNullOrWhiteSpace(value) ? null : value;
}
