# Dispatch autoritaire — API v1

Le Dispatch Prométhée est l'autorité serveur qui décide si Hermès peut démarrer une opération.

## Endpoint

```
GET /api/v1/operations/{operation_id}/dispatch
```

## États

- `PREPARATION_REQUIRED` : un contrôle serveur manque.
- `READY` : tous les contrôles serveur sont validés ; Hermès peut exécuter les contrôles locaux.
- `IN_PROGRESS` : le PIREP existe et de la télémétrie Hermès a été reçue.
- `COMPLETED` : le PIREP a été déposé/soumis ou est arrivé dans un état final phpVMS.
- `CANCELLED` : le PIREP/opération a été annulé.

Seul `READY` expose `can_start=true`.

## Contrôles serveur

```
operation  réservation valide
aircraft   appareil affecté, actif, au parking et correctement positionné si la règle est active
ofp        ligne SimBrief exacte encore active ou rattachée au PIREP de l'opération
pirep      PIREP pré-déposé, IN_PROGRESS et non annulé
```

`PIREP_PREFILED` n'est pas un contrôle client. Prométhée connaît le PIREP et tranche côté serveur.

## Contrôles Hermès

Lorsque le serveur répond `READY`, Hermès doit encore vérifier localement :

```
SIMULATOR_CONNECTED
AIRCRAFT_MATCH
DEPARTURE_MATCH
```

Hermès interroge à nouveau le Dispatch immédiatement avant Start. Si `can_start` est faux, l'enregistrement n'est pas lancé et les actions manquantes retournées par Prométhée sont affichées.

## OFP après prefile

phpVMS rattache le SimBrief au PIREP lors du prefile. Le resolver Prométhée recherche donc d'abord le SimBrief portant le `pirep_id` de l'opération, puis seulement un OFP actif non encore rattaché. Le Dispatch ne perd ainsi plus son OFP au moment exact où le PIREP est créé.

## BDD

Aucune migration n'est ajoutée par ce lot. Les états PIREP, SimBrief et la télémétrie Prométhée existante sont utilisés comme faits serveur.
