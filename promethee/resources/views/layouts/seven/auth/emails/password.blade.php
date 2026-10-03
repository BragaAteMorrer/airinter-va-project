@component('mail::message')
# Réinitialisation de votre accès

Une demande de réinitialisation du mot de passe a été reçue pour votre compte Air Inter.

@component('mail::button', ['url' => $link = url('password/reset', $token).'?email='.urlencode($user->getEmailForPasswordReset())])
RÉINITIALISER MON MOT DE PASSE
@endcomponent

Si vous n’êtes pas à l’origine de cette demande, aucune action n’est nécessaire.

Pour votre sécurité, ne transmettez jamais ce lien à un tiers.

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
