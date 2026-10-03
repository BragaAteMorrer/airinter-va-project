@component('mail::message')
# Votre candidature Air Inter

Bonjour {{ $user->name }},

Après examen, votre demande d’inscription Air Inter VA n’a pas été validée en l’état.

Si vous pensez qu’une information manque à votre dossier ou si vous souhaitez obtenir des précisions, vous pouvez contacter l’équipe Air Inter VA.

@component('mail::button', ['url' => url('/')])
CONTACTER / RETOURNER SUR AIR INTER VA
@endcomponent

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
