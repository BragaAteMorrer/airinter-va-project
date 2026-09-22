# Hermès — contrat API v1

Le parcours opérationnel Hermès utilise exclusivement le contrat Prométhée `/api/v1`.

## Surface consommée

```
GET  /api/v1/me
GET  /api/v1/operations
GET  /api/v1/operations/{operation_id}
GET  /api/v1/operations/{operation_id}/aircraft-eligibility
GET  /api/v1/operations/{operation_id}/briefing
GET  /api/v1/operations/{operation_id}/readiness
GET  /api/v1/operations/{operation_id}/dispatch
GET  /api/v1/operations/{operation_id}/pirep
POST /api/v1/operations/{operation_id}/pirep
POST /api/v1/operations/{operation_id}/telemetry
POST /api/v1/operations/{operation_id}/simbrief/session
POST /api/v1/operations/{operation_id}/simbrief/redirect
POST /api/v1/operations/{operation_id}/simbrief/account/import
POST /api/v1/operations/{operation_id}/simbrief/import
GET  /api/v1/hermes/configuration
GET  /api/v1/hermes/releases/latest
```

L'authentification initiale reste le mécanisme de session ACARS existant jusqu'au futur lot OAuth/PKCE. Après authentification, l'identité pilote est relue via `/api/v1/me`.

## Règle de dépendance

Le WebDesktop n'implémente plus de traduction :

```
/api/operations/* -> /api/promethee/acars/*
/api/flights/*    -> phpVMS flights/*
/api/prefile      -> phpVMS pireps/prefile
```

Les requêtes `/api/v1/*` de l'interface embarquée sont relayées telles quelles au serveur Prométhée authentifié.

La recherche libre du programme reste une fonction séparée de découverte phpVMS pour l'instant ; elle ne participe pas au parcours d'une opération réservée et fera l'objet d'une façade v1 dédiée si elle doit devenir un contrat Hermès permanent.

## Legacy

Les routes serveur `/api/promethee/acars/*` restent disponibles pour les anciens clients pendant la transition, mais elles sont officiellement deprecated. Aucune nouvelle fonctionnalité ne doit y être ajoutée.

## Invariant

Pour préparer et exécuter un vol, Hermès doit disposer d'un `operation_id`. Il n'existe plus de fallback silencieux par `bid_id`, `flight_id` ou PIREP générique dans le parcours opérationnel.
