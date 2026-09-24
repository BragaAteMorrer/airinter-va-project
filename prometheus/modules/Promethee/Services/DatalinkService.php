<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * Hermès operational datalink store.
 *
 * Contract v2 keeps the Lot 5 local-first/idempotent transport while adding
 * real recipient lifecycle semantics: SENT -> DELIVERED -> READ -> ACKNOWLEDGED.
 * Legacy NORMAL/HIGH priorities remain accepted and are normalized server-side.
 */
class DatalinkService
{
    private const MAX_MESSAGES = 500;
    private const PRIORITY_ALIASES = [
        'NORMAL' => 'ROUTINE',
        'HIGH' => 'IMPORTANT',
    ];
    private const PRIORITIES = ['ROUTINE', 'ADVISORY', 'IMPORTANT', 'URGENT'];

    public function __construct(private readonly ?string $root = null) {}

    public function list(string $operationId, int $pilotId, ?string $recipientDirection = null): array
    {
        if ($recipientDirection !== null) {
            return $this->mutate($operationId, function (array &$messages) use ($operationId, $pilotId, $recipientDirection) {
                $now = now()->toIso8601String();
                foreach ($messages as &$message) {
                    $message = $this->normalizeStoredMessage($message);
                    if ((int) ($message['pilot_id'] ?? 0) !== $pilotId
                        || ($message['direction'] ?? null) !== $recipientDirection) {
                        continue;
                    }

                    if (blank($message['delivered_at'] ?? null)) {
                        $message['delivered_at'] = $now;
                        if (($message['status'] ?? 'SENT') === 'SENT') $message['status'] = 'DELIVERED';
                    }
                }
                unset($message);

                return $this->snapshot($operationId, $pilotId, $messages);
            });
        }

        $messages = array_map(
            fn (array $message) => $this->normalizeStoredMessage($message),
            $this->read($operationId)
        );
        return $this->snapshot($operationId, $pilotId, $messages);
    }

    public function send(
        string $operationId,
        int $pilotId,
        string $direction,
        string $category,
        string $priority,
        string $body,
        bool $requiresAck,
        string $senderLabel,
        ?string $clientMessageId = null,
        ?string $replyTo = null
    ): array {
        return $this->mutate($operationId, function (array &$messages) use (
            $operationId, $pilotId, $direction, $category, $priority, $body,
            $requiresAck, $senderLabel, $clientMessageId, $replyTo
        ) {
            if ($clientMessageId) {
                foreach ($messages as $existing) {
                    if (($existing['client_message_id'] ?? null) === $clientMessageId
                        && (int) ($existing['pilot_id'] ?? 0) === $pilotId
                        && ($existing['direction'] ?? null) === $direction) {
                        return $this->normalizeStoredMessage($existing);
                    }
                }
            }

            $createdAt = now()->toIso8601String();
            $message = [
                'id' => (string) Str::uuid(),
                'operation_id' => $operationId,
                'pilot_id' => $pilotId,
                'direction' => $direction,
                'category' => strtoupper(trim($category)),
                'priority' => $this->normalizePriority($priority),
                'body' => trim($body),
                'requires_ack' => $requiresAck,
                'status' => 'SENT',
                'sender_label' => trim($senderLabel),
                'client_message_id' => $clientMessageId,
                'reply_to' => $replyTo,
                'created_at' => $createdAt,
                'sent_at' => $createdAt,
                'delivered_at' => null,
                'read_at' => null,
                'acknowledged_at' => null,
            ];

            $messages[] = $message;
            $messages = $this->trimMessages($messages);
            return $message;
        });
    }

    public function markRead(
        string $operationId,
        int $pilotId,
        string $messageId,
        string $recipientDirection
    ): array {
        return $this->mutate($operationId, function (array &$messages) use (
            $pilotId, $messageId, $recipientDirection
        ) {
            foreach ($messages as &$message) {
                $message = $this->normalizeStoredMessage($message);
                if (($message['id'] ?? null) !== $messageId
                    || (int) ($message['pilot_id'] ?? 0) !== $pilotId) {
                    continue;
                }

                if (($message['direction'] ?? null) !== $recipientDirection) {
                    throw new RuntimeException('Ce message ne peut pas être marqué comme lu par ce destinataire.');
                }

                $now = now()->toIso8601String();
                $message['delivered_at'] ??= $now;
                $message['read_at'] ??= $now;
                if (blank($message['acknowledged_at'] ?? null)) $message['status'] = 'READ';
                return $message;
            }
            unset($message);

            throw new RuntimeException('Message datalink introuvable.');
        });
    }

