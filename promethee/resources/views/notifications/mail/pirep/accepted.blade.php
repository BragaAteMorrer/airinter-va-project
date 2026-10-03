@component('mail::message')
# Compte-rendu de vol accepté

Votre vol **{{ $pirep->ident ?? $pirep->id }}** a été validé par l’exploitation Air Inter.

@component('mail::panel')
**Statut :** ACCEPTÉ  
**Départ :** {{ $pirep->dpt_airport_id ?? '—' }}  
**Arrivée :** {{ $pirep->arr_airport_id ?? '—' }}
@endcomponent

Le vol est désormais intégré à votre activité pilote dans Prométhée.

@component('mail::button', ['url' => route('frontend.pireps.show', [$pirep->id])])
CONSULTER LE COMPTE-RENDU
@endcomponent

Bon vol,

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
