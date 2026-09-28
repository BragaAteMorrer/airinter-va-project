<?php

namespace App\Http\Controllers;

use App\Models\OauthTokenFamily;
use App\Models\SecurityEvent;
use App\Models\TrustedDevice;
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

    public function revokeTrustedDevice(Request $request, TrustedDevice $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 404);

        $device->forceFill(['revoked_at' => now()])->save();

        SecurityEvent::create([
            'user_id' => $request->user()->id,
            'type' => 'trusted_device.revoked',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        return back()->with('status', 'Appareil de confiance révoqué.');
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
