# Hermès — matrice de certification pilote E2E

Cette matrice complète les tests automatisés de l'étape 8 de l'audit.

## Ce que la CI garantit

`PilotJourneyMatrixE2ETests` injecte un vol bloc-à-bloc à travers la même frontière
`ISimulatorConnector -> SimulatorConnectorHub -> FlightRecorder -> FlightDataMonitor -> FlightReview`
que l'application Hermès.

Les profils suivants doivent tous produire le même contrat opérationnel :

| Simulateur | Liaison | CI |
| --- | --- | --- |
| Microsoft Flight Simulator 2020 | SimConnect | obligatoire |
| Microsoft Flight Simulator 2024 | SimConnect | obligatoire |
| MSFS 2020/2024 | FSUIPC7 de secours | obligatoire |
| X-Plane 11/12 | UDP DataRef | obligatoire |
| Flight Simulator 2004 | FSUIPC | obligatoire |
| Flight Simulator X | FSUIPC | obligatoire |
| Prepar3D | FSUIPC | obligatoire |

Le scénario vérifie notamment :

- détection du connecteur actif ;
- conservation d'un `operation_id` et d'un PIREP uniques ;
- BOARDING -> OUT -> TAXI_OUT -> OFF -> CLIMB -> CRUISE -> DESCENT -> APPROACH -> FINAL ;
- touchdown confirmé avant ON ;
- TAXI_IN puis IN après arrêt parking confirmé ;
- pause simulateur détectée puis terminée ;
- Flight Review prêt au dépôt ;
- landing rate, fuel used et durée de pause conservés ;
- synchronisation obligatoire des positions, événements et faits SOP avant clôture ;
- perte puis récupération du simulateur sans changer d'opération ni de PIREP.

Côté Prométhée, `HermesOperationLifecycleTest::test_15_reference_pilot_journey_crosses_the_full_operation_contract`
valide le parcours HTTP complet :

`operation -> OFP -> PREFILE -> READY -> telemetry -> IN -> FILE -> opération retirée des réservations actives`.

## Ce que la CI ne peut pas prétendre tester

GitHub Actions ne lance pas les exécutables commerciaux des simulateurs. La validation
des pilotes, offsets, DataRefs, droits Windows, FSUIPC installé et comportement réel
du simulateur reste une certification sur poste pilote.

## Checklist de certification réelle

Pour chaque ligne de la matrice :

1. démarrer le simulateur et Hermès sans vol actif ;
2. se connecter via Argos ;
3. sélectionner une réservation existante ;
4. choisir un appareil autorisé par le grade ;
5. récupérer ou importer l'OFP SimBrief ;
6. vérifier O/D, appareil, PAX, niveau de vol et CI ;
7. pré-déposer le PIREP et confirmer que le vol reste READY, jamais Completed ;
8. démarrer l'enregistrement au parking ;
9. vérifier que le bon connecteur apparaît dans Diagnostics ;
10. effectuer OUT, taxi, décollage et montée ;
11. vérifier la carte Live et la progression ;
12. déclencher une pause supportée par le simulateur puis reprendre ;
13. si possible, couper momentanément la liaison simulateur puis la rétablir ;
14. poursuivre jusqu'à l'atterrissage et vérifier que le premier contact ne crée pas ON immédiatement ;
15. rejoindre le parking, frein de parc serré, puis attendre IN ;
16. ouvrir Flight Review et contrôler événements, landing rate, carburant, pauses et éventuelles observations ;
17. synchroniser jusqu'à zéro message en attente ;
18. déposer le PIREP ;
19. confirmer dans Prométhée que le journal, les passagers, le score et la trace sont visibles ;
20. confirmer que l'appareil et le même vol peuvent être réutilisés selon les règles normales après clôture.

## Résultats à consigner

Pour chaque essai réel, noter :

- version Hermès ;
- simulateur + version ;
- connecteur réellement utilisé ;
- appareil/add-on ;
- operation_id ;
- PIREP ;
- OUT/OFF/ON/IN présents ;
- pause détectée ;
- perte/reconnexion testée ou non ;
- dépôt réussi ;
- anomalie + capture/log associé.

Une certification réelle n'est considérée complète que lorsque les sept profils ci-dessus
ont une exécution réussie ou une anomalie explicitement documentée.
