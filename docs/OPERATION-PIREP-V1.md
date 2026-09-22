# Contrat PIREP par opération — API v1

Le lot C fait du PIREP un artefact de l'opération Prométhée tout en conservant les tables et services phpVMS existants.

## API

```
GET  /api/v1/operations/{operation_id}/pirep
POST /api/v1/operations/{operation_id}/pirep
```

Le POST est idempotent du point de vue du client : si l'opération possède déjà un PIREP Hermès, Prométhée retourne ce PIREP au lieu d'en créer un second.

## Chaîne de corrélation

```
operation_id
  -> Bid phpVMS
  -> SimBrief exact de l'opération
  -> PirepService::prefile()
  -> PIREP phpVMS
```

Le PIREP est créé avec le `simbrief_id` exact. Le service phpVMS natif attache alors cette ligne SimBrief au PIREP via `simbriefs.pirep_id`.

Le marqueur `source_name = Hermes ACARS [operation_id]` fournit la résolution inverse PIREP -> opération sans nouvelle table ni modification du schéma historique.

Le mode compte SimBrief persiste désormais lui aussi l'OFP généré dans la table SimBrief existante avant le prefile. Les modes API et compte convergent donc sur le même modèle.

## Réponse de création

La réponse contient `operation_id`, `pirep_id`, `simbrief_id` et un bloc `correlation` permettant à Hermès de vérifier explicitement toute la chaîne.

## Compatibilité

Aucune migration BDD. Aucun PIREP historique n'est réécrit. Le workflow natif phpVMS reste responsable des règles de prefile, du dépôt final, de l'acceptation et de la mise à jour de la flotte.

Le lot D pourra maintenant considérer PIREP comme un contrôle serveur du Dispatch.
