<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['identities', 'oauthTokenFamilies' => fn ($query) => $query->latest('updated_at')]);

        $families = $user->oauthTokenFamilies
            ->groupBy('client_id')
            ->map(function ($items, $clientId) {
                $client = Passport::client()->newQuery()->find($clientId);
                return [
                    'client_id' => $clientId,
                    'name' => $client?->name ?: 'Application OAuth',
                    'active' => $items->where('status', 'active')->count(),
                    'last_used_at' => optional($items->max('last_used_at')),
                ];
            })
            ->values();

        $passkeys = $user->passkeys()
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get();

        $securityEvents = $user->securityEvents()
            ->latest('created_at')
            ->limit(30)
            ->get();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();

        return view('account', [
            'user' => $user,
            'applications' => $families,
            'sessions' => $sessions,
            'passkeys' => $passkeys,
            'securityEvents' => $securityEvents,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }
}
