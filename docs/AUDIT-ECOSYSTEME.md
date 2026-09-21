# Audit de l'écosystème Air Inter VA

## Cadre et méthode

Cet audit applique la règle suivante :

> **EXISTANT → AUDIT → CONSERVATION → AMÉLIORATION → EXTENSION**

Prométhée et Hermès sont des produits en cours de développement, pas des projets
vierges. Une fonctionnalité n'est donc proposée qu'après recherche d'un équivalent
dans le code. L'ordre de préférence est : **améliorer**, **étendre**,
**refactoriser**, puis seulement **remplacer** si une preuve technique le justifie.

### Périmètre effectivement inspecté

- le socle Laravel/phpVMS contenu dans `prometheus/` : routes, contrôleurs,
  services, modèles, migrations, commandes, middleware, vues, traductions,
  configuration et tests ;
- les extensions Prométhée visibles dans les ressources et dans
  `modules/Promethee/Database/migrations` ;
- Hermès dans `acars/` : application .NET, client API, SimConnect, enregistreur,
  télémétrie, stockage local, interface, publication et installateur ;
- l'orchestration locale Docker et les scripts de démarrage ;
- les fichiers d'hébergement présents dans le dépôt.

### Limites de preuve

- aucun code séparé identifiable comme l'ancien OCC **Prometheus** n'est isolé
  dans ce dépôt. Le répertoire `prometheus/` contient le socle phpVMS qui héberge
  aussi le travail Prométhée actuel. Une comparaison historique stricte exige un
  tag, une branche, un dépôt ou un export de production antérieur clairement
  identifié ;
- aucun arbre applicatif autonome d'`airinter-va.org` n'a été identifié. Les
  répertoires cPanel, mail et logs ne constituent pas une source applicative
  exploitable et contiennent des données privées ;
- une table créée par migration ou une chaîne traduite prouve une intention et
  un début d'implémentation, pas un parcours fonctionnel. En l'absence de route,
  contrôleur/service et test consommateurs, l'état retenu est **PRÉVU DANS LE
  CODE** ou **PARTIEL**, jamais « fonctionnel » ;
- l'état de la production et des services externes reste **À VÉRIFIER** sans test
  authentifié sur l'environnement déployé.

## Nomenclature constatée

| Nom fonctionnel | Emplacement actuel | Conclusion |
| --- | --- | --- |
| Prométhée | `prometheus/` | phpVMS et personnalisations Prométhée cohabitent ; ne pas renommer avant stabilisation. |
| Hermès | `acars/` | client nommé encore `Promethee.Acars` dans les namespaces, assemblages et distributions. |
| Prometheus historique | non isolé | référence historique à documenter avec une source distincte. |
| airinter-va.org | non isolé | audit de code non réalisable avec les seules données présentes. |

La nomenclature est une dette de lisibilité, mais un renommage immédiat aurait un
coût élevé (scripts, namespaces, packages, documentation et déploiement). La
décision recommandée est **CONSERVER maintenant**, puis préparer un renommage
progressif et réversible lorsque les flux fonctionnels seront stabilisés.

## Inventaire de l'existant

Dans ce tableau, « Prometheus/phpVMS » désigne le socle historique réellement
visible ; « Prométhée » désigne les adaptations et extensions Air Inter ; Hermès
désigne le client `acars/`.

