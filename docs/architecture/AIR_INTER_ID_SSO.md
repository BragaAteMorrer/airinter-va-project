# Air Inter ID — Prométhée SSO phase 1

This increment connects Prométhée to the standalone Air Inter ID OAuth2 service while preserving the existing phpVMS login as a rollback path.

## Product name

Public product name: **Air Inter ID**.

Use `airinter-id` for folders, services and configuration prefixes. Use `AII` only as an internal shorthand when a compact identifier is useful.

## Authentication flow

1. Prométhée redirects the browser to `https://id.airinter-va.org/oauth/authorize`.
2. Air Inter ID authenticates the member.
3. Air Inter ID redirects to `https://promethee.airinter-va.org/auth/airinter-id/callback` with an authorization code.
4. Prométhée exchanges the code server-to-server for an access token.
5. Prométhée calls `/api/v1/me`.
6. The returned legacy identity whose provider is `promethee` supplies the immutable phpVMS `users.id`.
7. Prométhée opens its normal Laravel session for that local user.

E-mail is never used as the cross-system primary key.

## Required Prométhée environment

```dotenv
AIRINTER_ID_ENABLED=true
AIRINTER_ID_URL=https://id.airinter-va.org
AIRINTER_ID_CLIENT_ID=<passport-client-id>
AIRINTER_ID_CLIENT_SECRET=<passport-client-secret>
AIRINTER_ID_REDIRECT_URI=https://promethee.airinter-va.org/auth/airinter-id/callback
```

Keep `AIRINTER_ID_ENABLED=false` until the Air Inter ID database, Passport keys and first-party clients are provisioned.

## Air Inter ID provisioning

From the Air Inter ID application:

```bash
php artisan migrate --force
php artisan passport:keys
php artisan airinter-id:import-promethee --force
php artisan airinter-id:configure-clients --show-secrets
```

Store the generated Prométhée client ID and secret in Prométhée's production `.env`. Never commit the secret.

## Transition rules

- Existing phpVMS authentication remains enabled during phase 1.
- Password changes are not moved to Air Inter ID yet.
- A failed/missing Air Inter ID link does not create a new phpVMS user automatically.
- Suspended/pending/rejected local users cannot bypass phpVMS state through SSO.
- No PIREP, rank, fleet, dispatch or finance data is copied into Air Inter ID.

## Next increments

1. Add the “Se connecter avec Air Inter ID” entry point to the active Prométhée theme/login screen.
2. Add Hermès Authorization Code + PKCE using the loopback callback.
3. Add the public `airinter-va.org` member client.
4. Once all accounts are linked and rollback has been tested, make Air Inter ID authoritative for password/e-mail/MFA.
5. Add OIDC discovery/JWKS/ID Tokens only after the OAuth2 migration is stable.
