@component('mail::message')
# Nouvelle candidature pilote

Une nouvelle candidature vient d’être enregistrée dans Prométhée.

@component('mail::panel')
**Pilote :** {{ $user->name }}  
**E-mail :** {{ $user->email }}  
**Compagnie :** Air Inter · ITF  
**État :** {{ UserState::label($user->state) }}
@endcomponent

Vérifiez le dossier et validez le pilote depuis l’administration.

@component('mail::button', ['url' => url('/admin/users')])
OUVRIR L’ADMINISTRATION
@endcomponent

Prométhée · Air Inter VA
@endcomponent