| Domaine | Prometheus/phpVMS | Prométhée | Hermès | État consolidé |
| --- | --- | --- | --- | --- |
| Authentification | login, API key, rôles et permissions | session ACARS Bearer 12 h ajoutée | login pilote et clé historique | **EXISTANT MAIS À AMÉLIORER** |
| Réservation | bids complets et API utilisateur | réutilisation du modèle existant | récupération `/user/bids` | **EXISTANT MAIS À AMÉLIORER** |
| Horaires/planning | vols, horaires et recherche | habillage et libellés présents | sélection/recherche de vol | **PARTIEL** côté parcours Hermès |
| ACARS | stockage positions, événements et live map | endpoints session/télémétrie | SimConnect, phases et synchronisation | **EN COURS DE DÉVELOPPEMENT** |
| PIREP | pré-dépôt, mise à jour, dépôt, finances | briefing et télémétrie prévus | pré-dépôt, enregistrement et dépôt | **EXISTANT MAIS À AMÉLIORER** |
| SimBrief | service, modèles, UI et import OFP | endpoints ACARS session/import ajoutés | ouverture, import et rattachement | **EN COURS DE DÉVELOPPEMENT** |
| Flotte | appareils, sous-flottes, grades/type ratings | tarification et automatisations prévues | choix d'appareil autorisé | **EXISTANT MAIS À CORRIGER** |
| Live map | routes ACARS GeoJSON et vue | personnalisation à vérifier | carte locale de la trace | **PARTIEL** |
| Missions | traductions historiques présentes | tables missions créées | aucune consommation dédiée | **PRÉVU DANS LE CODE** |
| Tours/circuits | vocabulaire historique présent | tables circuits et étapes créées | aucune consommation dédiée | **PRÉVU DANS LE CODE** |
| Passeport | libellés et vues historiques à rechercher par thème | entrée de navigation/traduction | hors périmètre immédiat | **À VÉRIFIER** |
| Qualifications | grades, sous-flottes et type ratings | règles automatiques prévues | contraintes réutilisées pour la flotte | **PARTIEL** |
| Statistiques | PIREP, widgets et statistiques phpVMS | bulletin/progression prévus en base | rapport et historique local limité | **EXISTANT MAIS À AMÉLIORER** |
| Événements | modèle phpVMS et notifications | tables événements et RSVP | aucune UI dédiée | **PARTIEL** |
| Multilingue | huit catalogues visibles | middleware, préférence utilisateur et commande de contrôle | interface actuellement française en dur | **EXISTANT MAIS À FIABILISER** |
| Notifications | infrastructure Laravel/phpVMS | table de messagerie prévue | messages d'état locaux | **PARTIEL** |
| API | API phpVMS riche et authentifiée | session ACARS et pont SimBrief | proxy local vers Prométhée | **EN COURS DE DÉVELOPPEMENT** |
| Thèmes | layouts `seven` et `beta` | moderne, années 2000 et Minitel visibles | moderne/Minitel dans l'UI | **EXISTANT MAIS À AMÉLIORER** |
| Tableau à palettes | non identifié dans le socle générique | assets et catalogues de tableau présents | hors périmètre | **PARTIEL / À VÉRIFIER EN PRODUCTION** |
| Administration | administration phpVMS complète | schémas de gouvernance supplémentaires | règles locales éditables | **EXISTANT MAIS À AMÉLIORER** |
| Téléchargements | contrôleur et routes existants | distribution Hermès à relier | ZIP et Setup générables | **EXISTANT MAIS À RELIER** |
| Updater Hermès | sans objet | téléchargement possible | aucun mécanisme d'auto-update identifié | **ABSENT APRÈS VÉRIFICATION DU CODE HERMÈS** |

## Cartographie de l'architecture actuelle

```text
                           ┌──────────────────────────┐
                           │ airinter-va.org          │
                           │ code non isolé ici       │
                           └────────────┬─────────────┘
                                        │ lien/SSO à vérifier
                                        ▼
┌──────────────────────────────────────────────────────────────────┐
│ prometheus/ : phpVMS + personnalisations Prométhée              │
│                                                                  │
│ sources de vérité : pilotes, droits, vols, bids, flotte, PIREP  │
│ services : SimBrief, ACARS API, live map, notifications          │
│ extensions prévues : missions, circuits, économie, messagerie    │
└───────────────┬──────────────────────────────┬───────────────────┘
                │ API Bearer                  │ API SimBrief serveur
                ▼                             ▼
┌────────────────────────────┐       ┌────────────────────────────┐
│ acars/ : Hermès            │       │ SimBrief                   │
│ préparation + télémétrie   │──────▶│ compte personnel du pilote │
│ file locale + PIREP        │◀──────│ OFP importé par Prométhée  │
└───────────────┬────────────┘       └────────────────────────────┘
                │ SimConnect
                ▼
┌────────────────────────────┐
│ MSFS 2020 / 2024           │
└────────────────────────────┘
```

