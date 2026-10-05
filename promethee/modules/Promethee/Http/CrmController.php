<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\{Airline, Rank, User};
use App\Models\Enums\UserState;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Promethee\Services\BrandingService;

class CrmController extends Controller
{
    private function page(string $name, array $data = [])
    {
        return view('promethee::'.$name, $data + [
            'branding' => app(BrandingService::class)->active(),
        ]);
    }

    public function index(Request $request)
    {
        $pilots = User::with(['rank:id,name','airline:id,icao,name'])
            ->whereNotIn('state', [UserState::DELETED])
            ->orderBy('pilot_id')
            ->get(['id','name','email','pilot_id','rank_id','airline_id','home_airport_id','flight_time','state']);

        $campaigns = DB::table('promethee_crm_campaigns as campaign')
            ->leftJoin('users', 'users.id', '=', 'campaign.created_by')
            ->leftJoin('promethee_crm_senders as sender', 'sender.id', '=', 'campaign.sender_id')
            ->select('campaign.*', 'users.name as creator_name', 'sender.email as sender_email', 'sender.name as sender_name')
            ->latest('campaign.created_at')
            ->limit(30)
            ->get();

        return $this->page('admin.crm', [
            'pilots' => $pilots,
            'ranks' => Rank::orderBy('hours')->orderBy('name')->get(['id','name']),
            'airlines' => Airline::orderBy('name')->get(['id','icao','name']),
            'bases' => $pilots->pluck('home_airport_id')->filter()->unique()->sort()->values(),
            'senders' => DB::table('promethee_crm_senders')->orderByDesc('is_default')->orderBy('name')->get(),
            'campaigns' => $campaigns,
            'mailTemplates' => DB::table('promethee_crm_mail_templates')->orderBy('id')->get(),
        ]);
    }

    public function saveSender(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:promethee_crm_senders,id',
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:191',
            'reply_to' => 'nullable|email:rfc|max:191',
            'active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->boolean('is_default')) {
            DB::table('promethee_crm_senders')->update(['is_default' => false, 'updated_at' => now()]);
        }

        $payload = [
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'reply_to' => !empty($data['reply_to']) ? strtolower(trim($data['reply_to'])) : null,
            'active' => $request->boolean('active'),
            'is_default' => $request->boolean('is_default'),
            'updated_at' => now(),
        ];

        if (!empty($data['id'])) {
            DB::table('promethee_crm_senders')->where('id', $data['id'])->update($payload);
        } else {
            DB::table('promethee_crm_senders')->insert($payload + ['created_at' => now()]);
        }

