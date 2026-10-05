<?php

namespace App\Notifications\Messages;

use App\Contracts\Notification;
use App\Models\User;
use App\Notifications\Channels\MailChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Promethee\Services\CrmMailTemplateService;

class UserRegistered extends Notification implements ShouldQueue
{
    private bool $mailEnabled = true;

    use MailChannel;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly User $user
    ) {
        parent::__construct();

        $template = app(CrmMailTemplateService::class)->resolve('pilot.welcome', $this->user);
        $this->mailEnabled = $template['active'];
        if ($this->mailEnabled) {
            $this->setMailable(
                $template['subject'],
                'notifications.mail.user.crm-template',
                ['bodyHtml' => $template['body_html']]
            );
        }
    }

    public function via($notifiable)
    {
        return $this->mailEnabled ? ['mail'] : [];
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
        ];
    }
}