### Propriété des données

| Donnée | Propriétaire | Modifiée par | Consommée par |
| --- | --- | --- | --- |
| compte, grade, qualifications | Prométhée/phpVMS | administration | site, Hermès |
| vol et horaires | Prométhée/phpVMS | opérations | site, Hermès, SimBrief |
| réservation | Prométhée/phpVMS | pilote/site/API | Hermès |
| flotte et disponibilité | Prométhée/phpVMS | administration et règles PIREP | site, Hermès, SimBrief |
| OFP | SimBrief, copie liée dans Prométhée | pilote puis import serveur | Prométhée, Hermès, PIREP |
| télémétrie brute active | Hermès, puis Prométhée | SimConnect/Hermès | live map, PIREP, safety |
| file hors ligne | Hermès local uniquement | Hermès | synchronisation Prométhée |
| PIREP et carrière | Prométhée/phpVMS | Hermès puis validation | site, statistiques, safety |

Cette répartition est déjà saine dans son principe. Il faut la consolider plutôt
que créer une deuxième base métier dans Hermès.

## Audit Prométhée/phpVMS

### À conserver

- le socle phpVMS : modèles, services et parcours éprouvés pour pilotes, vols,
  flotte, bids, PIREP, rôles, permissions, finances, notifications et API ;
- les deux layouts existants et les assets Prométhée au lieu d'une nouvelle
  application frontend parallèle ;
- l'intégration SimBrief existante (`SimBriefService`, modèles, formulaires,
  import OFP) que l'API Hermès doit appeler plutôt que reproduire ;
- les routes API ACARS existantes pour positions, événements, logs et PIREP ;
- les huit catalogues de langue, le middleware de langue, la préférence stockée
  sur l'utilisateur et la commande de vérification des traductions ;
- le tableau à palettes, les thèmes moderne/2000/Minitel et leurs variantes : ce
  sont des fonctions engagées à tester et améliorer, pas à reproposer.

### À terminer

- relier les tables Prométhée qui ne sont actuellement visibles que dans les
  migrations à des modèles, services, permissions, contrôleurs, routes, vues et
  tests avant de les déclarer fonctionnelles : missions, circuits, affectations,
  boutique, transferts, messagerie, briefings, RSVP, progression et économie ;
- établir une matrice de migration **Prometheus historique → phpVMS/Prométhée**
  à partir d'une source historique identifiable. Le dépôt actuel ne permet pas
  de distinguer de façon fiable « migré », « abandonné » et « repensé » ;
- vérifier le tableau opérations/palettes avec des données de production : plage
  horaire, fuseau, retards, annulations, responsive, accessibilité et cache ;
- fiabiliser le multilingue par des tests de requête couvrant URL, cookie,
  préférence pilote et fallback, et non uniquement la présence des clés ;
- relier la page de téléchargements aux artefacts signés et versionnés d'Hermès.

### À corriger ou sécuriser

- l'endpoint API de briefing SimBrief est maintenant limité au propriétaire
  authentifié ; il faut ajouter le test de non-divulgation qui empêchera toute
  régression ;
- les nouvelles API Hermès doivent retourner des erreurs métier stables et ne
  pas exposer les détails sensibles de phpVMS ;
- la documentation annonce plusieurs fonctionnalités comme « ajoutées » alors
  que certaines ne sont prouvées dans ce checkout que par leur schéma de base.
  La documentation doit distinguer `livré`, `partiel`, `schéma prêt` et `prévu`.

## Audit Hermès

### Ce qui existe réellement

- deux surfaces d'exécution dans le même projet .NET 8 : serveur web local
  multiplateforme et application Windows/WebView2 ;
