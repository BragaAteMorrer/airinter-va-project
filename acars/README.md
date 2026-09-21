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
au client. L'administrateur doit renseigner `simbrief.api_key`, affecter les
sous-flottes aux vols et aux grades, et définir `simbrief_type` sur l'appareil ou
sa sous-flotte. Les fenêtres contextuelles doivent être autorisées pour Hermès.

Les positions et événements non envoyés sont conservés localement après une coupure. Après un redémarrage, le pilote doit explicitement reprendre le vol : l'application ne rattache jamais silencieusement des données à un ancien PIREP.

## Créer la distribution Windows (.exe)

Depuis PowerShell à la racine du dépôt :

```powershell
.\acars\build-release.ps1 -Version 1.0.0
```

Le script produit `dist\Promethee-ACARS-win-x64-1.0.0.zip`. C'est le fichier à publier : il contient un unique `Promethee.Acars.exe`, autonome (le pilote n'a pas besoin d'installer .NET). Après extraction, il suffit de lancer cet EXE : il ouvre directement une fenêtre Windows native, sans navigateur ni serveur local.

### Prérequis pilote

- Windows 10/11 64 bits et Microsoft Flight Simulator démarré ;
- SimConnect installé avec MSFS/son SDK. Si le DLL n'est pas trouvé automatiquement, définir `PROMETHEE_SIMCONNECT_DLL` vers le `SimConnect.dll` 64 bits avant de lancer l'EXE ;
- accès HTTPS au site phpVMS (HTTP n'est accepté que pour `localhost`).

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
