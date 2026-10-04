# Hermès EFB — Microsoft Flight Simulator 2024

Cette application ajoute **Air Inter Hermès** au système EFB natif de Microsoft Flight Simulator 2024.

Elle est volontairement une **surface de consultation**. Hermès desktop reste l'autorité pour l'authentification Argos, l'opération, l'enregistrement ACARS, le pré-dépôt et le dépôt du PIREP.

## Architecture

```
EFB MSFS 2024
  AIRINTER_HERMES_EFB_REQUEST
             │
             ▼
        CommBus MSFS
             │
             ▼
SimConnectReader ── HermesEfbBridge ── FlightRecorder / Telemetry
             │
             ▼
  AIRINTER_HERMES_EFB_STATE
             │
             ▼
       EFB Air Inter
```

Le protocole est en lecture seule :

- `AIRINTER_HERMES_EFB_REQUEST` : `state` ou `ping` ;
- `AIRINTER_HERMES_EFB_STATE` : état opérationnel JSON ;
- version de protocole : `1`.

Aucun token Argos, cookie, mot de passe ou action de dépôt PIREP n'est envoyé à l'EFB.

## Données affichées

- vol et route ;
- appareil / immatriculation ;
- statut Dispatch ;
- phase ACARS ;
- altitude, vitesse et carburant ;
- PAX ;
- niveau de vol ;
- Cost Index ;
- messages de pause / recovery / synchronisation ;
- disponibilité du dépôt final.

## Prérequis SDK

Le dossier `efb_api` est fourni par le SDK MSFS 2024 et **n'est pas versionné dans ce dépôt**.

À partir du sample EFB officiel du SDK :

1. copier `PackageSources/efb_api` dans `acars/msfs2024-efb/efb_api` ;
2. exécuter `npm install` dans ce dossier, comme demandé par le SDK ;
3. exécuter `npm install` dans `HermesEfb` ;
4. copier `.env.example` vers `.env` si nécessaire ;
5. lancer `npm run typecheck`, puis `npm run build`.

Le résultat est produit dans :

`acars/msfs2024-efb/HermesEfb/dist`

## Packaging MSFS 2024

Le SDK officiel utilise un **Copy asset group** pour les apps EFB.

Utiliser le projet EFB Template fourni par le SDK, puis copier le contenu de `dist` vers le dossier source qui sera monté sous :

`html_ui/efb_ui/efb_apps/AirInterHermes/`

Le `BASE_URL` du build correspond déjà à ce chemin VFS.

Le SDK / DevMode doit générer le package final (`layout.json`, `manifest.json`) : ces fichiers ne doivent pas être fabriqués à la main dans le dépôt Hermès.

## Compatibilité

- **MSFS 2024 + SimConnect** : EFB natif via CommBus.
- **MSFS 2020** : Hermès continue à fonctionner normalement ; l'absence des exports CommBus est traitée comme une capacité non disponible.
- **FSUIPC / X-Plane / FSX / P3D / FS2004** : aucun impact, l'EFB MSFS 2024 n'est simplement pas exposé.

## Développement

`npm run watch` garde esbuild actif pour la boucle DevMode.

Pour tester le transport :

1. lancer Hermès ;
2. lancer MSFS 2024 ;
3. vérifier dans Diagnostics Hermès que `efb.commBusAvailable` vaut `true` ;
4. ouvrir **Air Inter Hermès** dans l'EFB ;
5. sélectionner/préparer une opération dans Hermès ;
6. vérifier que vol, PAX, FL et CI sont répliqués ;
7. démarrer le tracking et contrôler la mise à jour de la phase et de la télémétrie.

L'EFB doit rester utilisable si Prométhée est temporairement hors ligne : il affiche alors le dernier contexte local connu par Hermès et l'état de synchronisation.
