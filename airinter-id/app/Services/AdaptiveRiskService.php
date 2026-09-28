<?php

namespace App\Services;

use App\Models\SecurityEvent;
use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Http\Request;

class AdaptiveRiskService
{
    public function assess(Request $request, User $user, ?TrustedDevice $trustedDevice = null): array
    {
        $score = 0;
        $reasons = [];
        $ip = (string) $request->ip();
        $ua = mb_substr((string) $request->userAgent(), 0, 1000);

        $knownExactContext = SecurityEvent::query()
            ->where('user_id', $user->id)
            ->where('type', 'login.succeeded')
            ->where('ip_address', $ip)
            ->where('user_agent', $ua)
            ->exists();

        if (!$knownExactContext) {
            $knownIp = SecurityEvent::query()
                ->where('user_id', $user->id)
                ->where('type', 'login.succeeded')
                ->where('ip_address', $ip)
                ->exists();

            $knownUa = SecurityEvent::query()
                ->where('user_id', $user->id)
                ->where('type', 'login.succeeded')
                ->where('user_agent', $ua)
                ->exists();

            if (!$knownIp && !$knownUa) {
                $score += 40;
                $reasons[] = 'new_ip_and_browser';
            } elseif (!$knownIp) {
                $score += 25;
                $reasons[] = 'new_ip';
            } elseif (!$knownUa) {
                $score += 25;
                $reasons[] = 'new_browser';
            }
        }

        $recentFailures = SecurityEvent::query()
            ->where('user_id', $user->id)
            ->where('type', 'login.failed')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->count();

        if ($recentFailures >= 5) {
            $score += 35;
            $reasons[] = 'multiple_recent_failures';
        } elseif ($recentFailures >= 2) {
            $score += 15;
            $reasons[] = 'recent_failures';
        }

        if (!$user->hasVerifiedEmail()) {
            $score += 10;
            $reasons[] = 'email_unverified';
        }

        if ($trustedDevice?->active()) {
            $score = max(0, $score - 30);
            $reasons[] = 'trusted_device';
        }

        $level = match (true) {
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };

        return [
            'score' => $score,
            'level' => $level,
            'reasons' => $reasons,
            'require_step_up' => $level === 'high',
            'allow_trusted_device_bypass' => $level === 'low',
        ];
    }

    public function severityForScore(int $score): string
    {
        return match (true) {
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'info',
        };
    }
}
