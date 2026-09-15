<?php

namespace App\Http\Middleware;

use App\Contracts\Middleware;
use Closure;
use Igaster\LaravelTheme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Read the current theme from the settings (set in admin), and set it
 */
class SetActiveTheme implements Middleware
{
    private static $skip = [
        'admin',
        'admin/*',
        'api',
        'api/*',
        'importer',
        'importer/*',
        'install',
        'install/*',
        'update',
        'update/*',
    ];

    /**
     * Handle the request
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->setTheme($request);

        return $next($request);
    }

    /**
     * Set the theme for the current middleware
     */
    public function setTheme(Request $request)
    {
        if ($request->is(self::$skip)) {
            return;
        }

        try {
            $theme = setting('general.theme', 'seven');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $theme = 'seven';
        }

        // SPTheme is still stored in the production settings, but its Blade
        // views are no longer installed. Use the complete Promethee shell
        // instead of letting every legacy page fail with a missing view.
        if ($theme === 'SPTheme') {
            $theme = 'beta';
        }

        $themesPath = config('themes.themes_path');
        if (!is_dir($themesPath.DIRECTORY_SEPARATOR.$theme)) {
            Log::warning("Configured theme [{$theme}] is unavailable; using the Promethee theme instead.");
            $theme = 'beta';
        }

        Theme::set($theme);
    }
}
