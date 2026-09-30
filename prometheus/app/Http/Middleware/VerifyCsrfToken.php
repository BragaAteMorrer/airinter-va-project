<?php

namespace App\Http\Middleware;

use App\Contracts\Middleware;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken extends BaseVerifier implements Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    public function handle($request, Closure $next): Response
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMaxBytes = $this->iniBytes((string) ini_get('post_max_size'));

        // When CONTENT_LENGTH is greater than PHP's post_max_size, PHP discards
        // the complete request body before Laravel sees it. That also removes
        // _token and _method, which otherwise looks like a CSRF failure (419).
        if ($postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            abort(
                413,
                'Fichier trop volumineux pour la configuration PHP du serveur. '.
                'Limite post_max_size actuelle : '.ini_get('post_max_size').'.'
            );
        }

        return parent::handle($request, $next);
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '0') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }
}