    public function acknowledge(
        string $operationId,
        int $pilotId,
        string $messageId,
        string $recipientDirection
    ): array {
        return $this->mutate($operationId, function (array &$messages) use (
            $pilotId, $messageId, $recipientDirection
        ) {
            foreach ($messages as &$message) {
                $message = $this->normalizeStoredMessage($message);
                if (($message['id'] ?? null) !== $messageId
                    || (int) ($message['pilot_id'] ?? 0) !== $pilotId) {
                    continue;
                }

                if (($message['direction'] ?? null) !== $recipientDirection) {
                    throw new RuntimeException('Ce message ne peut pas être acquitté par ce destinataire.');
                }

                if (!($message['requires_ack'] ?? false)) return $message;

                $now = now()->toIso8601String();
                $message['delivered_at'] ??= $now;
                $message['read_at'] ??= $now;
                $message['acknowledged_at'] ??= $now;
                $message['status'] = 'ACKNOWLEDGED';
                return $message;
            }
            unset($message);

            throw new RuntimeException('Message datalink introuvable.');
        });
    }

    private function snapshot(string $operationId, int $pilotId, array $allMessages): array
    {
        $messages = array_values(array_filter(
            $allMessages,
            fn (array $message) => (int) ($message['pilot_id'] ?? 0) === $pilotId
        ));

        usort($messages, fn (array $a, array $b) => strcmp(
            (string) ($a['created_at'] ?? ''),
            (string) ($b['created_at'] ?? '')
        ));

        return [
            'contract_version' => '2.0',
            'operation_id' => $operationId,
            'messages' => $messages,
            'unread_count' => count(array_filter($messages, fn (array $message) =>
                ($message['direction'] ?? null) === 'OPS_TO_COCKPIT'
                && blank($message['read_at'] ?? null)
            )),
            'pending_ack_count' => count(array_filter($messages, fn (array $message) =>
                ($message['direction'] ?? null) === 'OPS_TO_COCKPIT'
                && ($message['requires_ack'] ?? false)
                && blank($message['acknowledged_at'] ?? null)
            )),
        ];
    }

    private function normalizeStoredMessage(array $message): array
    {
        $message['priority'] = $this->normalizePriority((string) ($message['priority'] ?? 'ROUTINE'));
        $message['sent_at'] ??= $message['created_at'] ?? null;
        $message['delivered_at'] ??= null;
        $message['read_at'] ??= null;
        $message['acknowledged_at'] ??= null;

        if (!blank($message['acknowledged_at'])) $message['status'] = 'ACKNOWLEDGED';
        elseif (!blank($message['read_at'])) $message['status'] = 'READ';
        elseif (!blank($message['delivered_at'])) $message['status'] = 'DELIVERED';
        else $message['status'] = 'SENT';

        return $message;
    }

    private function normalizePriority(string $priority): string
    {
        $priority = strtoupper(trim($priority));
        $priority = self::PRIORITY_ALIASES[$priority] ?? $priority;
        return in_array($priority, self::PRIORITIES, true) ? $priority : 'ROUTINE';
    }

    private function trimMessages(array $messages): array
    {
        if (count($messages) <= self::MAX_MESSAGES) return array_values($messages);

        $protected = [];
        foreach ($messages as $message) {
            if ((($message['requires_ack'] ?? false) && blank($message['acknowledged_at'] ?? null))
                || blank($message['read_at'] ?? null)) {
                $protected[(string) ($message['id'] ?? '')] = true;
            }
        }

        $keep = $protected;
        $room = max(0, self::MAX_MESSAGES - count($keep));
        for ($index = count($messages) - 1; $index >= 0 && $room > 0; $index--) {
            $id = (string) ($messages[$index]['id'] ?? '');
            if (isset($keep[$id])) continue;
            $keep[$id] = true;
            $room--;
        }

        return array_values(array_filter(
            $messages,
            fn (array $message) => isset($keep[(string) ($message['id'] ?? '')])
        ));
    }

    private function read(string $operationId): array
    {
        $path = $this->path($operationId);
        if (!is_file($path)) return [];

        $handle = fopen($path, 'rb');
        if (!$handle) throw new RuntimeException('Impossible de lire le stockage datalink.');
        try {
            if (!flock($handle, LOCK_SH)) throw new RuntimeException('Impossible de verrouiller le stockage datalink.');
            $raw = stream_get_contents($handle);
            $decoded = json_decode($raw ?: '[]', true);
            return is_array($decoded) ? $decoded : [];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function mutate(string $operationId, callable $callback): mixed
    {
        $path = $this->path($operationId);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException('Impossible de créer le stockage datalink.');
        }

        $handle = fopen($path, 'c+b');
        if (!$handle) throw new RuntimeException('Impossible d’ouvrir le stockage datalink.');

        try {
            if (!flock($handle, LOCK_EX)) throw new RuntimeException('Impossible de verrouiller le stockage datalink.');
            rewind($handle);
            $raw = stream_get_contents($handle);
            $decoded = json_decode($raw ?: '[]', true);
            $messages = is_array($decoded) ? $decoded : [];

            $result = $callback($messages);

            rewind($handle);
            if (!ftruncate($handle, 0)) throw new RuntimeException('Impossible de réécrire le stockage datalink.');
            $json = json_encode(array_values($messages), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (fwrite($handle, $json) === false) throw new RuntimeException('Impossible d’écrire le stockage datalink.');
            fflush($handle);

            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function path(string $operationId): string
    {
        $root = $this->root ?: storage_path('app/promethee/datalink');
        return rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.hash('sha256', $operationId).'.json';
    }
}
