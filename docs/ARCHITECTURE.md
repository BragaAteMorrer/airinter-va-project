# Architecture cible

Prométhée est le système opérationnel d’Air Inter VA. Hermès est le compagnon du vol. phpVMS reste le moteur métier existant tant que cette séparation est respectée.

## Responsabilités

| Composant | Responsabilité | Ne doit pas devenir |
| --- | --- | --- |
| airinter-va.org | Musée, archives, découverte et recrutement | Un second OCC |
| Prométhée | Opérations, réservations, briefing, flotte, carrière et patrimoine | Un simple thème phpVMS |
| API Air Inter | Contrats métier stables entre les applications | Un accès direct aux tables phpVMS |
| Hermès | Préparation, connexion simulateur, télémétrie, événements et PIREP | Un second Prométhée |
| SimBrief | Calcul de l’OFP | Une logique dupliquée dans Prométhée |

## Flux de référence

1. Le pilote choisit une opération dans Prométhée.
2. Prométhée agrège réservation, appareil, météo, contexte historique et OFP.
3. Hermès récupère un briefing normalisé via l’API.
4. Hermès suit le vol et conserve localement les données non synchronisées.
5. L’API valide les événements et crée le PIREP.
6. Prométhée met à jour opérations, flotte, carnet et progression.

## Contrat API

La façade métier cible est versionnée sous `/api/v1` :

- `/me`
- `/operations`
- `/flights`
- `/bookings`
- `/aircraft`
- `/briefings`
- `/pireps`
- `/events`
- `/missions`
- `/passport`
- `/career`

Hermès ne doit dépendre ni du schéma SQL ni d’un objet interne tel que `bid.flight.id`. Les réponses exposent des identifiants métier, un état explicite et une version de contrat.

## Principes produit

- **Operations** : ce qui se passe maintenant.
- **Flight** : ce que le pilote prépare et exécute.
- **Career** : ce qu’il a accompli.
- **Heritage** : ce que le vol représente dans l’histoire d’Air Inter.

Toute nouvelle fonctionnalité doit renforcer au moins une couche et définir ses liens avec les autres.

## Données historiques

Un horaire historique porte à terme `valid_from`, `valid_until`, `historical_source`, `source_page`, `confidence`, `aircraft_family`, `historical_registration` et un statut `verified`, `reconstructed` ou `fictional-event`.

La provenance doit être visible. Une reconstruction ne doit jamais être présentée comme une certitude historique.

## Sécurité

- Aucun secret, export d’hébergement, journal de production ou sauvegarde de base n’est versionné.
- Les jetons Hermès restent temporaires et stockés hashés côté serveur.
- Les entrées API sont validées côté serveur ; la télémétrie cliente n’est jamais une autorité.
- Les dépendances et tests sont contrôlés par la CI.
