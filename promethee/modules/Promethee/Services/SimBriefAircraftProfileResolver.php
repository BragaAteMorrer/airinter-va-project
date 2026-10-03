<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;

class SimBriefAircraftProfileResolver
{
    public const STRATEGIES = ['native', 'custom_airframe', 'proxy'];

    public function resolve(Aircraft $aircraft, ?array $variant = null, ?string $typeOverride = null): array
    {
        $actualIcao = strtoupper(trim((string) (
            $aircraft->icao
            ?: ($variant['icao'] ?? null)
            ?: $aircraft->subfleet?->type
        )));
        $actualName = trim((string) (
            $aircraft->subfleet?->name
            ?: $aircraft->name
            ?: ($variant['label'] ?? null)
            ?: $actualIcao
        ));

        if (filled($typeOverride)) {
            $value = strtoupper(trim((string) $typeOverride));

            return [
                'strategy' => $this->looksLikeInternalId($value) ? 'custom_airframe' : 'native',
                'type' => $value,
                'source' => 'planning_override',
                'actual_icao' => $actualIcao,
                'actual_name' => $actualName,
                'proxy_type' => null,
                'internal_id' => $this->looksLikeInternalId($value) ? $value : null,
                'config' => [],
                'airframe_db_id' => null,
            ];
        }

        $profile = is_array($variant['simbrief_profile'] ?? null)
            ? $variant['simbrief_profile']
            : [];

        if ($profile !== []) {
            $strategy = strtolower(trim((string) ($profile['strategy'] ?? 'native')));
            abort_unless(in_array($strategy, self::STRATEGIES, true), 422,
                'Le profil sb-airframe '.$actualIcao.' utilise une stratégie SimBrief inconnue.');

            $profileIcao = strtoupper(trim((string) ($profile['icao'] ?? '')));
            $profileName = trim((string) ($profile['actual_name'] ?? ''));
            if ($profileIcao !== '') $actualIcao = $profileIcao;
            if ($profileName !== '') $actualName = $profileName;

            if ($strategy === 'custom_airframe') {
                $internalId = trim((string) ($profile['internal_id'] ?? $variant['simbrief_type'] ?? ''));
                abort_if($internalId === '', 422,
                    'Le profil sb-airframe '.$actualIcao.' est configuré en airframe personnalisé mais son Internal ID SimBrief est vide.');

                return $this->result(
                    $strategy,
                    $internalId,
                    'sb_airframe.custom_airframe',
                    $actualIcao,
                    $actualName,
                    null,
                    $internalId,
                    $profile,
                    $variant
                );
            }

            if ($strategy === 'proxy') {
                $proxy = strtoupper(trim((string) ($profile['proxy_type'] ?? '')));
                abort_if($proxy === '', 422,
                    'Le profil de planification Air Inter « '.$actualIcao.' » est incomplet : aucun type proxy SimBrief n’est configuré.');

                return $this->result(
                    $strategy,
                    $proxy,
                    'sb_airframe.proxy',
                    $actualIcao,
                    $actualName,
                    $proxy,
                    null,
                    $profile,
                    $variant
                );
            }

            $native = strtoupper(trim((string) ($profile['native_type'] ?? $actualIcao)));
            abort_if($native === '', 422,
                'Le profil sb-airframe natif ne contient aucun type SimBrief exploitable.');

            return $this->result(
                'native',
                $native,
                'sb_airframe.native',
                $actualIcao,
                $actualName,
                null,
                null,
                $profile,
                $variant
            );
        }

        $candidates = [
            'aircraft.simbrief_type' => $aircraft->simbrief_type,
            'subfleet.simbrief_type' => $aircraft->subfleet?->simbrief_type,
            'aircraft.icao' => $aircraft->icao,
            'subfleet.type' => $aircraft->subfleet?->type,
        ];

        foreach ($candidates as $source => $value) {
            $value = strtoupper(trim((string) $value));
            if ($value === '') continue;

            return [
                'strategy' => $this->looksLikeInternalId($value) ? 'custom_airframe' : 'native',
                'type' => $value,
                'source' => $source,
                'actual_icao' => $actualIcao,
                'actual_name' => $actualName,
                'proxy_type' => null,
                'internal_id' => $this->looksLikeInternalId($value) ? $value : null,
                'config' => [],
                'airframe_db_id' => null,
            ];
        }

        abort(422,
            'Aucun profil SimBrief n’est défini pour '.$aircraft->registration
            .' (sb-airframe, aircraft.simbrief_type, subfleet.simbrief_type et ICAO sont vides).');
    }

    private function result(
        string $strategy,
        string $type,
        string $source,
        string $actualIcao,
        string $actualName,
        ?string $proxyType,
        ?string $internalId,
        array $profile,
        ?array $variant
    ): array {
        return [
            'strategy' => $strategy,
            'type' => $type,
            'source' => $source,
            'actual_icao' => $actualIcao,
            'actual_name' => $actualName,
            'proxy_type' => $proxyType,
            'internal_id' => $internalId,
            'config' => $profile,
            'airframe_db_id' => $variant['airframe_db_id'] ?? null,
        ];
    }

    private function looksLikeInternalId(string $value): bool
    {
        return (bool) preg_match('/^\d+_\d+$/', $value);
    }
}
