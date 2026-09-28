<?php

namespace App\Http\Controllers;

use App\Models\OauthTokenFamily;
use App\Models\SecurityEvent;
use App\Services\TokenSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;

class AccountSecurityController extends Controller
{
    public function revokeApplication(Request $request, string $clientId, TokenSecurityService $tokens): RedirectResponse
    {
        $tokens->revokeUserClient($request->user(), $clientId);

        return back()->with('status', 'Accès de l’application révoqué.');
    }

    public function revokeSession(Request $request, string $sessionId): RedirectResponse
    {
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $request->user()->id)
            ->delete();

        SecurityEvent::create([
            'user_id' => $request->user()->id,
            'type' => 'session.revoked',
            'metadata' => ['session_id' => substr($sessionId, 0, 12)],
            'created_at' => now(),
        ]);

        return back()->with('status', 'Session révoquée.');
    }

    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        SecurityEvent::create([
            'user_id' => $request->user()->id,
            'type' => 'session.others_revoked',
            'created_at' => now(),
        ]);

        return back()->with('status', 'Toutes les autres sessions ont été déconnectées.');
    }
}