        return back()->with('success', 'Adresse expéditrice CRM enregistrée.');
    }

    public function saveMailTemplate(Request $request, string $key)
    {
        $template = DB::table('promethee_crm_mail_templates')->where('key', $key)->first();
        abort_unless($template, 404);

        $data = $request->validate([
            'subject' => 'required|string|max:191',
            'body' => 'required|string|max:30000',
            'active' => 'nullable|boolean',
        ]);

        DB::table('promethee_crm_mail_templates')
            ->where('key', $key)
            ->update([
                'subject' => trim($data['subject']),
                'body' => trim($data['body']),
                'active' => $request->boolean('active'),
                'updated_at' => now(),
            ]);

        DB::table('promethee_audit_logs')->insert([
            'actor_id' => $request->user()->id,
            'action' => 'crm.system_template.updated',
            'subject_type' => 'crm_mail_template',
            'subject_id' => $key,
            'context' => json_encode(['active' => $request->boolean('active')]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Modèle système « '.$template->label.' » enregistré.');
    }

    public function deleteSender(int $id)
    {
        abort_if(
            DB::table('promethee_crm_campaigns')->where('sender_id', $id)->exists(),
            422,
            'Cette adresse est déjà utilisée dans l’historique CRM. Désactivez-la plutôt que de la supprimer.'
        );

        DB::table('promethee_crm_senders')->where('id', $id)->delete();

        return back()->with('success', 'Adresse expéditrice supprimée.');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'sender_id' => 'required|integer|exists:promethee_crm_senders,id',
            'subject' => 'required|string|max:191',
            'body' => 'required|string|max:30000',
            'all_active' => 'nullable|boolean',
            'states' => 'nullable|array',
            'states.*' => 'integer|in:0,1,2,3,4',
            'rank_ids' => 'nullable|array',
            'rank_ids.*' => 'integer|exists:ranks,id',
            'airline_ids' => 'nullable|array',
            'airline_ids.*' => 'integer|exists:airlines,id',
            'bases' => 'nullable|array',
            'bases.*' => 'string|max:8',
            'pilot_ids' => 'nullable|array',
            'pilot_ids.*' => 'integer|exists:users,id',
            'min_hours' => 'nullable|numeric|min:0|max:100000',
            'max_hours' => 'nullable|numeric|min:0|max:100000|gte:min_hours',
        ]);

        $sender = DB::table('promethee_crm_senders')
            ->where('id', $data['sender_id'])
            ->where('active', true)
            ->first();
        abort_unless($sender, 422, 'Cette adresse expéditrice est désactivée.');

        $audience = [
            'all_active' => $request->boolean('all_active'),
            'states' => array_values($data['states'] ?? []),
            'rank_ids' => array_values($data['rank_ids'] ?? []),
            'airline_ids' => array_values($data['airline_ids'] ?? []),
            'bases' => array_values(array_map('strtoupper', $data['bases'] ?? [])),
            'pilot_ids' => array_values($data['pilot_ids'] ?? []),
            'min_hours' => isset($data['min_hours']) ? (float) $data['min_hours'] : null,
            'max_hours' => isset($data['max_hours']) ? (float) $data['max_hours'] : null,
        ];

        $hasFilter = $audience['all_active']
            || count($audience['states'])
            || count($audience['rank_ids'])
            || count($audience['airline_ids'])
            || count($audience['bases'])
            || count($audience['pilot_ids'])
            || $audience['min_hours'] !== null
            || $audience['max_hours'] !== null;

        abort_unless($hasFilter, 422, 'Sélectionnez au moins un critère ou des pilotes précis avant l’envoi.');

        $recipients = $this->resolveRecipients($audience);
        abort_if($recipients->isEmpty(), 422, 'Aucun pilote ne correspond à cette sélection.');
        abort_if($recipients->count() > 1000, 422, 'Sélection trop large : limitez la campagne à 1000 destinataires.');

        $campaignId = DB::table('promethee_crm_campaigns')->insertGetId([
            'created_by' => $request->user()->id,
            'sender_id' => $sender->id,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'audience' => json_encode($audience, JSON_UNESCAPED_UNICODE),
            'status' => 'sending',
            'recipient_count' => $recipients->count(),
            'sent_count' => 0,
            'failed_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $recipientId = DB::table('promethee_crm_recipients')->insertGetId([
                'campaign_id' => $campaignId,
                'user_id' => $recipient->id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'status' => 'queued',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subject = $this->merge($data['subject'], $recipient);
            $html = $this->renderEmail($data['body'], $recipient, $sender);

            try {
                Mail::html($html, function ($mail) use ($recipient, $subject, $sender) {
                    $mail->to($recipient->email, $recipient->name)
                        ->from($sender->email, $sender->name)
                        ->subject($subject);
                    if (!empty($sender->reply_to)) {
                        $mail->replyTo($sender->reply_to);
                    }
                });

                $sent++;
                DB::table('promethee_crm_recipients')->where('id', $recipientId)->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $failed++;
                DB::table('promethee_crm_recipients')->where('id', $recipientId)->update([
                    'status' => 'failed',
                    'error' => mb_substr($e->getMessage(), 0, 1000),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('promethee_crm_campaigns')->where('id', $campaignId)->update([
            'status' => $failed === 0 ? 'sent' : ($sent > 0 ? 'partial' : 'failed'),
            'sent_count' => $sent,
            'failed_count' => $failed,
            'sent_at' => $sent > 0 ? now() : null,
            'updated_at' => now(),
        ]);

        DB::table('promethee_audit_logs')->insert([
            'actor_id' => $request->user()->id,
            'action' => 'crm.campaign.sent',
            'subject_type' => 'crm_campaign',
            'subject_id' => (string) $campaignId,
            'context' => json_encode([
                'sender' => $sender->email,
                'recipients' => $recipients->count(),
                'sent' => $sent,
                'failed' => $failed,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with(
            'success',
            'Campagne CRM terminée : '.$sent.' envoyé(s)'
            .($failed ? ', '.$failed.' échec(s).' : '.')
        );
    }

    public function campaign(int $id)
    {
        $campaign = DB::table('promethee_crm_campaigns as campaign')
            ->leftJoin('users', 'users.id', '=', 'campaign.created_by')
            ->leftJoin('promethee_crm_senders as sender', 'sender.id', '=', 'campaign.sender_id')
            ->where('campaign.id', $id)
            ->select('campaign.*', 'users.name as creator_name', 'sender.name as sender_name', 'sender.email as sender_email')
            ->first();
        abort_unless($campaign, 404);

        $recipients = DB::table('promethee_crm_recipients')
            ->where('campaign_id', $id)
            ->orderBy('status')
            ->orderBy('name')
            ->get();

        return $this->page('admin.crm-campaign', compact('campaign', 'recipients'));
    }

    private function resolveRecipients(array $audience): Collection
    {
        $query = User::query()->with(['rank:id,name','airline:id,icao,name'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNotIn('state', [UserState::DELETED]);

        if ($audience['all_active']) {
            $query->whereIn('state', [UserState::ACTIVE, UserState::ON_LEAVE]);
        } elseif (count($audience['states'])) {
            $query->whereIn('state', $audience['states']);
        }

        if (count($audience['rank_ids'])) $query->whereIn('rank_id', $audience['rank_ids']);
        if (count($audience['airline_ids'])) $query->whereIn('airline_id', $audience['airline_ids']);
        if (count($audience['bases'])) $query->whereIn('home_airport_id', $audience['bases']);
        if ($audience['min_hours'] !== null) $query->where('flight_time', '>=', (int) round($audience['min_hours'] * 60));
        if ($audience['max_hours'] !== null) $query->where('flight_time', '<=', (int) round($audience['max_hours'] * 60));

        $hasSegmentCriteria = $audience['all_active']
            || count($audience['states'])
            || count($audience['rank_ids'])
            || count($audience['airline_ids'])
            || count($audience['bases'])
            || $audience['min_hours'] !== null
            || $audience['max_hours'] !== null;

        $filtered = $hasSegmentCriteria
            ? $query->get(['id','name','email','pilot_id','rank_id','airline_id','home_airport_id','flight_time','state'])
            : collect();

        if (!count($audience['pilot_ids'])) return $filtered->unique('email')->values();

        $manual = User::with(['rank:id,name','airline:id,icao,name'])
            ->whereIn('id', $audience['pilot_ids'])
            ->whereNotNull('email')
            ->get(['id','name','email','pilot_id','rank_id','airline_id','home_airport_id','flight_time','state']);

        return $filtered->concat($manual)->unique(fn ($user) => strtolower($user->email))->values();
    }

    private function merge(string $text, User $pilot): string
    {
        return strtr($text, [
            '{{name}}' => $pilot->name ?? '',
            '{{pilot_id}}' => $pilot->pilot_id ?? '',
            '{{rank}}' => $pilot->rank?->name ?? '',
            '{{airline}}' => $pilot->airline?->icao ?? '',
            '{{base}}' => $pilot->home_airport_id ?? '',
            '{{hours}}' => (string) round(((int) ($pilot->flight_time ?? 0)) / 60),
        ]);
    }

    private function renderEmail(string $body, User $pilot, object $sender): string
    {
        $merged = $this->merge($body, $pilot);
        $safeBody = nl2br(e($merged), false);
        $logo = e(asset('promethee-assets/logos/air-inter-1970s.png'));
        $senderName = e($sender->name);

        return <<<HTML
<!doctype html>
<html lang="fr">
<body style="margin:0;background:#eef3f7;font-family:Arial,Helvetica,sans-serif;color:#163047">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef3f7;padding:28px 12px">
<tr><td align="center">
<table role="presentation" width="680" cellspacing="0" cellpadding="0" style="max-width:680px;width:100%;background:#ffffff;border:1px solid #cfdae4">
<tr><td style="padding:24px 28px;border-top:6px solid #d92936">
<img src="{$logo}" alt="Air Inter" style="max-width:180px;height:auto">
<div style="margin-top:16px;font-size:11px;font-weight:bold;letter-spacing:1.5px;color:#55738d">AIR INTER · PROMÉTHÉE CRM</div>
</td></tr>
<tr><td style="padding:8px 28px 30px;font-size:15px;line-height:1.65">{$safeBody}</td></tr>
<tr><td style="padding:18px 28px;background:#0b2233;color:#dbe8f1;font-size:11px;line-height:1.5">
Message envoyé par {$senderName} depuis Prométhée. Cet e-mail est adressé individuellement à votre compte pilote Air Inter.
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
