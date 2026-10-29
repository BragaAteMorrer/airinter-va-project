# Hermès — frein de parc et cales Fenix A32X V3

## Principe

Le frein de parc **physique** et les cales de roues sont deux observations distinctes.
Hermès ne modifie jamais les variables du simulateur pour contourner les
contrôles. Sur Fenix A319/A320/A321 uniquement, l'état `StandSecured`
est vrai si au moins une observation vaut vrai :

- `BRAKE PARKING POSITION` (SimConnect) ou offset FSUIPC `0x0BC8` :
  frein standard ;
- `L:S_MIP_PARKING_BRAKE` : levier de frein Fenix ;
- `L:B_CONFIG_CHOCKS` : cales de roues Fenix.

Pour les autres avions, seul le frein de parc standard est accepté.
Les valeurs absentes sont `null`, jamais considérées comme `true`.

## Transport des variables

- **SimConnect MSFS 2020 SU12+ / MSFS 2024** : lecture native en
  lecture seule, sur la connexion déjà ouverte, une fois par seconde.
  Les deux LVars sont demandées séparément des variables de vol afin
  qu'un échec ne coupe pas la télémétrie. Les valeurs de plus de 5 s
  ne sont pas utilisées et sont réinitialisées au changement d'avion.
- **FSUIPC7 en secours** : lecture des LVars via `MSFSVariableServices`
  (nécessite le WASM FSUIPC et la bibliothèque native `FSUIPC_WAPID.dll`
  accessible depuis l'application). Pour éviter d'activer cette liaison
  native sur tous les simulateurs, elle est **désactivée par défaut**.
  Définir `HERMES_FENIX_FSUIPC_LVARS=1` avant de lancer Hermès pour
  tester ce mode. En cas d'échec, les champs restent indisponibles et
  le frein standard continue à fonctionner.
- Ne pas forcer SimConnect et FSUIPC à être tous deux actifs pour
  collecter la même télémétrie. La connexion principale reste celle
  choisie par `SimulatorConnectorHub`.

## Règles de sécurité

- **START** : avion au sol, stationnement confirmé, vitesse sol
  strictement inférieure à 2 kt et moteurs arrêtés (si leurs états
  sont remontés). Une valeur `null` de stationnement bloque START.
- **IN** : après `TAXI_IN`, au sol, vitesse sol strictement inférieure
  à 2 kt, stationnement confirmé et stable au moins 15 secondes.
- **OUT** : ne déclenche pas OUT à basse vitesse si le stationnement
  est toujours confirmé. La transition de roulage à 5 kt garde
  l'ancien mécanisme de récupération d'un OUT manquant.
- **Ne jamais** enregistrer une cale comme frein de parc serré.
  Le statut brut `ParkingBrake` conserve sa valeur réelle.

## Procédure de validation sur Fenix V3

1. Noter le connecteur actif dans les diagnostics Hermès.
2. À l'arrêt, moteurs éteints et cales retirées, serrer puis desserrer
   le frein. Vérifier dans le prévol que les deux lectures sont cohérentes
   ou identifier la divergence avec le frein standard.
3. Frein desserré, poser les cales via l'EFB Fenix : le stationnement
   doit être validé **si** la variable `B_CONFIG_CHOCKS` est bien
   remontée par la version installée.
4. Retirer les cales, relâcher le frein, rouler : un unique OUT doit
   être enregistré.
5. À destination, finir le roulage, poser les cales ou serrer le frein ;
   l'événement IN ne doit apparaître qu'après 15 secondes à moins de 2 kt.
6. Refaire le test sur un avion générique pour vérifier que des
   données Fenix ne valident jamais son stationnement.

Le bon fonctionnement des LVars avec la **V3** et la fraîcheur des
données FSUIPC/WASM restent à valider sur une machine Windows avec MSFS.
La compilation/test automatisé ne remplace pas ce contrôle réel.
