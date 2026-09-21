# Air Inter VA — gaps produit et techniques (22 septembre 2026)

Ce document confronte l'audit stratégique au code réellement présent sur `master`.

## Déjà présent

- Prométhée dépasse déjà le simple thème phpVMS : portail, salle opérations, tableau départs, calendrier, administration, progression, missions/circuits, économie et sécurité.
- Hermès possède SimConnect, une machine d'état de vol, une file locale résiliente, le suivi OUT/OFF/ON/IN et une base de connecteurs.
- Une session ACARS courte durée existe côté Prométhée.
- Une façade `/api/acars/operations` et une intégration serveur SimBrief existent.
- La CI contrôle l'hygiène du dépôt, compile/teste Hermès et vérifie une partie du PHP Prométhée.
- La documentation distingue correctement Prométhée, Hermès, phpVMS et SimBrief.

## P0 — à terminer avant d'élargir le produit

### 1. Stabiliser le contrat Prométhée ↔ Hermès

L'API ACARS existe, mais elle reste un assemblage d'endpoints spécifiques. Il faut la transformer en contrat versionné et explicite.

À ajouter :

- `/api/v1/me`
- `/api/v1/operations`
- `/api/v1/operations/{id}`
- `/api/v1/operations/{id}/aircraft-eligibility`
- `/api/v1/operations/{id}/briefing`
- `/api/v1/pireps/prefile`
- erreurs Problem Details stables ;
- `correlation_id` de bout en bout ;
- `Idempotency-Key` sur toutes les mutations ;
- version de contrat dans les réponses.

Hermès ne doit plus dépendre de la forme des modèles phpVMS.

### 2. Expliquer la disponibilité des appareils

La route actuelle ne renvoie que les appareils disponibles. Elle doit également pouvoir expliquer pourquoi les autres sont refusés :

- `RANK_NOT_ALLOWED`
- `FLIGHT_SUBFLEET_NOT_ALLOWED`
- `WRONG_AIRPORT`
- `NOT_PARKED`
- `INACTIVE`
- `ALREADY_BID`
- `ACTIVE_OFP`

C'est indispensable pour rendre les erreurs actionnables dans Hermès.

### 3. Tester le parcours critique de bout en bout

Le critère Prométhée 1.0 est : « un nouveau pilote trouve un vol et le termine sans assistance ».

Il faut donc un scénario automatique couvrant :

`connexion -> opération -> appareil -> SimBrief -> pré-dépôt -> télémétrie -> OUT/OFF/ON/IN -> dépôt PIREP`.

Les tests phpVMS génériques ne remplacent pas un test métier Prométhée/Hermès.

### 4. Réconcilier les branches

Le dépôt a `main` comme branche par défaut alors que le développement réel est très largement sur `master`. Les deux branches divergent fortement.

Décision à prendre rapidement :

- soit `master` devient la branche par défaut ;
- soit `main` est réaligné puis devient l'unique branche de référence.

Tant que ce point reste ambigu, CI, PR et déploiements restent plus risqués qu'ils ne devraient l'être.

## P1 — rendre Prométhée réellement opérationnel

### Briefing unifié

Créer un DTO unique contenant :

- vol/réservation ;
- appareil sélectionné ;
- météo ;
- OFP ;
- carburant et masses ;
- restrictions compagnie ;
- statut réseau ;
- contexte historique disponible ;
- état de préparation Hermès.

### Live Operations

Le tableau à palettes doit être la vue d'une vraie opération, pas uniquement un rendu d'horaires.

Une opération doit connaître :

- pilote ;
- appareil ;
- phase Hermès ;
- dernier contact ;
- ETA ;
- retard ;
- source du vol ;
- statut OFP ;
- statut PIREP.

### Rapport après-vol

Remplacer le simple « accepted » par une lecture utile :

- **Safety**
- **Operations**
- **Flight**

Le score numérique absolu n'est pas nécessaire pour le MVP.

### Flotte persistante légère

Conserver position, heures, cycles et historique d'une immatriculation après validation du PIREP, avec repositionnement autorisé au départ.

## P1 — UX Hermès

Hermès doit devenir un parcours guidé :

1. Opération
2. Appareil
3. OFP
4. Pré-dépôt
5. Simulateur
6. READY
7. Vol
8. Rapport
9. Envoi

Chaque étape doit avoir : état, cause de blocage, action corrective et diagnostic copiable sans secret.

## P1 — qualité technique

- tests de contrat JSON entre PHP et C# ;
- tests d'autorisation inter-pilotes ;
- tests des motifs d'inéligibilité flotte ;
- secret scanning automatique ;
- dépendances PHP/.NET vérifiées ;
- journalisation structurée ;
- métriques de synchronisation ACARS ;
- procédure de rollback documentée.

## P2 — différenciation Air Inter

Une fois le chemin critique fiable :

- Carnet Air Inter à la place du passeport générique ;
- missions qui modifient l'état réel de la flotte ;
- activité compagnie vivante ;
- historique contextualisé relié aux vols ;
- horaires historiques sourcés si et seulement si Air Inter VA souhaite ce niveau éditorial ;
- PWA Prométhée.

## Ce qu'il ne faut pas prioriser

- reconstruire SimBrief ;
- application mobile native Prométhée ;
- économie ultra détaillée ;
- XP massif ;
- classement permanent ;
- sanctions ACARS agressives ;
- SSO maison avant d'avoir plusieurs applications réellement autonomes.

## Règle d'architecture

**Prométhée décide et expose le métier. Hermès exécute et observe le vol. phpVMS reste un moteur interne.**

Toute nouvelle feature doit respecter cette frontière.