- authentification par mot de passe échangé contre un Bearer token et compatibilité
  avec l'ancienne API key ;
- validation HTTPS du serveur, avec exception locale ;
- récupération utilisateur, vols, réservations et appareils autorisés ;
- pré-dépôt et dépôt du PIREP ;
- lecteur SimConnect natif avec position, altitudes MSL/AGL, IAS, GS, VS, cap,
  carburant, état sol, inclinaison, train, toucher, volets et frein de parc ;
- machine de phases `BOARDING → PUSHBACK → TAXI_OUT → TAKEOFF → ENROUTE →
  APPROACH → FINAL → LANDING → TAXI_IN → IN` et événements OUT/OFF/ON/IN ;
- calcul de distance, temps en vol, temps block, carburant et landing rate ;
- détection initiale de `HARD_LANDING`, `TAXI_OVERSPEED` et `GEAR_UP_FINAL` ;
- envoi par lots des positions et événements, avec acquittement après succès ;
- cache local écrit atomiquement, reprise explicite, historique et règles ;
- carte locale, chronologie, diagnostic exportable et trois présentations ;
- scripts de publication autonome Windows x64 et installateur Inno Setup ;
- flux SimBrief nouvellement branché sur le service serveur existant.

### Classification des problèmes

| Constat | Nature | Décision |
| --- | --- | --- |
| erreur générique lors du chargement des appareils | **BUG + AMÉLIORATION UX** | **CORRIGER** avec statut HTTP/code/correlation ID |
| confusion possible entre `bid.id` et `bid.flight.id` | **BUG de contrat** | **CORRIGER** et tester le DTO |
| restrictions d'appareil non expliquées | **FONCTIONNALITÉ INCOMPLÈTE** | **ÉTENDRE** l'API existante |
| file locale non chiffrée | **AMÉLIORATION SÉCURITÉ** | **AMÉLIORER** selon sensibilité retenue |
| erreurs de synchronisation volontairement avalées | **DETTE TECHNIQUE / OBSERVABILITÉ** | **AMÉLIORER** sans supprimer la file |
| règles safety limitées et dédupliquées par code pour tout le vol | **FONCTIONNALITÉ INCOMPLÈTE** | **ÉTENDRE**, pas remplacer |
| aucun updater identifié | **NOUVELLE FONCTIONNALITÉ** | **CRÉER** après stabilisation du packaging |
| deux UI, web locale et WebView2/desktop | **DETTE D'ARCHITECTURE potentielle** | **ÉVALUER**, ne pas réécrire sans mesure |
| artefacts `bin/` et `obj/` suivis par Git | **DETTE D'OUTILLAGE** | **CORRIGER** séparément et prudemment |

### Ce qu'il faut terminer avant d'étendre

1. rendre le parcours `réservation → appareil → SimBrief → préfile` fiable et
   testable de bout en bout ;
2. rendre les erreurs d'éligibilité compréhensibles sans exposer de secret ;
3. ajouter des tests sur les transitions de phases, interruptions, rejeu et
   idempotence ;
4. consolider l'API de télémétrie et les accusés serveur ;
5. versionner le protocole Hermès/Prométhée et gérer une version minimale du
   client ;
6. seulement ensuite enrichir le briefing, la messagerie dispatch et le safety.

## SimBrief : décision d'intégration

Le flux ajouté ne reconstruit pas SimBrief et ne remplace pas l'intégration
phpVMS. Il l'étend à Hermès :

```text
Prométhée (vol, pilote, flotte, clé VA)
       │ paramètres signés, sans clé secrète
       ▼
Hermès ─────▶ fenêtre SimBrief et compte personnel du pilote
       │                 │
       │                 └──── OFP généré
       ▼
Prométhée importe via SimBriefService, contrôle et rattache au PIREP
```

Le principe est bon, mais l'intégration reste **EN COURS DE DÉVELOPPEMENT** tant
que les points suivants ne sont pas couverts par des tests d'intégration : popup
bloquée/fermée trop tôt, OFP retardé, mauvais compte, double clic, expiration du
token, appareil devenu indisponible, unités carburant et rattachement atomique au
PIREP.

