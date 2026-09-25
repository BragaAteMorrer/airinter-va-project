@component('mail::message')
# Candidature reçue, {{ $user->name }}

Votre dossier pilote Air Inter a bien été transmis.

Il est maintenant **en attente de validation par l’équipe Air Inter VA**. Vous recevrez automatiquement un nouveau message dès que votre accès Prométhée sera ouvert.

Aucune action supplémentaire n’est nécessaire pour le moment.

@component('mail::panel')
**Statut :** DOSSIER REÇU · VALIDATION EN ATTENTE
@endcomponent

À très bientôt à bord,

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
