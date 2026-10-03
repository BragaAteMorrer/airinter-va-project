# Hermès ACARS : audit fonctionnel et plan d'intégration

> Cet audit ciblé doit être lu avec l'[audit de l'écosystème](AUDIT-ECOSYSTEME.md),
> qui inventorie le socle phpVMS/Prometheus, les extensions Prométhée et Hermès.
> Il ne propose pas de reconstruire les produits : toute évolution suit l'ordre
> **existant → audit → conservation → amélioration → extension**.

## 1. Positionnement recommandé

Hermès ne doit pas devenir un second site phpVMS. Il doit être le **poste de
travail opérationnel du pilote** entre Prométhée (source de vérité) et le
simulateur :

- **Prométhée** conserve les pilotes, grades, réservations, rotations, flotte,
  PIREP et OFP ;
- **Hermès** orchestre une mission, explique les blocages, met en cache les
  données utiles et enregistre la télémétrie ;
- **SimBrief** calcule le plan de vol, mais Prométhée reste le mandataire qui
  importe, valide et rattache l'OFP au pilote, au vol et à l'appareil ;
- **MSFS/SimConnect** fournit l'état réel du vol.

Le parcours cible est une machine à états unique : `réservation -> appareil ->
OFP -> PIREP pré-déposé -> READY -> OUT -> OFF -> ON -> IN -> synchronisé ->
déposé`. Chaque transition doit être idempotente, persistée localement et
auditable.

## 2. Diagnostic du blocage actuel

Le message « Impossible de récupérer les avions autorisés pour ce vol » ne
permet pas de conclure que la flotte est vide : Hermès transforme aujourd'hui
toute réponse phpVMS non-2xx en une erreur générique. Le proxy local appelle bien
`GET /api/flights/{id}/aircraft`, route présente et authentifiée côté phpVMS.

Le serveur ne retourne que l'intersection des appareils qui satisfont **toutes**
les conditions suivantes :

1. sous-flotte autorisée par le grade/type rating du pilote ;
2. sous-flotte affectée au vol, si le vol en impose une ;
3. appareil `PARKED` et `ACTIVE` ;
4. appareil au terrain de départ si l'option correspondante est active ;
5. appareil sans OFP actif si `simbrief.block_aircraft` est actif ;
6. appareil sans réservation concurrente si `bids.block_aircraft` est actif.

### Causes probables, dans l'ordre de vérification

1. **Erreur HTTP masquée** (401/403/404/500), impossible à distinguer d'une
   liste vide dans l'interface actuelle.
2. **Aucune sous-flotte commune** entre le grade du pilote et les sous-flottes
   des vols ITF749/ITF49.
3. **Appareils non disponibles** : statut, état, position LFPO ou verrou d'une
   autre réservation/OFP.
4. **Données SimBrief incomplètes** : `aircraft.simbrief_type`, puis
   `subfleet.simbrief_type`, puis l'ICAO sont utilisés comme repli. Le texte
   « type non renseigné » indique donc aussi que le contrat de données exposé
   à Hermès ne résout pas correctement ce champ.
5. **Identifiant de vol mal extrait d'une réservation** : l'API des bids enveloppe
   le vol sous `bid.flight`; Hermès doit appeler l'endpoint appareils avec
   `bid.flight.id`, et non l'identifiant du bid.

### Procédure de preuve minimale

Depuis une session du pilote concerné, journaliser sans secret : URL logique,
correlation ID, statut HTTP, code d'erreur métier et durée. Comparer ensuite :

```text
GET /api/user/bids
GET /api/flights/{flight_id}
GET /api/flights/{flight_id}/aircraft
```

Dans la base, contrôler l'affectation `flight_subfleet`, `subfleet_rank`, le
grade du pilote, puis `aircraft.status`, `aircraft.state`, `aircraft.airport_id`,
les bids concurrents et les lignes SimBrief sans `pirep_id`. Le serveur doit
retourner `200 []` pour « aucun avion éligible » et un objet d'erreur stable pour
un incident ; l'interface ne doit jamais confondre les deux.

## 3. Ce qu'il faut ajouter à Hermès