## Avancement prudent

Les pourcentages ci-dessous mesurent la **couverture visible dans ce checkout**,
pas la qualité de la production. Ils ne sont donnés que lorsque des parcours
exécutables et plusieurs couches de code permettent une approximation ; sinon
l'état est explicitement non évaluable.

### Prométhée

| Domaine | Estimation | Justification |
| --- | ---: | --- |
| Authentification socle | 80 % | parcours web/API mature ; convergence d'identité non démontrée |
| Dashboard et thèmes | 65 % | layouts/assets présents ; validation complète production manquante |
| Réservations et planning | 75 % | socle bids/vols/API existant ; parcours Hermès à fiabiliser |
| PIREP | 80 % | cycle phpVMS et API très avancé ; intégration Hermès à tester |
| Flotte/qualifications | 75 % | modèles/règles existants ; diagnostic d'éligibilité incomplet |
| Tableau à palettes | **NON ÉVALUABLE AVEC LES INFORMATIONS DISPONIBLES** | éléments visuels/traductions, parcours complet non isolé |
| Multilingue | 65 % | huit catalogues + middleware + contrôle ; comportement déployé à vérifier |
| Missions/circuits | 25 % | schéma et traductions ; couches applicatives non démontrées |
| Administration Prométhée | **NON ÉVALUABLE AVEC LES INFORMATIONS DISPONIBLES** | admin phpVMS mature, extensions Prométhée incomplètement traçables |
| API Hermès | 60 % | auth, vols, flotte, PIREP, télémétrie, SimBrief ; contrat/tests à consolider |

### Hermès

| Domaine | Estimation | Justification |
| --- | ---: | --- |
| Authentification | 70 % | Bearer sécurisé et API key ; logout/renouvellement/SSO à compléter |
| Connexion Prométhée | 65 % | client et proxy fonctionnels ; erreurs/contrat/version à améliorer |
| Connexion simulateur | 70 % | SimConnect et reprise de connexion ; validation MSFS réelle requise |
| Réservations/flotte | 45 % | récupération présente, chemin critique actuellement bloquant |
| Télémétrie | 70 % | capture, lots et file locale ; tests de charge/rejeu requis |
| Détection des phases | 65 % | machine complète initiale ; cas limites à tester |
| PIREP | 65 % | préfile, dépôt et métriques ; atomicité/idempotence à renforcer |
| Mode hors ligne | 60 % | file persistante et reprise explicite ; chiffrement/quotas à étudier |
| UI | 50 % | écrans principaux et thèmes ; guidage, accessibilité et erreurs à finir |
| Packaging | 70 % | ZIP autonome et Inno Setup ; signature/release automatisée manquantes |
| Updater | 0 % | aucune implémentation identifiée après inspection du code Hermès |

Ces valeurs doivent être remplacées par des mesures de critères d'acceptation dès
qu'une matrice de tests de production existe.

## Décisions : conserver, terminer, améliorer, créer

### 1. Ce que nous avons déjà — **CONSERVER**

- phpVMS comme cœur métier et administratif ;
- thèmes et identité Air Inter déjà engagés ;
- multilingue et préférence utilisateur ;
- flotte, grades, bids, SimBrief et PIREP du socle ;
- authentification ACARS Bearer existante ;
- SimConnect, machine de phases, OUT/OFF/ON/IN, télémétrie et cache local ;
- packaging Windows et installateur ;
- nouvelles tables Prométhée comme travaux engagés, sans les confondre avec des
  fonctionnalités livrées.

### 2. Ce que nous devons terminer — **TERMINER**

- parcours appareils autorisés et réservation dans Hermès ;
- intégration SimBrief de bout en bout et rattachement fiable au PIREP ;
- exposition applicative des migrations Prométhée réellement prioritaires ;
- tests multilingues, tableau à palettes et thèmes sur mobile/nuit ;
- tests automatiques du protocole, de la machine de phases et du mode hors ligne ;
- canal de téléchargement Hermès depuis Prométhée.

