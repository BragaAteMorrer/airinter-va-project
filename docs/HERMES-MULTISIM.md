# Lot K — Hermès multi-simulateur

## Principe

Le cœur Hermès ne dépend d'aucun simulateur. Il consomme uniquement `ISimulatorConnector` et `AircraftSnapshot`.

Le support est annoncé à deux niveaux distincts :

1. **détection / frontière de connecteur** ;
2. **télémétrie réellement implémentée et validée**.

Hermès ne doit jamais afficher « supporté » simplement parce qu'un processus est détecté.

## Matrice

| Simulateur | Transport | État Lot K |
| --- | --- | --- |
| MSFS 2020 | SimConnect | télémétrie implémentée ; validation réelle requise |
| MSFS 2024 | SimConnect | télémétrie implémentée ; validation réelle requise |
| FS2004 | FSUIPC3 | détection et frontière ; lecteur télémétrie non embarqué |
| FSX / Steam | FSUIPC4 | détection et frontière ; lecteur télémétrie non embarqué |
| Prepar3D | FSUIPC selon version | détection et frontière ; lecteur télémétrie non embarqué |
| X-Plane 11/12 | UDP DataRef RREF localhost | télémétrie expérimentale |

## Hub

`SimulatorConnectorHub` interroge tous les connecteurs et ne choisit comme actif qu'un connecteur ayant effectivement produit un snapshot récent.

Une session devient inactive si :

- le connecteur n'est plus `Connected` ;
- son dernier snapshot date de plus de 15 secondes.

Hermès peut alors basculer vers un autre connecteur sans redémarrage.

Le hub expose aussi un diagnostic par connecteur : famille, état, capacités annoncées, message, dernier snapshot et connecteur actif.

## Legacy Microsoft

FS2004, FSX et Prepar3D sont volontairement conservés derrière une frontière FSUIPC commune. Le Lot K ne copie ni DLL ni SDK FSUIPC dans Hermès et ne prétend pas disposer de télémétrie tant qu'un lecteur compatible et redistribuable n'a pas été validé.

Les processus `fs9`, `fsx` / `fsx_se` et plusieurs noms Prepar3D sont reconnus.

## X-Plane

Le connecteur utilise le protocole RREF UDP local sur `127.0.0.1:49000`. Il est read-only du point de vue Hermès et reste marqué expérimental tant que les vols réels XP11/XP12 ne sont pas validés.

## Règle de données

Une valeur indisponible reste `null`. Les connecteurs ne doivent pas inventer un zéro ou `false` pour satisfaire le moteur de vol.
