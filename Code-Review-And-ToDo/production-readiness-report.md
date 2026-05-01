# Production Readiness Report

Started: 2026-05-01
Status: In progress

## Acceptance Criteria

- [ ] All canonical tests are green.
- [ ] Framework PHPStan is green.
- [ ] Component PHPStan is green for every component under `components/`.
- [ ] Broken reference audit has no unresolved internal Avax/component references.
- [ ] Documentation checks are green.
- [ ] Forbidden folder and naming checks are green.
- [ ] Superglobal boundary audit is green.
- [ ] PHP-CS-Fixer dry-run is green for the committed scope.
- [ ] `.agents/management/TODO.md`, `.agents/management/BUGS.md`, and `.agents/management/ACTIVE.md` are synchronized.
- [ ] Every changed component has relevant tests or a documented reason.

## Current Baseline

Known green gates from the latest framework pass:

- `./vendor/bin/phpunit --no-coverage`
- `./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw`
- `php tooling/docs/validate-docs.php`
- `php tooling/docs/validate-docs-mirror-source.php`
- `php tooling/architecture/check-forbidden-folders.php`
- `php tooling/check-superglobals.php`

Known blockers:

- `components/` PHPStan is not green.
- `php tooling/audit_broken_refs.php` still reports unresolved internal references.
- PHP-CS-Fixer dry-run reports broad repository style drift.
- Management TODO/BUG/ACTIVE lists are not synchronized yet.

## Work Log

### 2026-05-01 - Application Cache

Status: In progress

Commands:

```bash
./vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
```

Findings:

- Public cache facade and compiled cache facade still use old named arguments.
- `AvaxCache` still calls cache-store contracts with old argument names.
- `CacheResult` redeclares promoted readonly properties.
- Several cache tests use PHPUnit named arguments, which PHPStan rejects because PHPUnit marks those APIs as
  no-named-arguments.