### 3. Ce que nous devons améliorer — **AMÉLIORER / ÉTENDRE**

- erreurs API structurées et diagnostic d'éligibilité ;
- observabilité et correlation IDs ;
- sécurité de l'accès aux briefings et des données locales ;
- cohérence de nommage visible sans déplacer brutalement les arbres ;
- safety basé sur les événements déjà capturés ;
- documentation qui sépare livré, partiel, schéma prêt et idée.

### 4. Ce que nous devons créer — **CRÉER APRÈS STABILISATION**

- mise à jour signée d'Hermès et politique de compatibilité ;
- contrat API versionné et tests de contrat ;
- pont d'identité avec airinter-va.org si le besoin SSO est confirmé ;
- fonctions externes uniquement par intégration : Navigraph, VATSIM, IVAO et
  météo spécialisée ne doivent pas être réimplémentés.

## Roadmap fondée sur le code réel

### Phase 0 — preuve et hygiène, immédiatement

1. créer une matrice de tests reproductible avec pilote, grade, vol, sous-flotte,
   appareil, bid, OFP et PIREP ;
2. corriger le chargement des appareils et distinguer `200 []`, 401, 403 et 5xx ;
3. tester systématiquement l'accès propriétaire aux briefings SimBrief ;
4. ignorer les futurs `bin/`, `obj/`, `vendor/`, `node_modules/` et données
   d'hébergement, puis nettoyer l'historique dans un chantier séparé avec backup ;
5. identifier officiellement les sources de Prometheus historique et
   d'airinter-va.org avant toute conclusion de migration.

### Phase 1 — Prométhée/Hermès à terminer

1. figer et versionner les DTO réservation, flotte, OFP, télémétrie et PIREP ;
2. terminer le parcours guidé Hermès et le flux SimBrief ;
3. ajouter idempotence et accusés durables ;
4. automatiser les tests de langue, phases, offline et droits ;
5. publier une première version signée depuis la page téléchargements existante.

### Phase 2 — Prométhée à achever

1. sélectionner, parmi les schémas déjà créés, les fonctions réellement utiles à
   la vision future Air Inter ;
2. terminer en priorité missions/circuits **si** les règles métier sont validées ;
3. terminer progression, événements et messagerie en réutilisant les modèles
   phpVMS plutôt qu'en les dupliquant ;
4. auditer et améliorer tableau à palettes, thèmes nuit et responsive ;
5. établir la matrice de transition depuis Prometheus historique.

### Phase 3 — avantage différenciant

1. briefing opérationnel consolidé dans Hermès à partir des données Prométhée et
   des services externes ;
2. safety Air Inter fondé sur la télémétrie déjà capturée, avec pédagogie plutôt
   qu'un score punitif ;
3. messagerie dispatch liée au vol et visible dans Prométhée ;
4. live map et suivi OCC avec qualité du dernier contact ;
5. updater signé et compatibilité contrôlée.

## Avis final

Le diagnostic initial est confirmé avec une nuance importante : **Hermès est un
prototype fonctionnel avancé**, et **Prométhée combine un socle OCC mature avec
des extensions Air Inter à des niveaux très différents**. Certaines sont livrées
visuellement, d'autres ne sont encore que des schémas et des traductions.

La meilleure valeur ne viendra donc ni d'une réécriture ni d'une accumulation de
pages. Elle viendra d'abord de la fermeture du chemin critique existant :

```text
PROMÉTHÉE → réservation → HERMÈS → appareil/OFP → MSFS
           → télémétrie OUT/OFF/ON/IN → PIREP → PROMÉTHÉE
```

Une fois ce trajet fiable, observable et testable, les briques déjà engagées
(tableau, langues, missions, progression, safety, messagerie) pourront être
achevées dans un ordre fondé sur leur utilité réelle pour Air Inter VA.
