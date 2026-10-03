<?php

namespace Modules\Promethee\Services;

use Carbon\Carbon;
use RuntimeException;

/**
 * Ephemeral Air Inter Network presence.
 *
 * Hermès emits a heartbeat; Prométhée remains authoritative for pilot,
 * scheduled flight and assigned aircraft identity. Presence is intentionally
 * operational and short-lived: no heartbeat means offline.
 */
class PresenceService
{
    public const ONLINE_TTL_SECONDS = 45;
    private const RETAIN_SECONDS = 21600;

    public function __construct(private readonly ?string $root = null) {}

    public function heartbeat(
        string $operationId,
        array $pilot,
        array $operation,
        array $client
    ): array {
        return $this->mutate(function (array &$entries) use ($operationId, $pilot, $operation, $client) {
            $now = now();
            $pilotId = (int) ($pilot['id'] ?? 0);
            if ($pilotId <= 0) throw new RuntimeException('Pilote de présence invalide.');

            $existing = collect($entries)->first(fn (array $entry) =>
                (int) ($entry['pilot']['id'] ?? 0) === $pilotId
                && ($entry['operation_id'] ?? null) === $operationId
            );

            $entries = array_values(array_filter($entries, function (array $entry) use ($pilotId, $now) {
                if ((int) ($entry['pilot']['id'] ?? 0) === $pilotId) return false;
                $lastSeen = $this->date($entry['last_seen_at'] ?? null);
                return $lastSeen !== null && $lastSeen->greaterThan($now->copy()->subSeconds(self::RETAIN_SECONDS));
            }));

            $connectedAt = $existing['connected_at'] ?? $now->toIso8601String();
            $record = [
                'presence_id' => 'presence_'.substr(hash('sha256', (string) $pilotId), 0, 24),
                'operation_id' => $operationId,
                'pilot' => [
                    'id' => $pilotId,
                    'ident' => trim((string) ($pilot['ident'] ?? '')),
                    'name' => trim((string) ($pilot['name'] ?? '')),
                ],
                'flight' => $operation['flight'] ?? null,
                'aircraft' => $operation['aircraft'] ?? null,
                'simulator' => $client['simulator'] ?? null,
                'connector' => $client['connector'] ?? null,
                'phase' => $client['phase'] ?? null,
                'position' => [
                    'lat' => $this->number($client['lat'] ?? null),
                    'lon' => $this->number($client['lon'] ?? null),
                    'altitude_msl' => $this->number($client['altitude_msl'] ?? null),
                ],
                'hermes_version' => $client['hermes_version'] ?? null,
                'recording' => (bool) ($client['recording'] ?? false),
                'client_state' => $client['client_state'] ?? null,
                'connected_at' => $connectedAt,
                'last_seen_at' => $now->toIso8601String(),
            ];

            $entries[] = $record;
            return $this->decorate($record, $now);
        });
    }

    public function network(): array
    {
        $now = now();
        $crews = array_values(array_filter(array_map(
            fn (array $entry) => $this->decorate($entry, $now),
            $this->read()
        ), fn (array $entry) => ($entry['online'] ?? false) === true));

        usort($crews, function (array $a, array $b) {
            $phaseA = (string) ($a['phase'] ?? '');
            $phaseB = (string) ($b['phase'] ?? '');
            if ($phaseA !== $phaseB) return strcmp($phaseA, $phaseB);
            return strcmp((string) ($a['pilot']['ident'] ?? ''), (string) ($b['pilot']['ident'] ?? ''));
        });

        $bySimulator = [];
        foreach ($crews as $crew) {
            $simulator = (string) ($crew['simulator'] ?: 'unknown');
            $bySimulator[$simulator] = ($bySimulator[$simulator] ?? 0) + 1;
        }
        ksort($bySimulator);

        return [
            'contract_version' => '1.0',
            'generated_at' => $now->toIso8601String(),
            'heartbeat_interval_seconds' => 15,
            'online_ttl_seconds' => self::ONLINE_TTL_SECONDS,
            'online_count' => count($crews),
            'by_simulator' => $bySimulator,
            'crews' => $crews,
        ];
    }

    private function decorate(array $entry, Carbon $now): array
    {
        $lastSeen = $this->date($entry['last_seen_at'] ?? null);
        $age = $lastSeen ? max(0, $lastSeen->diffInSeconds($now, false)) : PHP_INT_MAX;
        $entry['age_seconds'] = $age === PHP_INT_MAX ? null : $age;
        $entry['online'] = $lastSeen !== null && $age <= self::ONLINE_TTL_SECONDS;
        $entry['state'] = $entry['online'] ? 'ONLINE' : 'OFFLINE';
        return $entry;
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function date(mixed $value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function read(): array
    {
        $path = $this->path();
        if (!is_file($path)) return [];

        $handle = fopen($path, 'rb');
        if (!$handle) throw new RuntimeException('Impossible de lire le stockage de présence.');
        try {
            if (!flock($handle, LOCK_SH)) throw new RuntimeException('Impossible de verrouiller le stockage de présence.');
            $raw = stream_get_contents($handle);
            $decoded = json_decode($raw ?: '[]', true);
            return is_array($decoded) ? array_values($decoded) : [];
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function mutate(callable $callback): mixed
    {
        $path = $this->path();
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
            throw new RuntimeException('Impossible de créer le stockage de présence.');
        }

        $handle = fopen($path, 'c+b');
        if (!$handle) throw new RuntimeException('Impossible d’ouvrir le stockage de présence.');

        try {
            if (!flock($handle, LOCK_EX)) throw new RuntimeException('Impossible de verrouiller le stockage de présence.');
            rewind($handle);
            $raw = stream_get_contents($handle);
            $decoded = json_decode($raw ?: '[]', true);
            $entries = is_array($decoded) ? array_values($decoded) : [];

            $result = $callback($entries);

            rewind($handle);
            if (!ftruncate($handle, 0)) throw new RuntimeException('Impossible de réécrire le stockage de présence.');
            $json = json_encode(array_values($entries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            if (fwrite($handle, $json) === false) throw new RuntimeException('Impossible d’écrire le stockage de présence.');
            fflush($handle);
            return $result;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function path(): string
    {
        $root = $this->root ?: storage_path('app/promethee/presence');
        return rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'presence.json';
    }
}
