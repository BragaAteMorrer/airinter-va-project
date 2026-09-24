# Prométhée ACARS

Client local MSFS connecté à phpVMS 7 via SimConnect. Il reproduit le flux opérationnel utile d'un ACARS de compagnie : message **OUT** au départ du poste, **OFF** au décollage, **ON** à l'atterrissage, **IN** au parking, ainsi que des positions périodiques.

## Déploiement phpVMS

Le dépôt contient le support de connexion directe dans phpVMS. Déployer le code PHP puis exécuter une fois :

```bash
cd prometheus
php artisan migrate --force
```

Cette migration ajoute `acars_access_tokens`. Un mot de passe n'est jamais enregistré : la connexion crée un jeton Bearer aléatoire, stocké côté serveur uniquement sous forme de condensat SHA-256 et valable 12 heures. L'ancienne clé API reste disponible pour les installations qui n'ont pas encore appliqué la migration.

En environnement de développement local :

```bash
docker compose -f compose.promethee.yml up --build -d
```

## Utilisation pilote

1. Démarrer MSFS puis l'ACARS (`start-acars.ps1`).
2. Se connecter avec l'identifiant pilote (ou l'e-mail) et le mot de passe phpVMS. L'URL doit être en HTTPS, sauf `localhost`.
3. Pré-déposer le PIREP et démarrer l'enregistrement au sol avant de quitter le poste.
4. Laisser l'ACARS déclencher OUT/OFF/ON/IN. IN est validé après 15 secondes au parking, frein de parc serré et vitesse sol inférieure à 2 kt.
5. Synchroniser, puis déposer le PIREP après IN.

### Créer un OFP SimBrief depuis Hermès

Après avoir choisi le vol et un appareil autorisé, cliquer sur **Créer l’OFP
dans SimBrief**. Hermès transmet à SimBrief le vol, la route et l'appareil reçus
de Prométhée, puis ouvre la fenêtre de connexion SimBrief du pilote. Une fois
l'OFP généré, fermer cette fenêtre : Hermès importe automatiquement le briefing
dans Prométhée et inclut son `simbrief_id` lors du pré-dépôt du PIREP.

La clé API SimBrief de la compagnie reste sur Prométhée et n'est jamais envoyée
au client. Sur une installation déjà existante, après déploiement de cette version, lancer
une fois **Administration → Maintenance → Reseed** pour synchroniser les
métadonnées du réglage (la valeur existante est conservée). Puis, dans
**Administration → Settings → simbrief**, renseigner **SimBrief Company API
Key** et enregistrer. Le champ est write-only : une clé déjà configurée n'est
jamais réaffichée ; laisser le champ vide la conserve et sa suppression exige
de cocher explicitement **Remove the stored credential**.

Hermès reçoit uniquement `company_api_available: true/false` afin d'activer ou
désactiver le mode **API SimBrief**. La valeur de la clé n'est jamais incluse
dans les réponses API, les diagnostics Hermès ou les logs d'activité. Il faut
également affecter les sous-flottes aux vols et aux grades, et définir
`simbrief_type` sur l'appareil ou sa sous-flotte.

Les positions et événements non envoyés sont conservés localement après une coupure. Après un redémarrage, le pilote doit explicitement reprendre le vol : l'application ne rattache jamais silencieusement des données à un ancien PIREP.

### Hermès Datalink

Hermès dispose d'un transport opérationnel bidirectionnel lié à une
`operation_id` Prométhée.

Le contrat v1 distingue les catégories `OPS`, `DISPATCH`, `WEATHER`,
`SYSTEM` et `CREW`, ainsi que les priorités `NORMAL`, `HIGH` et
`URGENT`. Un message peut exiger un accusé de réception (`requires_ack`)
et répondre à un message précédent via `reply_to`.

Le client est **local-first** :

- un message cockpit est écrit dans `datalink.json` avant tout appel réseau ;
- chaque envoi possède un `client_message_id` UUID afin que les retries
  serveur soient idempotents ;
- les ACK sont eux aussi mis en file locale ;
- une coupure Prométhée laisse le vol et la messagerie utilisables ;
- Hermès retente automatiquement la synchronisation pendant un vol actif et
  l'interface Datalink peut la déclencher manuellement.

Prométhée utilise pour ce lot un stockage JSON verrouillé dans
`storage/app/promethee/datalink`. Aucun schéma phpVMS n'est modifié. Ce
stockage constitue le transport v1 ; le futur Dispatcher Desk consommera le
même service et pourra remplacer la persistance sans casser le contrat Hermès.

Les routes pilote sont sous
`/api/v1/operations/{operation}/datalink`. Une surface admin protégée sous
`/admin/promethee/datalink/messages` permet déjà au futur Dispatcher
d'injecter, lire et acquitter les messages sans exposer cette capacité aux
pilotes.

