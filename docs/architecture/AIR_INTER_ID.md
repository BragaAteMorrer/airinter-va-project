# Air Inter ID — ecosystem architecture

## Domain

`id.airinter-va.org` is the canonical identity authority.

It is a separate application and database. No phpVMS core dependency is allowed inside it.

## System ownership

| Domain data | Owner |
| --- | --- |
| credentials / password / MFA / sessions | Air Inter ID |
| stable person subject | Air Inter ID |
| OAuth clients and grants | Air Inter ID |
| pilot number / IT ident | Prométhée/phpVMS |
| rank / awards / hours / PIREPs | Prométhée/phpVMS |
| reservations / dispatch / fleet | Prométhée |
| ACARS local state / telemetry capture | Hermès |
| historical/editorial archive | airinter-va.org |

## Identity key

Every person receives an immutable random UUID in `airinter_id.users.subject`.

External systems store/link that subject. They do not derive it from:
- e-mail;
- pilot id;
- callsign;
- name.

This permits e-mail changes, airline/division changes and future account merges without breaking history.

## Existing users

The initial import uses:
- phpVMS `users.id` as the immutable legacy link;
- current password hash for continuity;
- phpVMS state to initialize SSO eligibility;
- pilot ident as a login alias only.

All operational tables stay untouched.

## Future registration

Long-term registration starts at Air Inter ID:

1. person creates/verifies identity;
2. Air Inter ID creates the immutable subject;
3. Prométhée receives a signed provisioning request;
4. phpVMS creates the pilot record and assigns the IT identifier;
5. Prométhée returns `user_id` / ident;
6. Air Inter ID records the link.

Approval remains an operational Prométhée responsibility unless policy changes later.

## Client flows

### Web applications
Authorization Code grant with confidential client credentials.

### Hermès
Authorization Code + PKCE as a public/native client. Login happens in the system browser; Hermès never receives or stores the pilot password.

### Service-to-service
Use dedicated machine credentials/scopes, not user tokens.

## Token policy

Initial policy:
- access token: 1 hour;
- refresh token: 30 days;
- revocation on account suspension/password security action;
- minimum scopes per client.

Do not use long-lived bearer tokens as desktop credentials.

## OIDC target

The database/API contract is OIDC-ready, but OAuth2 and OIDC are not the same protocol.

OIDC is considered complete only when Air Inter ID supports and tests:
- Authorization Code + PKCE;
- signed ID Tokens;
- issuer/audience/nonce validation;
- JWKS rotation;
- discovery metadata;
- standard UserInfo;
- logout/session behavior.

Until then clients must use the OAuth access token plus `/api/v1/me`.

## Migration safety

The transition is reversible:
- phpVMS passwords remain in place initially;
- current Prométhée/Hermès login stays available during pilot rollout;
- Air Inter ID import is additive;
- identity links can be rebuilt from phpVMS `users.id`;
- no operational history is moved.

Only after adoption is verified should password management be disabled in legacy applications.
