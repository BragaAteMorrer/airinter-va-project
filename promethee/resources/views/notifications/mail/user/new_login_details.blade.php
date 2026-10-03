@component('mail::message')
# Nouveaux accès Prométhée

Bonjour,

De nouveaux identifiants de connexion ont été générés pour votre compte Air Inter.

@component('mail::panel')
**Adresse e-mail :** {{ $user->email }}  
**Mot de passe temporaire :** {{ $newpw }}
@endcomponent

Pour votre sécurité, ce mot de passe doit être remplacé dès votre première connexion et ne doit être transmis à personne.

@component('mail::button', ['url' => url('/login')])
SE CONNECTER À PROMÉTHÉE
@endcomponent

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
