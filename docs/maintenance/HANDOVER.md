# Maintainer Handover — Air Inter VA / Prométhée / Hermès

This document is the starting point for a developer taking over the project without prior context.

## Product map

| Component | Location | Responsibility |
| --- | --- | --- |
| phpVMS | `prometheus/` | Upstream VA engine, database models, finance, fleet and legacy administration |
| Prométhée | `prometheus/modules/Promethee/` | Air Inter OCC/product layer, pilot portal, operations API, SimBrief bridge, safety, economy, dispatch |
| Hermès | `acars/` | Official Air Inter desktop ACARS |
| Hermès tests | `acars.tests/` | Desktop/telemetry/recovery/API contract regression tests |
| Prométhée assets | `prometheus/public/promethee-assets/` | Air Inter visual system |
| CI | `.github/workflows/` | Repository hygiene, build/tests, phpVMS upstream watch and Hermès release pipeline |
| Maintenance docs | `docs/maintenance/` | Upgrade and handover procedures |

## Architecture rule

**phpVMS is an upstream dependency; Prométhée is the Air Inter product.**

New Air Inter behavior should be implemented in `modules/Promethee/` first. Avoid modifying phpVMS `app/`, `config/`, `resources/views/` or other upstream files unless there is no clean extension point.

The authoritative list of unavoidable core overrides is:
`prometheus/.phpvms-upstream.json`.

A file missing from that manifest must not silently become a new core override. If one is unavoidable, document the reason in the manifest in the same pull request.

## phpVMS upgrades

Read `docs/maintenance/PHPVMS_UPGRADES.md` before every upgrade.

Useful commands:

```bash
python tools/phpvms_upstream_audit.py --check-manifest
python tools/phpvms_upstream_audit.py --latest
python tools/phpvms_upstream_audit.py --target 7.0.11
```

The audit is read-only. It classifies new upstream changes into direct/safe changes and manual merge collisions.

The GitHub workflow `phpVMS Upstream Watch` runs weekly and fails when a newer stable phpVMS release is available. A failed watch is a maintenance notification, not a production outage.

## Hermès API ownership

Hermès-specific HTTP endpoints belong to Prométhée.

The authentication and SimBrief controllers live under:

```text
prometheus/modules/Promethee/Http/Api/
```

The routes are declared in:

```text
prometheus/modules/Promethee/routes.php
```

The stable desktop contract is `/api/v1`. Older `/api/acars` routes exist only as compatibility aliases and should not receive new product features.

Do not put Hermès controllers/routes back into phpVMS core.

## Database migrations

Prométhée migrations belong under:

```text
prometheus/modules/Promethee/Database/migrations/
```

Never edit an already-deployed migration to change production state. Create a new forward-only migration.

The ACARS token migration was moved from phpVMS core into Prométhée without renaming its migration file, so Laravel installations which have already executed it keep recognizing it as applied.

## Translations

### Prométhée

Prométhée uses the phpVMS/Laravel catalogues under `prometheus/resources/lang/`.
Run:

```bash
php artisan promethee:translations-check
```

### Hermès

Hermès UI catalogues live in:

```text
acars/wwwroot/i18n.js
```

Supported locales are currently:

```text
fr, en, pt, es, it, ja, tr, de
```

Run:

```bash
node tools/check_hermes_i18n.cjs
```

When adding a new translatable static label:
1. add a stable `data-i18n` key in the HTML;
2. add that key to the French reference catalogue;
3. add it to every supported locale;
4. run the checker.

Do not put translation dictionaries back into `app.js`.

## Local validation before a pull request

At minimum:

```bash
node --check acars/wwwroot/i18n.js
node --check acars/wwwroot/app.js
node tools/check_hermes_i18n.cjs
python -m py_compile tools/phpvms_upstream_audit.py
python tools/phpvms_upstream_audit.py --check-manifest
```

From `prometheus/`:

```bash
composer validate --no-check-publish
php -l modules/Promethee/routes.php
php -l modules/Promethee/Providers/PrometheeServiceProvider.php
```

On Windows / CI:

```powershell
dotnet restore acars.tests/Promethee.Acars.Tests.csproj
dotnet build acars/Promethee.Acars.csproj --configuration Release --framework net8.0-windows
dotnet test acars.tests/Promethee.Acars.Tests.csproj --configuration Release --framework net8.0
```

The GitHub `Quality` workflow is the final gate.

## Production deployment

Do not deploy by FTP-copying a random selection of changed PHP files.

Preferred sequence:
1. create/merge a tested pull request;
2. back up database and persistent files;
3. deploy the exact tested Git commit;
4. run Composer;
5. clear Laravel caches;
6. run migrations;
7. rebuild phpVMS caches;
8. smoke-test pilot and admin workflows.

Typical server commands from the phpVMS root:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan phpvms:caches
```

Use `/update` only for pending phpVMS migrations/seeds. It does not download application releases.

## Production data that must survive deployments

Never replace or delete without a verified backup:
- `.env`;
- database;
- `storage/` persistent/upload content;
- user-uploaded/download assets;
- host-specific SSL/webserver configuration outside Git;
- secrets/API keys stored in environment or database settings.

Secrets must never be committed.

## Release/rollback discipline

Every release should identify:
- Git commit deployed;
- phpVMS upstream version;
- database backup timestamp;
- migrations executed.

If a deployment fails before migrations: revert to the previous tested Git commit.

If incompatible migrations ran: restore the matching previous code **and** database backup. Do not improvise a partial downgrade in production.

## Critical smoke tests

After a phpVMS or Prométhée upgrade, verify:
- login/logout;
- new-account flow;
- Prométhée dashboard/navigation;
- pilot profile/timezone;
- flight search/reservation;
- fleet/maintenance;
- Hermès login;
- Hermès operations `/api/v1`;
- SimBrief company API and account import;
- telemetry reception;
- PIREP prefile/file;
- finances/economy;
- module activation;
- admin settings;
- translated UI.

## Where to make future changes

Prefer, in order:
1. `modules/Promethee/`;
2. Prométhée assets/translations;
3. a documented adapter/service owned by Prométhée;
4. only as a last resort, a phpVMS core override recorded in the upstream manifest.

That order is what keeps future phpVMS upgrades affordable.
