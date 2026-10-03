# Argos — Prométhée SSO OIDC

Prométhée utilise désormais Argos comme fournisseur d'identité OpenID Connect tout en conservant le login phpVMS historique comme solution de repli pendant la migration.

## Flux d'authentification

1. Prométhée génère un `state`, un `nonce` et un couple PKCE `code_verifier` / `code_challenge`.
2. Le navigateur est redirigé vers `https://argos.airinter-va.org/oauth/authorize`.
3. Argos authentifie le membre (mot de passe, MFA, passkey selon la politique active).
4. Argos redirige vers `https://promethee.airinter-va.org/auth/airinter-id/callback` avec un authorization code.
5. Prométhée échange le code côté serveur en envoyant aussi le `code_verifier`.
6. Argos renvoie un `access_token`, un `refresh_token` et un `id_token`.
7. Prométhée valide localement la signature RS256 de l'ID Token avec le JWKS Argos ainsi que `iss`, `aud`, `exp`, `iat` et `nonce`.
8. Prométhée appelle `/api/v1/me` avec l'access token.
9. Le `sub` de `/api/v1/me` doit correspondre exactement au `sub` vérifié dans l'ID Token.
10. L'identité legacy `provider=promethee` fournit l'immuable `users.id` phpVMS.
11. Prométhée ouvre sa session Laravel locale pour cet utilisateur.

L'e-mail n'est jamais utilisé comme clé inter-systèmes.

## Scopes

Prométhée demande :

```text
openid profile email promethee:read
```

## Environnement Prométhée

```dotenv
AIRINTER_ID_ENABLED=true
AIRINTER_ID_URL=https://argos.airinter-va.org
AIRINTER_ID_ISSUER=https://argos.airinter-va.org
AIRINTER_ID_CLIENT_ID=<passport-client-id>
AIRINTER_ID_CLIENT_SECRET=<passport-client-secret>
AIRINTER_ID_REDIRECT_URI=https://promethee.airinter-va.org/auth/airinter-id/callback
```

Les préfixes techniques `AIRINTER_ID_*` sont conservés pour éviter une migration de configuration cassante ; le produit public s'appelle **Argos**.

## Provisioning Argos

Depuis l'application Argos :

```bash
php artisan migrate --force
php artisan passport:keys
php artisan airinter-id:import-promethee --force
php artisan airinter-id:configure-clients --show-secrets
php artisan argos:doctor
```

Stocker le Client ID et le secret Prométhée uniquement dans le `.env` de Prométhée.

## Règles de transition

- le login phpVMS historique reste disponible pendant la migration ;
- aucun utilisateur phpVMS n'est créé automatiquement si le lien Argos manque ;
- les comptes locaux suspendus / rejetés restent refusés ;
- aucune donnée opérationnelle (PIREP, rang, flotte, finances) n'est copiée dans Argos ;
- le `sub` Argos est l'identité OIDC stable ;
- l'ID phpVMS legacy est uniquement utilisé pour retrouver le compte opérationnel local.

## Sécurité

- Authorization Code uniquement ;
- PKCE S256 obligatoire ;
- `state` obligatoire ;
- `nonce` obligatoire ;
- validation RS256 via `/.well-known/jwks.json` ;
- validation de l'issuer et de l'audience ;
- comparaison du `sub` de l'ID Token avec le profil Argos ;
- aucune liaison par e-mail.
