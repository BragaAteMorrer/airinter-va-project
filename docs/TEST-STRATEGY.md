# Lot M — Stratégie de tests Prométhée / Hermès

## Barrière CI

Toute pull request vers `master` exécute :

1. hygiène du dépôt ;
2. validation JavaScript Hermès ;
3. build Windows Hermès ;
4. suite xUnit Hermès ;
5. validation Composer et syntaxe des composants Prométhée critiques.

Le rapport xUnit est conservé comme artifact GitHub Actions même en cas d'échec.

## Vol E2E de référence

`ReferenceFlightE2ETests` fige le parcours après Dispatch READY :

`ACARS_READY → BLOCK_OFF → TAXI_OUT → TAKEOFF → CLIMB → CRUISE → DESCENT → APPROACH → TOUCHDOWN → TAXI_IN → BLOCK_ON`.

La fermeture du recorder est testée séparément :

- refus avant `IN` ;
- refus si des positions/événements attendent encore leur ACK ;
- succès uniquement après synchronisation complète.

L'`operation_id` et le `pirep_id` restent des identités distinctes.

## Recorder

Les tests de régression couvrent également :

- conservation de l'identité opérationnelle pendant le vol ;
- création de l'événement OUT au démarrage ;
- ACK ciblé des positions ;
- ACK ciblé des événements sans supprimer les messages non confirmés.

## Moteur de vol

Les tests existants couvrent notamment :

- séquence sol-sol ;
- événements systèmes ;
- slew / sim rate / ajout carburant comme faits et non pénalités ;
- touchdown et rebonds.

## Multi-sim

La détection des familles MSFS, FS2004, FSX, Prepar3D et X-Plane est couverte par la suite .NET. Les tests ne transforment pas une détection de processus en preuve de télémétrie FSUIPC.

## Prométhée

Le CI vérifie explicitement la syntaxe des contrôleurs/services du pipeline Operation API, Telemetry, Safety et Fleet. Les tests fonctionnels Laravel complets restent dépendants de la base de test phpVMS et doivent être exécutés dans l'environnement d'intégration avant une release majeure.

## Règle de release

Une release Hermès/Prométhée ne doit pas contourner un test rouge. Lorsqu'un test révèle un contrat devenu obsolète, le contrat et le test doivent être modifiés explicitement dans la même PR.
