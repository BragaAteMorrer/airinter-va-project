<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $argosOrigin = $this->origin((string) config('app.url'))
            ?? $this->origin($request->getSchemeAndHttpHost())
            ?? 'https://argos.airinter-va.org';
        $requestOrigin = $this->origin($request->getSchemeAndHttpHost());
        $prometheeOrigin = $this->origin((string) config('airinter-id.promethee_url'));

        $imageSources = array_values(array_unique(array_filter([
            "'self'",
            'data:',
            $prometheeOrigin,
        ])));

        $formSources = array_values(array_unique(array_filter([
            "'self'",
            $requestOrigin,
            $argosOrigin,
        ])));

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; style-src 'self' 'unsafe-inline'; img-src ".implode(' ', $imageSources)."; ".
            "font-src 'self'; script-src 'self'; connect-src 'self'; frame-ancestors 'none'; ".
            "base-uri 'self'; form-action ".implode(' ', $formSources)
        );

        if ($request->isSecure() || app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }

    private function origin(string $url): ?string
    {
        $parts = parse_url(trim($url));

        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $origin = $scheme.'://'.strtolower((string) $parts['host']);

        if (isset($parts['port'])) {
            $origin .= ':'.(int) $parts['port'];
        }

        return $origin;
    }
}
