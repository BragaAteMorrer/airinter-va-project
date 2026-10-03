# Lot I — Post-flight factuel

## Objectif

Transformer le PIREP phpVMS et l'archive de télémétrie Hermès en un debrief lisible sans inventer une note pilote ni transformer l'OCC en système disciplinaire.

Le post-flight sépare trois lectures :

- **Safety** : événements observables liés aux limites disponibles dans la télémétrie (touchdown, dépassement de vitesse) ;
- **Operations** : critères opérationnels évaluables, notamment les checkpoints d'approche stabilisée ;
- **Flight** : chronologie factuelle du vol construite depuis les transitions observées.

## Contrat

`GET /api/v1/operations/{operation_id}/debrief`

La réponse contient :

- `summary` : vol, route, appareil, heures bloc, durée, carburant, landing rate et couverture télémétrique ;
- `debrief.safety` ;
- `debrief.operations` ;
- `debrief.flight` ;
- `data_quality` pour rendre explicites les données manquantes ;
- `provenance` afin de distinguer phpVMS, Hermès et l'analyse Prométhée.

Les statuts de section sont volontairement descriptifs :

- `OBSERVED` : données disponibles sans warning détecté par les règles connues ;
- `ATTENTION` : au moins un événement warning a été observé ;
- `INSUFFICIENT_DATA` : le serveur ne dispose pas des données nécessaires.

`OBSERVED` ne signifie pas « vol sûr » ou « pilote conforme ». L'absence de donnée n'est jamais transformée en réussite.

## Événements

Chaque événement expose un code stable, un niveau, un libellé, un timestamp lorsqu'il existe et les valeurs factuelles associées.

La timeline de vol conserve les transitions détectées à partir de la télémétrie : roulage initial, décollage, début de descente et atterrissage lorsque les données permettent de les observer.

## Compatibilité

Les clés historiques `facts` et `timeline` restent présentes pour les consommateurs Prométhée existants. Les nouveaux clients Hermès doivent utiliser les sections Safety / Operations / Flight.

Le score pédagogique historique n'est pas utilisé dans le contrat Post-flight v1.
