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

### 2026-05-01 - Component Completion Pass

Status: Partial hard-fail closure complete; full production readiness is still blocked by unavailable command execution.

Scope:

- `Identity/Access`
- `Identity/Security`
- `Identity/Tokens`
- `Identity/Auth`
- `HTTP/ApiVersioning`
- `HTTP/Request`
- `HTTP/Response`
- `DeveloperTools/CodeGeneration`
- `DeveloperTools/Diagnostics`
- `DataStack/Data`
- `DataStack/Database`
- `Operations/ApplicationWorkflow`
- `Operations/Events`
- `CLI/Console`

Closed hard-fail signals:

- Removed all runtime `NotImplementedException` / "not yet implemented" component paths found by text audit.
- Added real admin elevation state and authorization checking for `Identity/Access`.
- Added pending/approve/apply security change behavior for `Identity/Security`.
- Added real authorization-code, exchange, introspection, and revocation behavior for `Identity/Tokens`.
- Replaced the base64-only auth token codec and always-false token store with HMAC token encoding and revocation
  tracking.
- Added missing API version registry and resolver capabilities for `HTTP/ApiVersioning`.
- Replaced incomplete PSR upload/header behavior in `HTTP/Request`.
- Added missing request body parsers, runtime request creation, and request builder behavior.
- Replaced code-generation TODO stubs and forbidden `Services` generation with capability/action generation.
- Corrected `DeveloperTools/Diagnostics` namespace ownership and removed fake database/cache readiness results.
- Added concrete classes to previously empty component folders:
  `CLI/Console/System/Configuration`, `CLI/Console/System/Foundation`,
  `HTTP/Response/System/Capabilities/Streaming`,
  `Identity/Security/System/Flows/Encrypt`,
  `Identity/Security/System/Flows/Decrypt`,
  `Identity/Security/System/Foundation/Exceptions`,
  `Identity/Tokens/System/Foundation`,
  `Operations/ApplicationWorkflow/System/Capabilities/FailureRecording`,
  `Operations/Events/System/Flows/SubscribeToEvent`.

Verification completed before environment execution limit:

```bash
./vendor/bin/phpunit tests/Unit/Components/Identity/Access/AccessPublicSurfaceTest.php tests/Unit/Components/Identity/Security/SecurityChangeWorkflowTest.php tests/Unit/Components/Identity/Tokens/TokensPublicSurfaceTest.php tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php tests/Unit/Components/HTTP/Request/ServerRequestTest.php
```

Result:

- `OK (7 tests, 22 assertions)`

Verification blocked after additional Auth/Diagnostics/Request completion:

- A later `./vendor/bin/phpunit ...` run was rejected by the execution environment due the Codex usage limit.
- The next required command is:

```bash
./vendor/bin/phpunit tests/Unit/Components/Identity/Auth/AuthTokenCapabilityTest.php tests/Unit/Components/DeveloperTools/Diagnostics/HealthCheckTest.php tests/Unit/Components/Identity/Access/AccessPublicSurfaceTest.php tests/Unit/Components/Identity/Security/SecurityChangeWorkflowTest.php tests/Unit/Components/Identity/Tokens/TokensPublicSurfaceTest.php tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php tests/Unit/Components/HTTP/Request/ServerRequestTest.php
```

Current text-audit status:

- No empty directories remain under `components/`.
- No matches remain for:
  `Simple placeholder`, `TODO: Implement`, `NotImplementedException`, `not yet implemented`, `Not implemented`,
  `// Implementation`, `new Request(...)`, `Placeholder for`.

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