### Priorité P0 — rendre le parcours exploitable

- Un endpoint Prométhée d'agrégation, par exemple
  `GET /api/acars/operations`, qui fournit des DTO stables : réservation, vol,
  appareils éligibles, OFP et PIREP, sans obliger le client à connaître les
  relations internes de phpVMS.
- Un résultat d'éligibilité explicatif par appareil : `eligible` et des
  `reason_codes` (`RANK_NOT_ALLOWED`, `WRONG_AIRPORT`, `ALREADY_BID`,
  `ACTIVE_OFP`, `NOT_PARKED`, etc.).
- Des erreurs RFC 9457/Problem Details avec `code`, `title`, `detail`,
  `correlation_id` et `retryable`. Le client peut alors proposer « se
  reconnecter », « actualiser » ou « contacter les opérations » au lieu d'un
  message unique.
- Un bouton **Diagnostic** copiable, expurgé des jetons, et une page de
  prérequis qui teste API, pilote, grade, sous-flotte, appareil, SimBrief et
  SimConnect.
- Des opérations idempotentes (`Idempotency-Key`) pour la sélection d'appareil,
  le pré-dépôt et le dépôt final.

### Priorité P1 — faire d'Hermès le centre des opérations

- Briefing consolidé : météo, NOTAM, OFP, carburant, masse, route, alternates,
  restrictions compagnie et statut réseau.
- Assistant avant-vol avec feux rouge/orange/vert et validation croisée du type
  SimBrief avec l'appareil détecté dans MSFS.
- Messagerie dispatch/ACARS liée au vol, accusés de réception et conservation
  serveur.
- Suivi temps réel visible sur le site, avec dernier contact, phase, ETA,
  déroutement et niveau de qualité de la télémétrie.
- Mode hors ligne : cache chiffré de la mission active, file de sortie durable,
  rejeu ordonné et déduplication serveur.
- Rapport après-vol lisible avant envoi : chronologie, anomalies, consommation,
  landing rate, commentaires et pièces jointes.

### Priorité P2 — qualité et gouvernance

- Contrat OpenAPI versionné, tests de contrat Prométhée/Hermès et jeux de
  données couvrant tous les motifs d'inéligibilité.
- Observabilité : journaux structurés, correlation ID de bout en bout, métriques
  de synchronisation et tableau de santé administrateur.
- Mise à jour signée de l'ACARS, version minimale imposable et canal stable/bêta.
- Permissions dédiées et audit des actions sensibles. En particulier, l'accès
  à un briefing vérifie désormais qu'il appartient au pilote authentifié ; ce
  contrôle doit rester couvert par un test de non-divulgation inter-pilotes.

## 4. Intégration SimBrief recommandée

Il faut **réutiliser l'intégration serveur phpVMS existante**, et ne pas faire
appeler SimBrief directement par le client Windows. Cela garde la clé API hors
du binaire, centralise les règles de flotte et permet de rattacher l'OFP au PIREP.

### Flux cible

1. Hermès charge l'opération et fait choisir un appareil éligible.
2. Prométhée résout le type dans l'ordre
   `aircraft.simbrief_type -> subfleet.simbrief_type -> aircraft.icao` et retourne
   le type résolu **avec sa source**.
3. Hermès demande à Prométhée une session de génération SimBrief. Le serveur
   produit les paramètres signés à partir de sa clé, et le pilote termine le
   formulaire dans son navigateur.
4. Après génération, Hermès appelle un endpoint authentifié d'import. Le serveur
   télécharge l'OFP, vérifie le pilote, le vol et l'appareil, puis le stocke.
5. Hermès récupère un **DTO JSON normalisé** (route, niveaux, carburant, masses,
   horaires, météo, identifiant OFP) et, séparément, le document XML/PDF si le
   pilote le demande.
6. Le pré-dépôt envoie `simbrief_id` avec `flight_id` et `aircraft_id` ;
   Prométhée attache atomiquement l'OFP au PIREP.

### Endpoints ACARS à exposer

```text
GET  /api/acars/operations
GET  /api/acars/flights/{flight}/aircraft-eligibility
POST /api/acars/flights/{flight}/simbrief/session
POST /api/acars/flights/{flight}/simbrief/import
GET  /api/acars/simbrief/{id}
POST /api/acars/pireps/prefile
```

