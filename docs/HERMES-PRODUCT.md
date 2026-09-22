# Lot L — Hermès comme produit distribuable

## Résilience locale

Hermès conserve déjà l'état du vol, les positions et événements en attente dans `%LOCALAPPDATA%\AirInter\Promethee`. Après un redémarrage, un vol récupéré reste en pause et doit être repris explicitement : aucune télémétrie n'est silencieusement rattachée à un ancien PIREP.

Le Lot L ajoute un journal minimal de crash dans `%LOCALAPPDATA%\AirInter\Hermes\last-crash.json`. Il contient la version, le type d'exception, le message et la stack trace. Il ne contient ni mot de passe ni Bearer token.

## Diagnostic pilote

Hermès peut exporter un diagnostic JSON local contenant :

- version Hermès ;
- Windows / runtime / architecture ;
- origine du serveur, sans chemin, query string ni credentials ;
- état de connexion API ;
- état simulateur ;
- phase de vol ;
- nombre de messages en attente ;
- présence d'une session récupérable ;
- warning courant du recorder.

Les diagnostics sont écrits dans `%LOCALAPPDATA%\AirInter\Hermes\diagnostics`.

## Updater

Une installation automatique exige désormais :

1. une URL HTTPS ;
2. un SHA-256 publié par Prométhée ;
3. un téléchargement dont le hash correspond exactement.

Une release sans checksum peut être signalée à l'utilisateur mais n'est pas installable automatiquement.

## Packaging

`build-installer.ps1` produit toujours le Setup Inno Setup et son SHA-256.

Pour une release publique signée, fournir :

- `HERMES_SIGNING_CERTIFICATE` : chemin du PFX ;
- `HERMES_SIGNING_CERTIFICATE_PASSWORD` : mot de passe du PFX si nécessaire.

Le script signe alors le Setup avec SHA-256, demande un timestamp et vérifie Authenticode avant de générer le checksum. Sans certificat, le build reste possible mais émet explicitement un warning « NON SIGNE ».

Aucun certificat ou secret de signature ne doit être commité dans le dépôt.

## Critères de release publique

Avant de présenter une version comme stable :

- tests .NET verts ;
- installateur construit depuis un tag ;
- checksum publié ;
- signature Authenticode valide pour une distribution publique ;
- test installation / désinstallation Windows 10 et 11 ;
- test reprise après fermeture forcée pendant un vol ;
- test coupure réseau puis resynchronisation ;
- validation réelle des connecteurs simulateur annoncés comme supportés.