### Aircraft capabilities et adapters

Hermès maintient désormais un profil de capacités **par avion et par
connecteur**. Chaque donnée est classée dans un des trois états suivants :

- `SUPPORTED` : la donnée a réellement été observée au moins une fois pour
  l'avion courant ;
- `UNKNOWN` : le connecteur sait exposer cette famille de données, mais
  Hermès ne l'a pas encore reçue pour cet avion ;
- `UNSUPPORTED` : le connecteur actif n'expose explicitement pas cette
  famille de données.

Un changement d'identité avion réinitialise les observations afin qu'une
capacité vue sur un appareil ne soit jamais transférée au suivant.

Le registre d'adapters contient un fallback `generic` et reconnaît
actuellement les identités Fenix A320, PMDG, Flight Sim Labs et TFDi MD-11.
Cette reconnaissance **n'est pas une promesse de support** : les capacités
restent déterminées par la télémétrie réellement reçue. Les adapters sont
cependant placés dans le chemin de normalisation du hub afin de pouvoir
ajouter ultérieurement des intégrations vendor-specific sans modifier le
recorder.

Le recorder accepte maintenant un vol même lorsque certaines données systèmes
optionnelles (train, volets, frein de parc, bank) sont absentes, à condition
que les données minimales de navigation nécessaires au suivi soient présentes.
Les valeurs absentes restent `null` dans la télémétrie détaillée et ne
déclenchent pas de faux événements FDM.

### Flight Data Monitoring et Flight Review

Hermès collecte désormais des **observations factuelles** séparées des règles
compagnie et des pénalités. Le FDM local enregistre notamment :

- passage des gates **1000 ft** et **500 ft AGL** en approche avec un statut
  `STABLE`, `UNSTABLE` ou `UNKNOWN` selon les données réellement
  disponibles ;
- excursions de bank supérieures à 35° avec le pic observé ;
- ajout de carburant, utilisation du slew et augmentation du sim-rate ;
- remises de gaz, touch-and-go, touchdown confirmé et nombre de rebonds.

Les gates d'approche n'utilisent que des critères génériques disponibles
(train, volets, vitesse verticale et bank). Hermès n'invente jamais une VAPP
ou une configuration avion qu'il ne connaît pas.

Le **Flight Review** est accessible pendant le vol et devient prêt au dépôt
après `IN`. Il récapitule distance, temps airborne/block, carburant,
landing rate, gates 1000/500, FDM et anomalies compagnie avant l'envoi final
du PIREP.

Ces observations sont persistées dans l'état local et survivent à une reprise
après crash.

### SOP Engine Air Inter

Les observations FDM sont également mises en file locale avec un `fact_id`
UUID puis transmises à Prométhée via
`/api/v1/operations/{operation}/sop/facts`. Elles ne quittent la file Hermès
qu'après une réponse serveur réussie, de sorte qu'une coupure réseau n'efface
pas les faits opérationnels.

La séparation des responsabilités est volontaire :

- **Hermès** mesure et décrit les faits disponibles ;
- **Prométhée** applique les règles compagnie ;
- une donnée inconnue n'est jamais transformée en infraction ;
- aucune pénalité ni aucun score n'est calculé dans Hermès.

Les règles SOP sont administrables sous `/admin/promethee/sop`. Elles peuvent
filtrer un fact code par opérateur numérique et par phase, choisir une sévérité
`INFO`, `ADVISORY` ou `WARNING`, demander une revue pilote et/ou produire
une alerte Dispatch. Changer par exemple la limite de roulage ne nécessite donc
aucune nouvelle version d'Hermès : le client transmet le fait brut
`TAXI_SPEED_MAX` et Prométhée décide du seuil applicable.

### Moteur de phases Hermès

`FlightTrackingEngine` est l'unique source de vérité des phases de vol :

`BOARDING → PUSHBACK → TAXI_OUT → TAKEOFF → CLIMB → CRUISE → DESCENT → APPROACH → FINAL → LANDING → TAXI_IN → IN`.

Les événements ACARS canoniques sont distincts de ces phases :

- **OUT** : départ réel du poste, pas au clic sur Start Flight ;
- **OFF** : transition sol → air au décollage ;
- **ON** : touchdown confirmé après stabilisation, afin d'absorber les rebonds ;
- **IN** : avion immobilisé au parking, frein de parc serré pendant 15 secondes.

Une remise de gaz avant contact produit `GO_AROUND`. Un contact suivi d'un
redécollage produit `TOUCH_AND_GO` et ne produit pas de faux `ON`. Lors
d'une reprise après crash, la phase sauvegardée est restaurée au lieu de
réinitialiser le vol à `ACARS_READY`.

