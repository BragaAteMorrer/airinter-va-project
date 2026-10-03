# Lot H — Continuité flotte

## Objectif

Faire du vol Hermès une vraie rotation de flotte : l'appareil sélectionné pour une opération doit conserver sa position, son état et son carburant observé après l'arrivée, sans créer un inventaire parallèle à phpVMS.

## Source de vérité

La table phpVMS `aircraft` reste l'autorité pour :

- l'aéroport courant (`airport_id`) ;
- l'état opérationnel (`state`) ;
- le temps de vol cumulé (`flight_time`) ;
- le carburant restant (`fuel_onboard`) ;
- la dernière arrivée (`landing_time`).

Le PIREP reste l'autorité pour le vol effectué. `promethee_telemetry` fournit les observations Hermès qui permettent de vérifier l'arrivée.

Aucune nouvelle table n'est ajoutée.

## API

### GET /api/v1/operations/{operation_id}/fleet-state

Retourne l'état phpVMS de l'appareil et la dernière observation Hermès : phase, état sol, carburant, fenêtre de télémétrie et nombre d'échantillons.

### POST /api/v1/operations/{operation_id}/fleet-state/reconcile

Réconcilie une arrivée observée par Hermès :

- exige un PIREP appartenant au pilote authentifié ;
- exige de la télémétrie ;
- exige une phase d'arrivée LANDING, TAXI_IN ou IN avec appareil au sol ;
- positionne l'appareil sur l'aéroport d'arrivée du PIREP ;
- repasse l'appareil en PARKED ;
- conserve le carburant final observé lorsqu'il est valide ;
- renseigne `landing_time`.

L'opération est idempotente : rejouer la réconciliation fixe les mêmes valeurs.

## Heures et cycles

Le temps de vol cumulé n'est volontairement pas incrémenté par Prométhée. phpVMS possède déjà son cycle d'acceptation PIREP et son champ `aircraft.flight_time`; l'incrémenter également depuis Hermès créerait un risque de double comptage.

Le Lot H expose donc le temps cumulé existant mais laisse son écriture au mécanisme phpVMS.

Le schéma actuel d'`aircraft` ne contient pas de compteur de cycles exploitable dans le modèle. Aucun compteur parallèle n'est inventé dans ce lot. Un futur compteur de cycles devra être ajouté au niveau du domaine phpVMS/maintenance avec une règle d'idempotence explicite.

## Conséquence opérationnelle

Si la règle phpVMS `pireps.only_aircraft_at_dpt_airport` est active, une rotation suivante ne pourra sélectionner l'appareil que depuis sa nouvelle position. Un F-Gxxx arrivé à LIRF est donc naturellement proposé pour un départ LIRF et non plus LFPO.
