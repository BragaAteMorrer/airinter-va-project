# Distribution Hermès

Hermès est distribué aux pilotes comme un installateur Windows x64. Aucun Git, WSL ou SDK .NET n'est nécessaire.

## Release
Créer un tag `hermes-vX.Y.Z`. GitHub Actions construit le client self-contained, l'installateur Inno Setup et son SHA-256, puis attache l'installateur à une GitHub Release.

## Prométhée
La page Téléchargements doit référencer la release stable. L'administration Hermès conserve version, URL, SHA-256, notes, version minimale et indicateur obligatoire.

Le contrat cible est `GET /api/v1/hermes/releases/latest`, avec `version`, `download_url`, `sha256` et `mandatory`.

Hermès propose les mises à jour normales. Une mise à jour obligatoire peut empêcher le démarrage d'un nouveau vol, mais ne doit jamais interrompre un vol en cours.

Aucun code arbitraire ne doit être envoyé par Prométhée. Les mises à jour sont des paquets versionnés, contrôlés par checksum, puis à terme signés Authenticode.