Les endpoints d'import doivent refuser un OFP d'un autre pilote ou d'un autre
vol, ne jamais accepter une URL arbitraire fournie par le client (protection
SSRF), limiter la taille et le temps de téléchargement, et journaliser seulement
les identifiants non secrets.

## 5. Prompt d'implémentation

> Tu travailles dans le monorepo Air Inter VA. Transforme Hermès ACARS en poste
> opérationnel fiable entre le client MSFS et Prométhée/phpVMS, sans dupliquer la
> logique métier du serveur.
>
> **Commence par un audit exécutable.** Reproduis le chargement de
> `GET /api/flights/{flight_id}/aircraft` avec un pilote de test et couvre : grade
> sans sous-flotte, vol sans sous-flotte, mauvais aéroport, appareil non actif ou
> non parked, bid concurrent et OFP actif. Vérifie que l'identifiant utilisé depuis
> une réservation est `bid.flight.id`. Ajoute des tests avant toute correction.
>
> **Crée une façade API ACARS versionnée** qui retourne des DTO explicites, et
> non les modèles Eloquent. Fournis une opération agrégée et une liste
> d'éligibilité des appareils avec des `reason_codes`. Distingue toujours une
> liste vide (`200`) d'une panne via Problem Details (`code`, `correlation_id`,
> `retryable`). Propage le correlation ID dans les logs serveur et client.
>
> **Intègre SimBrief côté serveur.** Réutilise `SimBriefService` et la clé stockée
> dans les settings phpVMS. Résous le type avec
> `aircraft.simbrief_type -> subfleet.simbrief_type -> aircraft.icao`. Expose la
> création d'une session, l'import de l'OFP et un briefing JSON normalisé. Attache
> `simbrief_id` au PIREP dans une transaction. Ne mets aucun secret SimBrief dans
> Hermès. Valide strictement propriétaire, vol et appareil ; réactive aussi le
> contrôle de propriété sur l'endpoint XML de briefing.
>
> **Refonds l'écran Mes opérations** en parcours guidé : réservation,
> appareil, OFP, pré-dépôt, connexion simulateur, démarrage. Affiche pour chaque
> étape un statut et une action corrective. En cas d'échec, montre un message
> français utile et un panneau diagnostic copiable sans jeton. Prévois chargement,
> vide, erreur, hors ligne et reprise. Respecte l'accessibilité clavier et
> `prefers-reduced-motion`.
>
> **Fiabilise le vol.** Toutes les mutations doivent accepter une clé
> d'idempotence. Persiste localement uniquement la mission active et la file de
> télémétrie nécessaire, chiffrées si elles contiennent des données sensibles.
> Rejoue dans l'ordre, déduplique côté serveur et n'attache jamais silencieusement
> des points à un ancien PIREP.
>
> **Critères d'acceptation :** un pilote autorisé peut aller d'une réservation au
> PIREP déposé ; chaque motif de flotte vide est explicable ; un 401 propose une
> reconnexion, un 5xx une relance avec correlation ID ; un OFP SimBrief est
> importé et attaché au bon PIREP ; l'OFP d'un autre pilote retourne 403 ; aucun
> secret n'apparaît dans les logs ou le binaire ; les tests unitaires, intégration,
> contrat et reprise hors ligne passent. Documente migrations, configuration,
> rollback et procédure de diagnostic administrateur.

## 6. Ordre de livraison conseillé

1. Instrumenter l'erreur actuelle et corriger le contrat `bid.flight.id` /
   appareils.
2. Ajouter l'endpoint d'éligibilité et les tests de matrice grade/vol/flotte.
3. Exposer la façade SimBrief et sécuriser la propriété des briefings.
4. Brancher le parcours guidé Hermès et le pré-dépôt atomique.
5. Ajouter résilience hors ligne, observabilité, messagerie et briefing enrichi.

Cette séquence rétablit d'abord le chemin critique, puis transforme Hermès en
véritable cockpit opérationnel sans créer une seconde source de vérité.
