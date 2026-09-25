# Air Inter ID

Standalone identity and SSO service for the Air Inter VA ecosystem.

**Production target:** `https://id.airinter-va.org`

Runbook cPanel : [`docs/maintenance/AIR_INTER_ID_CPANEL.md`](../docs/maintenance/AIR_INTER_ID_CPANEL.md)

Air Inter ID is deliberately separate from phpVMS/Prométhée. It owns authentication and security identity; Prométhée remains the source of truth for operational pilot data.

## Responsibilities

Air Inter ID owns:
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
- creates an Air Inter ID account for each phpVMS user;
- copies the existing Laravel password hash for newly imported users;
- creates a stable UUID subject;
- records the immutable phpVMS `user_id` link;
- does not move or delete operational data.

Existing Air Inter ID passwords are not overwritten on later syncs unless:

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

Use the system browser for Air Inter ID login. V1 callback:

```text
http://127.0.0.1:47821/callback
```

Hermès starts a temporary loopback listener on that fixed local port, generates `state`, `code_verifier` and `code_challenge`, launches the browser to Air Inter ID, receives the authorization code locally, then exchanges it for access/refresh tokens. The port can later become configurable once the server/client redirect contract evolves together.

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

It should use Air Inter ID only for member identity. Historical/editorial data stays on the site.

## Transition strategy

### Phase 0 — shadow identity
Import users and links. Existing logins keep working everywhere.

### Phase 1 — Prométhée SSO
Add “Se connecter avec Air Inter ID”. Link by immutable phpVMS user id, never only by e-mail.

### Phase 2 — Hermès PKCE
Move Hermès from password submission to browser-based Authorization Code + PKCE.

### Phase 3 — Air Inter VA website
Add member SSO without migrating the museum/editorial database.

### Phase 4 — authority switch
Air Inter ID becomes the only place to change password, e-mail, MFA and security settings. Prométhée consumes identity claims but keeps operational fields.

### Phase 5 — OIDC
Add a vetted OIDC provider implementation with signed ID Tokens, JWKS, discovery, nonce validation and standard UserInfo. Keep the existing UUID subject.

## Non-negotiable rules

- Never use e-mail as the permanent cross-system primary key.
- Never copy PIREPs/ranks/badges into Air Inter ID.
- Never embed a confidential OAuth secret in Hermès.
- Never let the historical website write directly into Prométhée's operational tables.
- Never remove legacy authentication before every existing pilot has an Air Inter ID link.
- Keep rollback possible throughout the migration.
