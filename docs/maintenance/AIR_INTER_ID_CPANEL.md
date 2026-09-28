# Argos — déploiement cPanel

> Le dossier technique reste `airinter-id/` pendant la transition afin de ne pas casser les déploiements existants.

Cible : `https://id.airinter-va.org`

Ce document décrit le premier déploiement **sans activer encore le SSO dans Prométhée/Hermès**. La première mise en ligne sert à créer l'autorité d'identité, importer les comptes existants et vérifier la continuité des accès.

## 1. Sous-domaine

Dans cPanel, créer :

```text
id.airinter-va.org
```

Le document root doit pointer vers :

```text
/home/<cpanel-user>/airinter-id/public
```

et jamais vers `/home/<cpanel-user>/airinter-id`.

Activer AutoSSL / certificat valide avant toute connexion.

## 2. Version PHP

Utiliser PHP 8.3 ou plus récent compatible avec le projet.

Extensions minimales :
- openssl
- mbstring
- pdo_mysql
- tokenizer
- xml
- ctype
- json
- fileinfo

## 3. Base Argos

Créer une base et un utilisateur dédiés, par exemple :

```text
<cpanel>_airinter_id
<cpanel>_airinter_id
```

Attribuer tous les droits à cet utilisateur **uniquement sur la base Argos**.

## 4. Compte de lecture Prométhée

Créer temporairement un second utilisateur MySQL destiné à l'import.

Il ne doit avoir aucun droit d'écriture sur Prométhée.

Le besoin fonctionnel est uniquement :

```sql
GRANT SELECT ON <promethee_database>.users TO '<readonly_user>'@'localhost';
```

Adapter la syntaxe à l'interface cPanel disponible. Ne pas réutiliser les identifiants MySQL complets de Prométhée dans Argos si un compte lecture seule peut être créé.

## 5. Déployer les fichiers

Le dossier attendu est :

```text
~/airinter-id
```

Depuis le dépôt :

```bash
cd ~
git clone <repository-url> airinter-va-project
cp -a airinter-va-project/airinter-id ./airinter-id
cd ~/airinter-id
```

Sur une installation déjà reliée au dépôt, déployer à la place le commit testé correspondant à la release.

## 6. Configurer l'environnement

```bash
cp .env.example .env
```

Renseigner au minimum :

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://id.airinter-va.org
ARGOS_NAME="Argos"
ARGOS_RELEASE=<git-commit-sha-deployed>

DB_DATABASE=<airinter_id_database>
DB_USERNAME=<airinter_id_user>
DB_PASSWORD=<strong-password>

PROMETHEE_DB_DATABASE=<promethee_database>
PROMETHEE_DB_USERNAME=<readonly_user>
PROMETHEE_DB_PASSWORD=<readonly-password>
```

Conserver :

```dotenv
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=null
SESSION_SAME_SITE=lax
```

Ne jamais recopier le `.env` dans Git.

## 7. Installer Argos

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan vendor:publish --tag=passport-migrations
php artisan migrate --force
php artisan passport:keys
```

Puis créer/vérifier les trois clients de première partie :

```bash
php artisan airinter-id:configure-clients --show-secrets
```

La commande prépare :

| Client | Type | Callback |
| --- | --- | --- |
| Prométhée | confidentiel | `https://promethee.airinter-va.org/auth/airinter-id/callback` |
| Hermès | public PKCE | `http://127.0.0.1:47821/callback` |
| Air Inter VA | confidentiel | `https://www.airinter-va.org/auth/airinter-id/callback` |

Les secrets Prométhée et Air Inter VA ne sont affichés qu'à la création.

Les stocker dans un gestionnaire de secrets / cPanel, jamais dans Git ou un message public.

Hermès est un client public et n'a donc **aucun secret** embarqué.

## 8. Vérifier avant import

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan argos:doctor
php artisan argos:doctor --strict
php artisan airinter-id:configure-clients
```

Tester dans le navigateur :

```text
https://id.airinter-va.org/
https://id.airinter-va.org/login
https://id.airinter-va.org/up
```

À ce stade, Prométhée et Hermès continuent d'utiliser leur authentification actuelle.

## 9. Importer les comptes phpVMS

Avant l'import :
- sauvegarder la nouvelle base Argos ;
- vérifier que l'utilisateur Prométhée est bien en lecture seule ;
- ne pas utiliser `--sync-passwords` lors du premier essai.

Lancer :

```bash
php artisan airinter-id:import-promethee --force
```

L'import :
- ne modifie aucune table phpVMS ;
- crée un UUID `subject` stable ;
- conserve le lien vers `phpVMS users.id` ;
- conserve le hash de mot de passe existant ;
- conserve les comptes supprimés comme identités historiques désactivées ;
- ne copie aucun PIREP, grade, badge ou historique de vol.

Relancer la commande est supporté : elle est conçue pour resynchroniser les métadonnées sans recréer les identités.

## 10. Tester quelques comptes

Tester au minimum :
- un pilote actif ;
- un pilote en congé ;
- un pilote suspendu si disponible ;
- un compte ancien/supprimé ;
- connexion par e-mail ;
- connexion par ident pilote.

Un compte supprimé/suspendu ne doit pas obtenir de session SSO.

## 11. Optimiser

Une fois les tests terminés, le contrôle de santé doit être sans erreur :

```bash
php artisan argos:doctor
```

`ARGOS_RELEASE` doit contenir le SHA Git réellement déployé. Cela permet de comparer immédiatement la production au dépôt lors d'un incident.

Ensuite :

```bash
php artisan optimize
chmod -R u+rwX storage bootstrap/cache
```

## 12. Point d'arrêt volontaire

**Ne pas encore désactiver les logins Prométhée/Hermès.**

La phase suivante introduira d'abord un bouton de connexion Argos dans Prométhée, puis le flux PKCE dans Hermès.

Pendant cette phase de transition :
- Argos est additif ;
- phpVMS reste opérationnel ;
- les mots de passe historiques restent valides ;
- le retour arrière reste possible.

## 13. Rollback

Tant qu'aucun client n'utilise encore Argos :
1. mettre le sous-domaine hors ligne ;
2. restaurer/supprimer uniquement la base Argos ;
3. ne rien modifier dans Prométhée.

Aucune donnée opérationnelle n'a été déplacée, donc Prométhée et Hermès continuent normalement.


## OpenID Connect / Argos

After the Lot 2 deployment, verify the public metadata endpoints:

```text
https://id.airinter-va.org/.well-known/openid-configuration
https://id.airinter-va.org/.well-known/oauth-authorization-server
https://id.airinter-va.org/.well-known/jwks.json
```

Then run:

```bash
php artisan migrate --force
php artisan argos:doctor --strict
```

A first-party client requesting `openid` must also send:
- `state`;
- `nonce`;
- `code_challenge`;
- `code_challenge_method=S256`.

The token exchange must provide the matching `code_verifier`. A successful OpenID authorization-code exchange returns the normal Passport token response plus `id_token`.
