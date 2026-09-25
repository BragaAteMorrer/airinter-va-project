@component('mail::message')
# Invitation Air Inter

Vous avez été invité à rejoindre **Air Inter Virtual Airlines**.

Votre invitation permet de créer directement votre dossier pilote avec cette adresse e-mail.

@component('mail::panel')
**Compagnie d’entrée :** Air Inter · ITF  
**Espace opérations :** Prométhée  
**Client ACARS :** Hermès
@endcomponent

@component('mail::button', ['url' => $invite->link])
CRÉER MON DOSSIER PILOTE
@endcomponent

Cette invitation est personnelle. Si vous n’êtes pas à l’origine de cette demande, vous pouvez simplement ignorer ce message.

À bientôt à bord,

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
