<?php

namespace Modules\Promethee\Http;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HermesReleaseController extends Controller
{
    private const REPOSITORY = 'BragaAteMorrer/airinter-va-project';

    public function latest(): JsonResponse
    {
        try {
            $release = Cache::remember('promethee.hermes.latest-release', now()->addMinutes(15), function () {
                $response = Http::acceptJson()->withHeaders(['User-Agent' => 'Air-Inter-Promethee'])->timeout(5)
                    ->get('https://api.github.com/repos/'.self::REPOSITORY.'/releases/latest');
                if (!$response->successful()) throw new \RuntimeException('GitHub release API unavailable.');

                $payload = $response->json();
                $tag = (string) ($payload['tag_name'] ?? '');
                if (!preg_match('/^hermes-v(\d+(?:\.\d+){1,3}(?:[-+][0-9A-Za-z.-]+)?)$/', $tag, $match))
                    throw new \RuntimeException('Latest release is not an Hermès release.');

                $assets = collect($payload['assets'] ?? []);
                $installer = $assets->first(fn ($asset) => preg_match('/^Hermes-ACARS-Setup-.*\.exe$/i', (string) ($asset['name'] ?? '')));
                if (!$installer) throw new \RuntimeException('Hermès installer asset is missing.');

                $checksumAsset = $assets->first(fn ($asset) => str_ends_with(strtolower((string) ($asset['name'] ?? '')), '.exe.sha256'));
                $sha256 = null;
                if ($checksumAsset && !empty($checksumAsset['browser_download_url'])) {
                    $checksum = Http::timeout(5)->get($checksumAsset['browser_download_url']);
                    if ($checksum->successful() && preg_match('/\b([a-f0-9]{64})\b/i', $checksum->body(), $hash)) $sha256 = strtolower($hash[1]);
                }

                return [
                    'version' => $match[1],
                    'download_url' => $installer['browser_download_url'],
                    'sha256' => $sha256,
                    'mandatory' => false,
                    'channel' => !empty($payload['prerelease']) ? 'beta' : 'stable',
                    'notes' => trim((string) ($payload['body'] ?? '')),
                    'published_at' => $payload['published_at'] ?? null,
                    'release_url' => $payload['html_url'] ?? null,
                ];
            });
            return response()->json(['data' => $release]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Le service de mise à jour Hermès est temporairement indisponible.'], 503);
        }
    }
}
