<?php

namespace App\Http\Controllers;

use App\Models\SecurityEvent;
use App\Services\AccountSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Passport\Passport;

class AccountActivityController extends Controller
{
    public function index(Request $request): View
    {
        $events = $request->user()->securityEvents()
            ->latest('created_at')
            ->paginate(50)
            ->through(function (SecurityEvent $event) {
                $metadata = (array) $event->metadata;
                $clientId = $metadata['client_id'] ?? null;
                $client = $clientId ? Passport::client()->newQuery()->find($clientId) : null;

                $event->setAttribute('application_name', $client?->name ?? $this->applicationName($event));
                $event->setAttribute('display_label', $this->label($event->type));

                return $event;
            });

        return view('account.activity', compact('events'));
    }

    public function report(
        Request $request,
        SecurityEvent $event,
        AccountSecurityService $security,
    ): RedirectResponse {
        abort_unless((int) $event->user_id === (int) $request->user()->id, 404);

        if ($event->reported_at) {
            return back()->with('status', 'Cet événement a déjà été signalé.');
        }

        $event->forceFill([
            'reported_at' => now(),
            'severity' => 'high',
            'risk_score' => max(100, (int) ($event->risk_score ?? 0)),
        ])->save();

        $user = $request->user();

        // Keep the reporting session alive so the legitimate owner can secure
        // the account, but immediately cut every other web/OAuth context.
        $security->revokeOtherSecurityContexts($user, $request->session()->getId());

        $user->trustedDevices()
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $security->record($request, $user, 'account.incident.reported', [
            'security_event_id' => $event->id,
            'reported_type' => $event->type,
            'automatic_response' => [
                'other_sessions_revoked',
                'oauth_access_revoked',
                'trusted_devices_revoked',
            ],
        ]);

        $request->session()->put('auth.password_confirmed_at', 0);

        return redirect()->route('account.activity')
            ->with('status', 'Signalement enregistré. Les autres sessions, accès OAuth et appareils de confiance ont été révoqués. Changez maintenant votre mot de passe et vérifiez votre MFA.');
    }

    private function applicationName(SecurityEvent $event): string
    {
        $type = strtolower($event->type);
        if (str_contains($type, 'hermes')) {
            return 'Hermès';
        }
        if (str_contains($type, 'promethee') || str_contains($type, 'prométhée')) {
            return 'Prométhée';
        }
        if (str_starts_with($type, 'oauth.')) {
            return 'Application Air Inter';
        }

        return 'Argos';
    }

    private function label(string $type): string
    {
        return [
            'login.succeeded' => 'Connexion à Argos',
            'login.failed' => 'Tentative de connexion échouée',
            'login.new_context' => 'Connexion depuis un nouveau contexte',
            'logout' => 'Déconnexion d’Argos',
            'password.changed' => 'Mot de passe modifié',
            'profile.email_changed' => 'Adresse e-mail modifiée',
            'mfa.enabled' => 'MFA activé',
            'mfa.disabled' => 'MFA désactivé',
            'mfa.recovery_codes.regenerated' => 'Codes de secours régénérés',
            'passkey.registered' => 'Nouvelle passkey enregistrée',
            'passkey.deleted' => 'Passkey supprimée',
            'session.revoked' => 'Session révoquée',
            'session.others_revoked' => 'Autres sessions révoquées',
            'trusted_device.revoked' => 'Appareil de confiance révoqué',
            'oauth.family.revoked' => 'Autorisation d’application révoquée',
            'oauth.refresh.reuse_detected' => 'Réutilisation suspecte d’un jeton OAuth',
            'account.incident.reported' => 'Activité signalée comme inconnue',
        ][$type] ?? str_replace('.', ' · ', $type);
    }
}
