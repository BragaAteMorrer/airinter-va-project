# Lot G — Live Operations

## Objectif

Faire de Prométhée l’OCC temps réel des vols Hermès sans créer une seconde source de vérité. Le PIREP phpVMS reste le dossier de vol ; `operation_id` reste l’identité publique stable.

## Flux

1. Hermès démarre uniquement après un Dispatch `READY`.
2. Le recorder conserve `operation_id` et `pirep_id`.
3. Les positions sont envoyées sur `POST /api/v1/operations/{operation_id}/telemetry`.
4. Prométhée résout le PIREP par l’identité d’opération authentifiée.
5. Les échantillons idempotents sont archivés dans `promethee_telemetry`.
6. Live Operations agrège les PIREP en cours et leur télémétrie Hermès.

Aucune nouvelle table n’est nécessaire.

## Contrat Live Operations

Chaque vol expose notamment :

- `operation_id`, PIREP, vol, pilote et immatriculation ;
- phase Hermès courante ;
- position, altitude, IAS, GS, VS, cap et carburant ;
- âge et état du signal : `LIVE`, `STALE`, `LOST` ou `NO_SIGNAL` ;
- historique des transitions de phase ;
- jalons opérationnels `OUT`, `OFF`, `ON`, `IN` dérivés de la télémétrie enregistrée ;
- distance restante et ETA indicatives calculées par Prométhée.

Les jalons sont factuels : ils décrivent ce qu’Hermès a observé. Ils ne constituent ni une note ni une pénalité.

## Jalons

En attendant un journal d’événements serveur dédié, les jalons sont reconstruits depuis les premières phases observées :

- OUT : première sortie de BOARDING vers PUSHBACK/TAXI_OUT ou une phase ultérieure ;
- OFF : première phase TAKEOFF ou ultérieure ;
- ON : première phase LANDING/TAXI_IN/IN ;
- IN : première phase IN.

Cette reconstruction utilise l’archive persistée, pas l’heure du rafraîchissement OCC.

## Résilience

La télémétrie utilise `sample_id` avec `insertOrIgnore` : un retry Hermès ne duplique pas une position. Une opération sans PIREP préparé reçoit HTTP 409. Les anciennes routes PIREP restent uniquement pour les clients legacy.

L’OCC charge la télémétrie de tous les vols actifs en une requête groupée, au lieu d’une requête par vol, puis rafraîchit l’affichage toutes les cinq secondes.

## Hors périmètre

Le Lot G ne modifie pas la position permanente de la flotte après acceptation du PIREP : ce sera traité dans le Lot H en s’appuyant sur le comportement phpVMS existant.
