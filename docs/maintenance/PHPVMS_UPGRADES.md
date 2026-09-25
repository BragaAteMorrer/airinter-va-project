# Updating phpVMS under Prométhée

Prométhée is built on phpVMS, but the Air Inter product must remain maintainable even if its original authors are unavailable.

## Golden rules

1. **Never unzip a phpVMS release directly over production.**
2. **Never use `/update` as a file updater.** The phpVMS `/update` screen only runs migrations/data migrations already present on disk.
3. Keep Air Inter features inside `modules/Promethee/` whenever possible.
4. Any unavoidable phpVMS-core edit must be small, documented in `prometheus/.phpvms-upstream.json`, and covered by CI.
5. Upgrade through a branch + pull request, then deploy the tested commit.

## Current upstream baseline

The authoritative baseline is `prometheus/.phpvms-upstream.json`.

It records:
- phpVMS release/tag;
- upstream commit SHA;
- official release ZIP SHA256;
- known core overrides;
- Air Inter-owned extensions that live inside phpVMS paths.

Do not infer the version from `config/app.php`; phpVMS keeps a generic framework value there. Runtime versioning is controlled by `config/version.yml`.

## Checking for a future release

From the repository root:

```bash
python tools/phpvms_upstream_audit.py --latest
```

Or for a specific release:

```bash
python tools/phpvms_upstream_audit.py --target 7.0.11
```

The audit downloads the pinned release and the target release, verifies the pinned official SHA256, compares upstream files, and classifies target changes:

- **SAFE UPSTREAM CHANGES**: the local file still matches our pinned upstream baseline and may normally be replaced by the target version;
- **MANUAL MERGE REQUIRED**: Air Inter changed the same file, so keep our behavior while applying upstream's changes manually.

The script does **not** modify the repository.

## Upgrade procedure

1. Back up production before touching code:
   - database;
   - `.env`;
   - `storage/`;
   - uploaded files and any hosting-specific configuration.
2. Create a branch such as `chore/phpvms-7.0.11`.
3. Run the upstream audit against the target release.
4. Apply safe source changes from upstream.
5. Manually merge every collision. Prefer moving Air Inter logic into `modules/Promethee/` instead of increasing the core override.
6. Update `prometheus/.phpvms-upstream.json` with the new release metadata and official SHA256.
7. Update `prometheus/config/version.yml`.
8. Run CI and local checks.
9. Deploy the tested commit.
10. On the server, from the phpVMS/Prométhée directory:

```bash
composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan phpvms:caches
```

11. Open `/update` only if phpVMS still reports pending migrations/seeds. It is a migration runner, not a downloader.
12. Smoke-test:
    - login and registration;
    - Prométhée dashboard;
    - flight search/reservation;
    - Hermès authentication;
    - Hermès `/api/v1` operations;
    - SimBrief create/import;
    - PIREP filing;
    - admin modules/settings/finances.

## Rollback

Do not attempt a partial rollback by copying individual PHP files on production.

Restore the previous tested Git commit and, if the new release executed destructive/incompatible migrations, restore the matching database backup. Keep code and database snapshots paired.

## Remaining intentional core surface

The long-term target is **zero product logic in phpVMS core**.

At the 7.0.10 baseline, the main remaining overrides are documented in the manifest. In particular:
- legacy phpVMS pages are isolated below `/legacy`;
- the legacy flight/profile controllers retain a small Air Inter bridge;
- flight/PIREP display identifiers have Air Inter formatting;
- the SimBrief persistence service still contains product integration logic.

Hermès session and SimBrief API controllers were moved into `modules/Promethee/` during the 7.0.10 upgrade, so future phpVMS route upgrades no longer need to understand Hermès.

When touching one of the remaining overrides, ask first: **can this behavior be implemented in Prométhée instead?** If yes, move it.
