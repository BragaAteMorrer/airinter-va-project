# Vol E2E de référence — Prométhée / Hermès

Ce scénario est le parcours de non-régression prioritaire de l'écosystème Air Inter VA.

## Préconditions serveur

1. Le pilote est authentifié dans Hermès.
2. Une réservation phpVMS est exposée comme opération API v1.
3. Un appareil éligible est affecté.
4. SimBrief est préparé via l'opération et son OFP exact est persisté.
5. Le PIREP est pré-déposé via `POST /api/v1/operations/{operation_id}/pirep`.
6. `GET /api/v1/operations/{operation_id}/dispatch` retourne `READY` et `can_start=true`.
7. Hermès valide localement simulateur connecté, appareil correspondant et aéroport de départ.

Hermès ne démarre pas le recorder si l'étape 6 échoue.

## Parcours de référence

```
Connexion Hermès
  -> Mes opérations
  -> sélection opération
  -> appareil
  -> SimBrief / OFP
  -> prefile PIREP
  -> Dispatch READY
  -> Start
  -> BOARDING / OUT
  -> PUSHBACK
  -> TAXI_OUT
  -> TAKEOFF / OFF
  -> CLIMB
  -> CRUISE / ENROUTE
  -> DESCENT
  -> APPROACH
  -> FINAL
  -> LANDING / ON
  -> TAXI_IN
  -> IN
  -> synchronisation des messages
  -> dépôt PIREP
  -> Dispatch COMPLETED
  -> débrief Prométhée
```

## Invariants

- `operation_id` reste identique du Dispatch jusqu'à la télémétrie.
- `pirep_id` reste identique du prefile au dépôt final.
- le Start est impossible sans Dispatch `READY`.
- la première télémétrie fait passer le serveur à `IN_PROGRESS`.
- le dépôt final est impossible avant `IN`.
- le recorder ne se termine pas tant que des messages ACARS restent à synchroniser.
- une coupure réseau conserve les messages localement et une reprise ne crée pas une seconde opération.
- les anomalies sont enregistrées comme faits, pas transformées arbitrairement en pénalités.

## Automatisation

`ReferenceFlightE2ETests` couvre la machine d'état simulateur-neutre du départ parking à BLOCK_ON, la conservation locale de l'identité opération/PIREP, ainsi que la barrière de clôture : Hermès refuse `Complete()` avant `IN` et tant qu'il reste des positions ou événements ACARS non acquittés.

Le vrai test HTTP Laravel du parcours complet nécessitera un environnement de test phpVMS avec base isolée et fixtures. Il ne doit jamais utiliser ou réinitialiser la base historique de production.

## Test manuel de recette

Pour une recette réelle, utiliser un vol catalogue court avec un appareil disponible. Conserver dans le rapport de recette : operation_id, pirep_id, simbrief_id, appareil, départ/arrivée, timestamps OUT/OFF/ON/IN, résultat du Dispatch avant Start et après dépôt.
