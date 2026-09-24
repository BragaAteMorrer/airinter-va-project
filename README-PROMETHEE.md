# Prométhée local

Ce dépôt contient une extension phpVMS Air Inter nommée Prométhée et un client ACARS personnel.

## Lancer le site

Depuis PowerShell, dans ce dossier :

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\start-promethee.ps1
```

Puis ouvrir :

```text
http://localhost:8088/
http://127.0.0.1:1974
```

Le premier démarrage construit l'image Docker, importe la copie locale de la base et crée un compte administrateur local :

```text
admin@promethee.test / promethee-local
```

## Lancer l'ACARS hors Docker pour MSFS

Le conteneur Docker lance l'interface ACARS. Pour lire directement MSFS via SimConnect, utiliser plutôt le lancement Windows :

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\start-acars.ps1
```

Puis ouvrir :

```text
http://127.0.0.1:1974
```

Le client ACARS lit MSFS via SimConnect. Si `SimConnect.dll` n'est pas trouvé automatiquement, définir son chemin avant le lancement :

```powershell
$env:PROMETHEE_SIMCONNECT_DLL = 'C:\chemin\vers\SimConnect.dll'
powershell -NoProfile -ExecutionPolicy Bypass -File .\start-acars.ps1
```

## Fonctionnalités ajoutées

- Portail Prométhée avec thèmes moderne, Minitel et années 2000.
- Tableau de situation Prométhée orienté dispatch.
- Salle opérations avec file départs, derniers rapports, routes actives et rendez-vous.
- Calendrier interne.
- Liste pilotes actifs, anciens et retraités.
- Outil économie pour ajuster carburant et billets par périmètre.
- Grille tarifaire bleu/blanc/rouge configurable.
- Bulletin sécurité mensuel anonymisé à partir des PIREPs et de la télémétrie.
- API télémétrie Prométhée.
- SOP Engine Air Inter : faits FDM Hermès, règles compagnie administrables, revue pilote et alertes Dispatch sous `/admin/promethee/sop`.
- ACARS Windows local avec tampon disque et dépôt de PIREP.
- Espace administration protégé sous `/admin/promethee` (pilotage, règles de progression, tarifs, réseau et messagerie).
- Recalcul badges/grades : `php artisan promethee:progression-recalculate`, exécuté chaque heure par le scheduler Laravel.
- Migration `2026_09_16_000005_add_admin_automation` : règles de badges, règles de grades et historique de progression.

## Note historique

Le nom Prométhée est conservé comme nom de projet. Les recherches documentaires disponibles ici ont confirmé des systèmes Air Inter nommés Sirène puis Antarès, mais pas Prométhée comme nom historique officiel.
