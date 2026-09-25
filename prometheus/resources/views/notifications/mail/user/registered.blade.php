@component('mail::message')
# Bienvenue chez Air Inter, {{ $user->name }}

Votre accès pilote est désormais ouvert.

Prométhée devient votre centre des opérations : réservation d’un vol Air Inter, affectation de l’appareil, préparation SimBrief, suivi Hermès et débriefing.

@component('mail::button', ['url' => url('/login')])
ACCÉDER À PROMÉTHÉE
@endcomponent

Votre identifiant pilote et votre mot de passe restent strictement personnels.

Bon vol et bienvenue dans la ligne,

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
