# Argos

Standalone identity and SSO service for the Air Inter VA ecosystem.

**Production target:** `https://id.airinter-va.org`

Runbook cPanel : [`docs/maintenance/AIR_INTER_ID_CPANEL.md`](../docs/maintenance/AIR_INTER_ID_CPANEL.md)

Argos is deliberately separate from phpVMS/Prométhée. It owns authentication and security identity; Prométhée remains the source of truth for operational pilot data.

## Responsibilities

Argos owns:
- login credentials;
- stable public subject (`sub`);
- e-mail / verification state;
- authentication sessions;
- future MFA/passkeys/recovery;
- OAuth2 clients/tokens;
- security audit events;
- links to legacy/system identities.

Prométhée keeps:
- phpVMS user id / pilot id;
- ITxxx identifier;
- PIREPs and flight time;
- ranks, awards and qualifications;
- bids/reservations;
- fleet, economy, dispatch and maintenance;
- pilot operational status and permissions.

The public Air Inter site keeps its editorial/history content.

## Why OAuth2 first

Laravel Passport is used as the standards-based OAuth2 authorization server. The initial clients are:
1. Prométhée — confidential web client;
2. Hermès — public native client using Authorization Code + PKCE;
3. airinter-va.org — confidential web client when the historical site is integrated.

Do **not** advertise this service as OpenID Connect yet. OIDC discovery, JWKS and ID Tokens will be enabled only when a vetted OIDC provider layer is installed and tested. The permanent `users.subject` UUID already provides the future OIDC `sub`.

## cPanel layout

Create the subdomain:

```text
id.airinter-va.org
```

Its document root must point to:

```text
/home/<cpanel-user>/airinter-id/public
```

Do not point the subdomain at the project root.

Create a separate MySQL database and user, for example:

```text
<cpanel>_airinter_id
<cpanel>_airinter_id
```

Use a second **read-only** MySQL account for the Prométhée database during migration. Grant it only `SELECT`.

## First deployment

```bash
cd ~/airinter-id
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan vendor:publish --tag=passport-migrations
php artisan migrate --force
php artisan passport:keys
php artisan airinter-id:configure-clients --show-secrets
php artisan optimize
```

Passport 13 requires its migrations to be published into the application; they are intentionally kept explicit instead of relying on hidden package migrations.

Storage permissions:

```bash
chmod -R u+rwX storage bootstrap/cache
```

## Import existing pilots

First use a dedicated read-only Prométhée DB account in `.env`, then:

```bash
php artisan airinter-id:import-promethee --force
```

The command:
- creates an Argos account for each phpVMS user;
- copies the existing Laravel password hash for newly imported users;
- creates a stable UUID subject;
- records the immutable phpVMS `user_id` link;
- does not move or delete operational data.

Existing Argos passwords are not overwritten on later syncs unless:

```bash
php artisan airinter-id:import-promethee --sync-passwords --force
```

That option is for the transition period only.

## First-party OAuth clients

### Prométhée

Confidential web client.

Redirect URI:

```text
https://promethee.airinter-va.org/auth/airinter-id/callback
```

Scopes:

```text
profile email promethee:read
```

### Hermès

Hermès must become a **public PKCE client**. Do not embed a client secret in the executable.

Use the system browser for Argos login. V1 callback:

```text
http://127.0.0.1:47821/callback
```

Hermès starts a temporary loopback listener on that fixed local port, generates `state`, `code_verifier` and `code_challenge`, launches the browser to Argos, receives the authorization code locally, then exchanges it for access/refresh tokens. The port can later become configurable once the server/client redirect contract evolves together.

Scopes:

```text
profile email hermes:operate
```

Hermès should no longer collect the Air Inter password once this flow is production-ready.

### Historical site

A confidential client is provisioned now so the contract is reserved, but the historical site is connected only in a later phase.

Redirect URI:

```text
https://www.airinter-va.org/auth/airinter-id/callback
```

It should use Argos only for member identity. Historical/editorial data stays on the site.

## Transition strategy

### Phase 0 — shadow identity
Import users and links. Existing logins keep working everywhere.

### Phase 1 — Prométhée SSO
Add “Se connecter avec Argos”. Link by immutable phpVMS user id, never only by e-mail.

### Phase 2 — Hermès PKCE
Move Hermès from password submission to browser-based Authorization Code + PKCE.

### Phase 3 — Air Inter VA website
Add member SSO without migrating the museum/editorial database.

### Phase 4 — authority switch
Argos becomes the only place to change password, e-mail, MFA and security settings. Prométhée consumes identity claims but keeps operational fields.

### Phase 5 — OIDC
Add a vetted OIDC provider implementation with signed ID Tokens, JWKS, discovery, nonce validation and standard UserInfo. Keep the existing UUID subject.

## Non-negotiable rules

- Never use e-mail as the permanent cross-system primary key.
- Never copy PIREPs/ranks/badges into Argos.
- Never embed a confidential OAuth secret in Hermès.
- Never let the historical website write directly into Prométhée's operational tables.
- Never remove legacy authentication before every existing pilot has an Argos link.
- Keep rollback possible throughout the migration.


## Lot 0 — production readiness

Before enabling SSO clients in production:

```bash
php artisan migrate --force
php artisan argos:doctor
```

Use `ARGOS_RELEASE` in production to record the exact deployed Git commit SHA.

