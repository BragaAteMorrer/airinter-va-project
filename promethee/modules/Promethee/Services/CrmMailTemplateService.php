<?php

namespace Modules\Promethee\Services;

use App\Models\Enums\UserState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmMailTemplateService
{
    public function resolve(string $key, User $user): array
    {
        $template = DB::table('promethee_crm_mail_templates')->where('key', $key)->first();

        if (!$template || !$template->active) {
            return ['active' => false, 'subject' => '', 'body' => '', 'body_html' => ''];
        }

        $subject = $this->merge((string) $template->subject, $user);
        $body = $this->merge((string) $template->body, $user);

        return [
            'active' => true,
            'subject' => $subject,
            'body' => $body,
            'body_html' => Str::markdown($body, [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]),
        ];
    }

    public function merge(string $text, User $user): string
    {
        return strtr($text, [
            '{{name}}' => (string) ($user->name ?? ''),
            '{{email}}' => (string) ($user->email ?? ''),
            '{{pilot_id}}' => (string) ($user->pilot_id ?? ''),
            '{{ident}}' => (string) ($user->ident ?? ''),
            '{{rank}}' => (string) ($user->rank?->name ?? ''),
            '{{airline}}' => (string) ($user->airline?->icao ?? 'ITF'),
            '{{base}}' => (string) ($user->home_airport_id ?? ''),
            '{{state}}' => UserState::label((int) $user->state),
            '{{promethee_url}}' => rtrim((string) config('app.url'), '/'),
        ]);
    }
}