## Hermès Datalink v2

Le Datalink est un transport opérationnel lié à une opération Air Inter. Hermès
conserve localement les messages sortants, reçus de lecture et ACK tant que
Prométhée n'a pas confirmé leur traitement.

Catégories : `OPS`, `DISPATCH`, `WEATHER`, `SYSTEM`, `CREW`.

Priorités canoniques :

- `ROUTINE`
- `ADVISORY`
- `IMPORTANT`
- `URGENT`

Les anciennes valeurs `NORMAL` et `HIGH` restent acceptées pendant la
transition et sont normalisées respectivement vers `ROUTINE` et
`IMPORTANT`.

Cycle serveur d'un message :

```
QUEUED (local Hermès)
  -> SENT
  -> DELIVERED
  -> READ
  -> ACKNOWLEDGED   (uniquement lorsqu'un ACK est demandé)
```

`DELIVERED` signifie que le destinataire a récupéré le message depuis
Prométhée. `READ` est un reçu explicite distinct de l'ACK. Un ACK implique
également la lecture côté serveur.

## Créer la distribution Windows (.exe)

Depuis PowerShell à la racine du dépôt :

```powershell
.\acars\build-release.ps1 -Version 1.0.0
```

Le script produit `dist\Promethee-ACARS-win-x64-1.0.0.zip`. C'est le fichier à publier : il contient un unique `Promethee.Acars.exe`, autonome (le pilote n'a pas besoin d'installer .NET). Après extraction, il suffit de lancer cet EXE : il ouvre directement une fenêtre Windows native, sans navigateur ni serveur local.

### Prérequis pilote

- Windows 10/11 64 bits ;
- **MSFS 2020/2024** : SimConnect installé avec MSFS/son SDK. Si la DLL n'est pas trouvée automatiquement, définir `PROMETHEE_SIMCONNECT_DLL` vers le `SimConnect.dll` 64 bits avant de lancer l'EXE ;
- **FS2004** : FSUIPC3 installé (branche historique 3.999z9) ;
- **FSX / FSX Steam / Prepar3D 1–3** : FSUIPC4 installé ;
- **Prepar3D 4–6** : FSUIPC6 installé ;
- accès HTTPS au site phpVMS (HTTP n'est accepté que pour `localhost`).

Hermès embarque uniquement le client .NET `FSUIPCClientDLL` nécessaire pour
ouvrir l'interface IPC. Il ne redistribue pas FSUIPC. L'accès IPC utilisé par
les applications tierces ne nécessite pas la licence payante des fonctions
avancées FSUIPC. Le support FS2004/FSX/P3D est considéré **implémenté mais non
validé en vol réel** tant que la matrice de tests matériels n'est pas terminée.

Ne pas ajouter le cache local au ZIP : les sessions et positions en attente sont enregistrées séparément dans `%LOCALAPPDATA%\AirInter\Promethee` sur chaque poste.

### Installateur Windows

Pour produire un `Setup.exe` avec raccourcis Bureau et menu Démarrer, installer une fois [Inno Setup 6](https://jrsoftware.org/isdl.php), puis lancer :

```powershell
.\acars\build-installer.ps1 -Version 1.0.0
```

Le setup est créé dans `dist\Promethee-ACARS-Setup-1.0.0.exe`.

### Verrouiller le serveur phpVMS (administrateur)

Avant de distribuer le client, l'administrateur Windows configure l'unique serveur autorisé depuis une console PowerShell **ouverte en administrateur** :

```powershell
.\acars\set-server.ps1 -Server 'https://va.exemple.fr'
```

L'adresse est enregistrée dans `HKLM\SOFTWARE\AirInter\PrometheeACARS`. Les pilotes peuvent la consulter mais ne peuvent pas la modifier dans l'ACARS.


## Code signing policy

Hermès is distributed from the public Air Inter VA source repository. Official Windows releases are built from this repository through the project's automated release workflow.

Free code signing is intended to be provided by SignPath.io, certificate by SignPath Foundation, subject to project acceptance by SignPath Foundation.

- Committers, reviewers and release approvers: the maintainers of `BragaAteMorrer/airinter-va-project`.
- Signing requests must correspond to an official Hermès release built from the public source repository.
- Release binaries must not be modified after signing.
- Every published installer is accompanied by a SHA-256 checksum.
- Signing credentials and private keys must never be committed to this repository.

### Privacy

Hermès communicates with the Prométhée/phpVMS server explicitly configured for the virtual airline and with services explicitly requested by the pilot as part of the flight workflow (for example SimBrief). Authentication secrets are not included in diagnostic exports. Flight state and unsent telemetry are persisted locally for recovery and synchronization.

