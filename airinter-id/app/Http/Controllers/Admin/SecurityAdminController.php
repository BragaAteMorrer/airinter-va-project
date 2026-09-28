<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\TrustedDevice;
use App\Models\User;
use App\Services\AccountSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityAdminController extends Controller
{
    public function index(): View
    {
        $events = SecurityEvent::query()
            ->with('user:id,subject,display_name,email')
            ->where(function ($query) {
                $query->whereIn('severity', ['medium', 'high'])
                    ->orWhereIn('type', [
                        'oauth.refresh.reuse_detected',
                        'login.new_context',
                        'mfa.disabled',
                        'passkey.deleted',
                    ]);
            })
            ->latest('created_at')
            ->limit(100)
            ->get();

        $stats = [
            'events_24h' => SecurityEvent::query()->where('created_at', '>=', now()->subDay())->count(),
            'high_24h' => SecurityEvent::query()->where('severity', 'high')->where('created_at', '>=', now()->subDay())->count(),
            'trusted_devices' => TrustedDevice::query()->whereNull('revoked_at')->where('expires_at', '>', now())->count(),
            'mfa_users' => User::query()->whereNotNull('two_factor_confirmed_at')->count(),
            'passkey_users' => User::query()->whereHas('passkeys')->count(),
        ];

        return view('admin.security', compact('events', 'stats'));
    }

    public function revokeUser(
        Request $request,
        User $user,
        AccountSecurityService $security,
    ): RedirectResponse {
        $security->revokeOtherSecurityContexts($user);

        TrustedDevice::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $security->record($request, $user, 'admin.security_contexts.revoked', [
            'actor_subject' => $request->user()->subject,
        ]);

        return back()->with('status', 'Sessions, tokens OAuth et appareils de confiance révoqués pour '.$user->display_name.'.');
    }
}
