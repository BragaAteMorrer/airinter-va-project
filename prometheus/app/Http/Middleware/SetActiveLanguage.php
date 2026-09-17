<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetActiveLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $preferredLanguage = config('app.locale', 'fr');
        // An authenticated pilot's choice takes precedence over the browser
        // cookie. This keeps the portal consistent when they change device.
        if ($request->user() && in_array($request->user()->locale, array_keys(config('languages')), true)) {
            $preferredLanguage = $request->user()->locale;
        } elseif (setting('general.auto_language_detection', false) && !$request->hasCookie('lang')) {
            $preferredLanguage = $request->getPreferredLanguage(array_keys(config('languages')));
        } else {
            $preferredLanguage = $request->cookie('lang', config('app.locale', 'fr'));
        }

        if (!array_key_exists($preferredLanguage, config('languages'))) {
            $preferredLanguage = config('app.fallback_locale', 'fr');
        }

        App::setLocale($preferredLanguage);

        return $next($request);
    }
}