The repair migration `2026_09_28_190000_repair_runtime_infrastructure_tables.php` is deliberately non-destructive. It creates missing Laravel runtime tables (`sessions`, `cache`, queue tables, etc.) on installations where the original initial migration was already marked as executed without having created all of them.

For deployment gates, use:

```bash
php artisan argos:doctor --strict
```

Warnings then become a non-zero exit code, which makes the command suitable for CI/deployment scripts.


## Lot 1 — OAuth hardening

Argos first-party clients use Authorization Code with PKCE S256.

Security policy:
- exact redirect URI matching;
- `state` required on authorization requests;
- PKCE required for confidential and public clients;
- only `S256` code challenges are accepted;
- only `authorization_code` and `refresh_token` grants are accepted;
- every first-party client has an explicit scope allowlist;
- no default OAuth scopes are silently granted.

Client scope allowlists:

```text
Prométhée: profile email promethee:read
Hermès:    profile email hermes:operate
Website:   profile email
```

The middleware rejects malformed or over-privileged authorization requests before Passport processes them.


## Lot 2 — OpenID Connect

Argos now exposes an OpenID Connect provider layer on top of Laravel Passport.

Standard endpoints:

```text
/.well-known/openid-configuration
/.well-known/oauth-authorization-server
/.well-known/jwks.json
/oauth/authorize
/oauth/token
/oauth/userinfo
```

OIDC rules:
- clients request the `openid` scope;
- Argos requires a `nonce` whenever `openid` is requested;
- Authorization Code + PKCE S256 remains mandatory;
- successful authorization-code exchanges receive an RS256-signed `id_token`;
- the OIDC `sub` is the immutable Argos UUID (`users.subject`), never the Laravel numeric user id;
- ID Tokens are audience-bound to the OAuth client id and short-lived (5 minutes);
- profile/email claims are only included when the matching scopes were granted;
- UserInfo is protected by Passport access tokens;
- JWKS publishes only the RSA public key and a deterministic `kid`.

The current implementation deliberately reuses Passport's RSA signing key pair so OAuth access-token signing and OIDC ID-token verification share one managed key lifecycle.

Before enabling OIDC in production:

```bash
php artisan migrate --force
php artisan passport:keys
php artisan optimize:clear
php artisan argos:doctor --strict
```

The next lot should focus on key rotation, refresh-token rotation/reuse detection, revocation and session/application management.


## Lot 3 — token security

Argos tracks refresh-token rotation using token families while Laravel Passport remains the cryptographic OAuth authority.

Security behaviour:
- only SHA-256 fingerprints of refresh tokens are stored by Argos;
- every authorization-code login starts a token family;
- every successful refresh marks the previous refresh token as used and records the replacement;
- reuse of a used/revoked refresh token revokes the complete family;
- associated Passport access and refresh tokens are revoked;
- pre-Lot-3 refresh tokens are accepted once and migrated into a tracked family after Passport validates them;
- users can revoke an application's active families from the Argos account page;
- users can terminate individual web sessions or all other sessions.

Signing key rotation:

```bash
php artisan argos:keys:rotate
```

The command archives the previous public key for JWKS verification, generates a fresh 4096-bit RSA pair, revokes active OAuth tokens, and forces clients to authenticate again. Private retired keys are never archived.

Because Passport validates access tokens against its current signing key, Argos intentionally treats signing-key rotation as a security boundary rather than trying to keep old access tokens alive.


## Lot 4 — account security

Argos owns end-user account security.

Available flows:
- forgotten-password e-mail and password reset;
- authenticated password change;
- e-mail verification;
- TOTP MFA compatible with standard authenticator applications;
- one-time recovery codes;
- MFA login challenge before the Argos web session is created;
- recent password confirmation for sensitive account actions.

Security rules:
- TOTP secrets are encrypted at rest with Laravel's application encryption key;
- recovery codes are never stored in plaintext, only password hashes;
- recovery codes are displayed once after generation;
- changing/resetting a password revokes other sessions and OAuth security contexts;
- enabling/disabling MFA revokes other sessions and OAuth contexts;
- sensitive operations require password confirmation within 15 minutes;
- password reset responses do not reveal whether an e-mail address exists;
- new passwords require at least 12 characters, mixed case and numbers.

Recommended production mail configuration is required for reset and verification notifications.


## Lot 5 — passkeys and security center

Argos supports passwordless WebAuthn/passkey authentication using the official `laravel/passkeys` server package.

Capabilities:
- passkey login from the Argos login screen;
- registration of Windows Hello, Touch ID / Face ID, phone and compatible hardware security keys;
- account-state authorization still enforced before a passkey login is accepted;
- passkey registration/deletion protected by recent password confirmation;
- passkey lifecycle events written to `security_events`;
- local browser WebAuthn client in `public/passkeys.js` — no CDN and no Node/Vite runtime dependency;
- recent security history displayed in the account;
- queued e-mail alerts for sensitive security changes and refresh-token reuse detection;
- previously unseen login IP + user-agent combinations create a `login.new_context` security event.

First install of this lot requires the new Composer dependency:

```bash
composer update laravel/passkeys --with-all-dependencies
php artisan migrate --force
php artisan argos:doctor --strict
```

After `composer.lock` has been regenerated and committed by the deployment environment, normal releases can return to `composer install --no-dev --optimize-autoloader`.

Recommended production requirements:
- HTTPS only;
- `APP_URL=https://id.airinter-va.org`;
- queue worker/cron active so security alert notifications are delivered;
- set a stable `PASSKEYS_USER_HANDLE_SECRET` and never rotate it casually.
