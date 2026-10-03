# Hermès — architecture cible des paramètres, simulateurs et OAuth

## 1. Principe

Hermès possède trois couches de configuration distinctes.

### Politique compagnie — Prométhée / administration

Ces paramètres sont décidés par Air Inter VA et téléchargés après authentification :

- intervalle de télémétrie ;
- événements enregistrés ;
- règles de vol et seuils ;
- règles de pause / simulation rate ;
- auto-file / confirmation PIREP ;
- versions minimale et recommandée ;
- simulateurs autorisés ;
- connecteurs autorisés ;
- règles par type, sous-flotte ou appareil ;
- règles par simulateur ;
- politique de confidentialité/rétention ;
- fonctionnalités activées ;
- messages opérationnels.

Le pilote ne peut pas modifier ces valeurs.

### Profil pilote — synchronisé

Préférences pouvant suivre le compte :

- unités ;
- langue ;
- format horaire ;
- thème ;
- notifications ;
- comportement de démarrage ;
- préférences briefing ;
- simulateur favori.

### Installation locale

Paramètres propres au PC :

- chemins des simulateurs ;
- connecteurs/plugins installés ;
- ports ;
- diagnostics ;
- périphériques ;
- dossiers de plans de vol ;
- cache ;
- logs ;
- démarrage avec Windows.

Aucun secret OAuth ou mot de passe ne doit être stocké dans localStorage.

## 2. Matrice simulateurs

Hermès doit utiliser une abstraction `ISimulatorConnector`.

| Famille | Versions cibles | Connecteur |
| --- | --- | --- |
| Microsoft Flight Simulator moderne | MSFS 2020, MSFS 2024 | SimConnect |
| ESP / Microsoft legacy | FS2004/FS9, FSX | FSUIPC |
| Lockheed Martin | Prepar3D v1-v6 | FSUIPC / SimConnect selon capacité |
| X-Plane | 11, 12 et versions explicitement validées | plugin Hermès / UDP-datarefs |

Chaque connecteur expose un modèle canonique commun : position, altitude, vitesse, cap, vertical speed, sol/air, carburant, moteurs, parking brake, gear, flaps, lights et identité avion lorsque disponible.

Une capability matrix remplace les suppositions : une règle non observable sur un simulateur/add-on est `unsupported`, jamais automatiquement considérée comme une faute.

## 3. Profils avion déclaratifs

Prométhée distribue des profils versionnés :

`simulator + aircraft/addon -> mappings + capabilities + thresholds`

Cela permet d'adapter Fenix, PMDG, iniBuilds, Leonardo, ToLiss, FlightFactor, appareils FS2004 historiques, etc. sans republier Hermès pour chaque mapping.

Les profils distants restent des données déclaratives validées. Hermès n'exécute jamais de code distant.

## 4. OAuth / Air Inter ID

### Cible

Prométhée devient l'Authorization Server de l'écosystème Air Inter. Hermès est un **public client desktop**.

Flux : **Authorization Code + PKCE**.

1. Hermès génère `state`, `code_verifier` et `code_challenge`.
2. Hermès ouvre le navigateur système sur Prométhée.
3. Le pilote se connecte sur le domaine Air Inter.
4. Prométhée affiche le consentement si nécessaire.
5. Retour vers Hermès via loopback `http://127.0.0.1:<port>/oauth/callback`.
6. Hermès échange le code + verifier contre access/refresh tokens.
7. Les tokens sont stockés dans le coffre sécurisé du système, pas dans le front WebView.
8. L'access token est court ; le refresh token est rotatif et révocable.

Hermès n'embarque **aucun client secret** : un secret dans une application desktop n'est pas secret.

### Scopes initiaux

- `profile:read`
- `operations:read`
- `operations:write`
- `briefing:read`
- `pireps:write`
- `telemetry:write`
- `aircraft:read`
- `settings:read`

Les scopes administrateur ne sont jamais accordés à Hermès.

### Migration

1. conserver temporairement login/session et API key ;
2. ajouter Passport/OAuth2 à Prométhée ;
3. enregistrer Hermès comme public client PKCE ;
4. implémenter le callback loopback dans le host C# ;
5. migrer `PhpVmsClient` vers Bearer ;
6. ajouter révocation/logout ;
7. retirer progressivement le mot de passe et la clé API de l'interface standard.

## 5. Écran Paramètres Hermès cible

Sections :

- **Compte** — pilote, session, déconnexion, appareils autorisés ;
- **Simulateurs** — détection, chemins, connecteur, test de connexion ;
- **Avions & add-ons** — profil détecté, capacités, diagnostic ;
- **ACARS** — politique compagnie en lecture seule + préférences autorisées ;
- **Plans de vol** — SimBrief, dossiers PLN/FMS, import/export ;
- **Unités & affichage** ;
- **Notifications** ;
- **Réseau & synchronisation** ;
- **Confidentialité** — données collectées et rétention ;
- **Diagnostics** — version, connecteurs, API, copie du diagnostic expurgé ;
- **Mises à jour**.

## 6. Contrat de configuration

Créer à terme :

- `GET /api/v1/hermes/configuration`
- `GET /api/v1/hermes/aircraft-profiles`
- `GET /api/v1/me/preferences`
- `PATCH /api/v1/me/preferences`
- `POST /api/v1/hermes/diagnostics` (opt-in explicite)

La configuration renvoie `schema_version`, `etag`, `generated_at` et `minimum_client_version`.

Hermès conserve la dernière configuration valide signée/cachée pour tolérer une indisponibilité temporaire de Prométhée.
