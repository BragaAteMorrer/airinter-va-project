# Contrat SimBrief par opération — API v1

Prométhée traite désormais l'opération comme point d'entrée du workflow SimBrief d'Hermès.

## Endpoints

```
POST /api/v1/operations/{operation_id}/simbrief/session
POST /api/v1/operations/{operation_id}/simbrief/redirect
POST /api/v1/operations/{operation_id}/simbrief/account/import
POST /api/v1/operations/{operation_id}/simbrief/import
```

L'identifiant public `operation_id` est résolu vers le Bid phpVMS historique. Aucun changement de schéma de base de données n'est nécessaire.

## Corrélation SimBrief

Pour les appels API v1, le `static_id` envoyé à SimBrief est déterministe :

```
AIRINTER_OP_{OPERATION_ID}
```

Le même identifiant est utilisé pour ouvrir, modifier et réimporter le plan. Le serveur reconstruit le vol et l'appareil depuis l'opération authentifiée : Hermès ne choisit pas librement un autre vol ou appareil dans ces routes.

Les réponses SimBrief v1 exposent également `operation_id`.

## Compatibilité

Les routes historiques `/api/acars/flights/{flight}/simbrief/*` restent disponibles pendant la transition Prometheus → Prométhée. Elles ne sont plus utilisées par le parcours SimBrief normal d'Hermès lorsqu'une opération Prométhée est sélectionnée.

La base phpVMS existante reste la source de vérité pour les Bid, SimBrief et futurs PIREP. Le lot B n'ajoute, ne supprime et ne migre aucune table.

## Suite

Le lot C introduira le prefile PIREP piloté par l'opération. Le dispatch pourra alors vérifier côté serveur l'existence du PIREP et ne laisser à Hermès que les contrôles locaux simulateur/appareil/aéroport.
