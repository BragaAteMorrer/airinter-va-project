@component('mail::message')
# Compte-rendu de vol à revoir

Votre compte-rendu **{{ $pirep->ident ?? $pirep->id }}** n’a pas pu être validé en l’état.

@if($pirep->comments->count() > 0)
## Observations de l’exploitation
@foreach($pirep->comments as $comment)
- {{ $comment->comment }}
@endforeach
@endif

@component('mail::panel')
**Statut :** REFUSÉ / À CORRIGER  
**Départ :** {{ $pirep->dpt_airport_id ?? '—' }}  
**Arrivée :** {{ $pirep->arr_airport_id ?? '—' }}
@endcomponent

@component('mail::button', ['url' => route('frontend.pireps.show', [$pirep->id])])
OUVRIR LE COMPTE-RENDU
@endcomponent

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
