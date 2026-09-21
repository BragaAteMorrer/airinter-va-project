<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use Illuminate\Support\Facades\DB;

/**
 * Declarative configuration contract for the official desktop ACARS.
 * Values are deliberately whitelisted: the server can set policy, never run
 * code or provide arbitrary executable URLs to a pilot workstation.
 */
class AcarsConfigurationController extends Controller
{
    public function show()
    {
        return response()->json(['data' => [
            'schema_version' => 1,
            'live' => [
                'position_interval_seconds' => $this->integer('acars.position_interval_seconds', 15, 5, 300),
            ],
            'tracking' => [
                'recorded_events' => [
                    'BLOCK_OFF', 'TAXI_OUT', 'TAKEOFF', 'CLIMB', 'CRUISE',
                    'DESCENT', 'APPROACH', 'LANDING', 'TOUCHDOWN', 'BLOCK_ON',
                    'SLEW_ACTIVE', 'SIM_RATE_INCREASED', 'FUEL_INCREASED',
                ],
                'allow_simulation_rate' => $this->boolean('acars.allow_simulation_rate', false),
                'slew_policy' => $this->choice('acars.slew_policy', 'record', ['record', 'allow']),
            ],
            'pirep' => [
                'auto_send_allowed' => $this->boolean('acars.auto_send_allowed', false),
                'confirmation_required' => $this->boolean('acars.confirmation_required', true),
            ],
            'client' => [
                'minimum_version' => $this->nullableVersion('acars.minimum_version'),
                'latest_version' => $this->nullableVersion('acars.latest_version'),
                'release_notes_url' => $this->nullableHttpsUrl('acars.release_notes_url'),
                'download_url' => $this->nullableHttpsUrl('acars.download_url'),
            ],
        ]]);
    }

    private function raw(string $key): ?string
    {
        return DB::table('promethee_settings')->where('key', $key)->value('value');
    }

    private function integer(string $key, int $default, int $min, int $max): int
    {
        $value = filter_var($this->raw($key), FILTER_VALIDATE_INT);
        return $value === false || $value === null ? $default : max($min, min($max, $value));
    }

    private function boolean(string $key, bool $default): bool
    {
        $value = $this->raw($key);
        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function choice(string $key, string $default, array $allowed): string
    {
        $value = $this->raw($key);
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function nullableVersion(string $key): ?string
    {
        $value = $this->raw($key);
        return is_string($value) && preg_match('/^\d+\.\d+\.\d+$/', $value) ? $value : null;
    }

    private function nullableHttpsUrl(string $key): ?string
    {
        $value = $this->raw($key);
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL)
            && str_starts_with($value, 'https://') ? $value : null;
    }
}
