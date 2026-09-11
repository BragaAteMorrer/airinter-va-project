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

Les positions et événements non envoyés sont conservés localement après une coupure. Après un redémarrage, le pilote doit explicitement reprendre le vol : l'application ne rattache jamais silencieusement des données à un ancien PIREP.
