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
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $severity = trim((string) $request->query('severity', ''));

        $events = SecurityEvent::query()
            ->with('user:id,subject,display_name,email')
            ->when($severity !== '', fn ($q) => $q->where('severity', $severity))
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('type', 'like', '%'.$query.'%')
                        ->orWhere('ip_address', 'like', '%'.$query.'%')
                        ->orWhereHas('user', fn ($users) => $users
                            ->where('display_name', 'like', '%'.$query.'%')
                            ->orWhere('email', 'like', '%'.$query.'%')
                            ->orWhere('subject', 'like', '%'.$query.'%'));
                });
            })
            ->where(function ($q) {
                $q->whereIn('severity', ['medium', 'high'])
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

        $users = collect();
        if ($query !== '') {
            $users = User::query()
                ->withCount(['trustedDevices', 'securityEvents', 'passkeys'])
                ->where(function ($q) use ($query) {
                    $q->where('display_name', 'like', '%'.$query.'%')
                        ->orWhere('email', 'like', '%'.$query.'%')
                        ->orWhere('subject', 'like', '%'.$query.'%')
                        ->orWhereHas('identities', fn ($identities) => $identities
                            ->where('external_ident', 'like', '%'.$query.'%'));
                })
                ->limit(20)
                ->get();
        }

        $stats = [
            'users' => User::query()->count(),
            'events_24h' => SecurityEvent::query()->where('created_at', '>=', now()->subDay())->count(),
            'high_24h' => SecurityEvent::query()->where('severity', 'high')->where('created_at', '>=', now()->subDay())->count(),
            'trusted_devices' => TrustedDevice::query()->whereNull('revoked_at')->where('expires_at', '>', now())->count(),
            'mfa_users' => User::query()->whereNotNull('two_factor_confirmed_at')->count(),
            'passkey_users' => User::query()->whereHas('passkeys')->count(),
        ];

        return view('admin.security', compact('events', 'stats', 'users', 'query', 'severity'));
    }

    public function revokeUser(Request $request, User $user, AccountSecurityService $security): RedirectResponse
    {
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
