<?php

namespace App\Http\Controllers\Frontend;

use App\Contracts\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switchLang(string $lang): RedirectResponse
    {
        abort_unless(array_key_exists($lang, config('languages')), 404);

        // A signed-in pilot's preference takes priority over the cookie.
        // Persisting the choice here keeps the selector consistent across
        // browsers and after the next sign-in.
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $lang])->save();
        }

        $cookie = Cookie::make('lang', $lang, 60 * 24 * 365);

        return back()->withCookie($cookie);
    }
}
