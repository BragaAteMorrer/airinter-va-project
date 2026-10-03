@component('mail::message')
# Nouveau compte-rendu de vol

Un nouveau PIREP vient d’être transmis à l’exploitation.

@component('mail::panel')
**Pilote :** {{ $pirep->user->ident }} · {{ $pirep->user->name }}  
**Vol :** {{ $pirep->ident ?? $pirep->id }}  
**Route :** {{ $pirep->dpt_airport_id ?? '—' }} → {{ $pirep->arr_airport_id ?? '—' }}
@endcomponent

@component('mail::button', ['url' => route('admin.pireps.edit', [$pirep->id])])
OUVRIR LE PIREP DANS PROMÉTHÉE
@endcomponent

Prométhée · Air Inter VA
@endcomponent
