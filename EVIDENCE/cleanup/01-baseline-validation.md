# Stage A Baseline Validation

Date: 2026-05-13
Program: AvaX Full Enterprise Cleanup Program
Stage: Stage A - Validation Baseline Closure
Status: RED_BASELINE

No remediation was performed before this baseline was captured.

## Worktree Counts

| Metric                                                                  |                          Count |
|-------------------------------------------------------------------------|-------------------------------:|
| Dirty worktree entries before cleanup evidence                          |                             12 |
| Dirty worktree entries after cleanup evidence files were created        | 13 (`EVIDENCE/cleanup/` added) |
| Tracked files                                                           |                           6651 |
| Visible non-git files                                                   |                           6604 |
| Repository files excluding `.git`                                       |                          37692 |
| PHP files in framework/components/tests/examples/labs/tooling           |                           4210 |
| Files in framework/components/tests/examples/labs/tooling/docs/EVIDENCE |                           6231 |

## Baseline Commands

| Command                                                                                        | Result | Evidence                                                     |
|------------------------------------------------------------------------------------------------|--------|--------------------------------------------------------------|
| `git status --short`                                                                           | DIRTY  | 12 existing dirty entries plus new `EVIDENCE/cleanup/`       |
| `git branch --show-current`                                                                    | `main` | command output                                               |
| `git log -20 --oneline`                                                                        | PASS   | head `fc499a9e5`                                             |
| `composer validate --no-check-publish`                                                         | GREEN  | `./composer.json is valid`                                   |
| `composer dump-autoload -o`                                                                    | GREEN  | `Generated optimized autoload files containing 9272 classes` |
| `vendor/bin/phpunit --no-coverage`                                                             | GREEN  | `OK (8289 tests, 23805 assertions)`                          |
| `vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G` | RED    | 25 errors                                                    |

Composer/PHP/PHPUnit/PHPStan commands require the local Docker-backed PHP environment in this workspace. Direct sandbox
runs failed with `permission denied while trying to connect to the docker API at unix:///var/run/docker.sock`, then the
same commands were rerun with sandbox escalation.

## PHPStan Error Inventory

Current PHPStan result: 25 errors.

| Area                                                                                            | Errors | Blocks cleanup? | Notes                                                                                |
|-------------------------------------------------------------------------------------------------|-------:|----------------:|--------------------------------------------------------------------------------------|
| `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/FileCacheStore.php` |      1 |             YES | Dirty worktree introduced readonly property assignment outside constructor           |
| `components/Application/Container/System/Capabilities/Composition/Assembly/AssembleRuntime.php` |      1 |             YES | Dirty worktree constructor call missing `CreateDependencyBlueprint::$blueprintCache` |
| `components/Application/Container/System/Capabilities/ResolveCallable/ResolveCallable.php`      |      1 |             YES | Strict comparison always true                                                        |
| `components/DataStack/Database/System/Capabilities/ORM/Repositories/EntityRepository.php`       |      1 |             YES | Generic return type mismatch                                                         |
| `components/HTTP/Dispatcher/System/Capabilities/ActionResolution/ControllerResolver.php`        |      1 |             YES | `resolve()` declares `object`, returns `callable`                                    |
| `components/HTTP/Router/System/PublicSurface/Router.php`                                        |      1 |             YES | `is_callable()` always true                                                          |
| `components/Identity/Auth/System/Capabilities/Identity/UserSource/InMemoryUserSource.php`       |      8 |             YES | Accesses now-private `User::$roles` and `User::$permissions`                         |
| `framework/System/Capabilities/Queue/Configuration/QueueServiceProvider.php`                    |      6 |             YES | Missing `QueueBrokerInterface`; invalid service provider types                       |
| `framework/System/Capabilities/Queue/RegisterQueueCommands.php`                                 |      5 |             YES | Mixed worker, callable/object mismatch, missing failed-job methods                   |

## Gate Results

| Gate                                                                | Result            | Evidence                                                     |
|---------------------------------------------------------------------|-------------------|--------------------------------------------------------------|
| `php tooling/security/check-security-blockers.php`                  | PASS              | Security blockers check passed                               |
| `php tooling/governance/check-component-adoption.php`               | FAIL              | Raw file gate reports 4 `MIGRATE_TO_FILESYSTEM` violations   |
| `php tooling/refactor/check-component-canonical-shape.php`          | PASS              | All components follow canonical shape                        |
| `php tooling/refactor/check-namespace-drift.php`                    | PASS              | PASS                                                         |
| `php tooling/refactor/check-public-surface.php`                     | PASS              | PASS                                                         |
| `php tooling/refactor/check-runtime-leaks.php`                      | PASS              | PASS                                                         |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php` | PASS              | No forbidden pattern folders                                 |
| `php tooling/refactor/check-component-suite-structure.php`          | PASS              | PASS                                                         |
| `php tooling/refactor/check-duplicate-owners.php`                   | PASS              | PASS                                                         |
| `php tooling/refactor/check-raw-file-operations.php`                | FAIL              | 4 MUST FIX, 16 NEEDS DESIGN DECISION                         |
| `php tooling/audit_broken_refs.php`                                 | RED_BY_CONTENT    | Exit 0, but reports 20 missing symbols, including 6 CRITICAL |
| `php avax runtime:doctor`                                           | PASS              | No runtime safety issues detected                            |
| `php tooling/governance/check-governance-index-current.php`         | PASS              | Governance index current                                     |
| `php tooling/governance/check-stage-lock.php`                       | YELLOW_BY_CONTENT | Exit 0, but Active Stage is UNKNOWN                          |
| `php tooling/security/check-security-naming.php`                    | PASS              | No security naming violations                                |
| `php tooling/performance/check-performance-naming.php`              | YELLOW_BY_CONTENT | Exit 0 with 19 `sleep()` warnings                            |
| `php tooling/refactor/check-forbidden-folders.php`                  | PASS              | PASS                                                         |
| `php tooling/check-superglobals.php`                                | PASS              | No unauthorized superglobal usage                            |
| `php tooling/refactor/check-failure-boundary-adoption.php`          | PASS              | 11 passed, 0 failed                                          |

## FailureBoundary Gates

| Gate                                                         | Result |
|--------------------------------------------------------------|--------|
| `php tooling/failure-boundary/check-attributes-compiled.php` | PASS   |
| `php tooling/failure-boundary/check-dogfooding.php`          | PASS   |
| `php tooling/failure-boundary/check-local-try-catch.php`     | PASS   |

## Events Gates

All Events gates passed:

- `check-canonical-event-owner.php`
- `check-compiled-listener-registry.php`
- `check-dispatch-runtime.php`
- `check-event-emission-api.php`
- `check-event-sourcing-not-default.php`
- `check-events-no-hot-path-reflection.php`
- `check-fluent-dsl-registration.php`
- `check-listens-to-attribute.php`
- `check-psr14-interop.php`
- `check-real-dogfooding.php`

## Database Lifecycle Gates

All database lifecycle gates passed:

- `gate-1-entity-lifecycle-wiring.php`
- `gate-2-query-lifecycle-wiring.php`
- `gate-3-transaction-lifecycle-wiring.php`
- `gate-4-compiled-registry-frozen.php` (PASS with warning: no dedicated registry test file found)
- `gate-5-dsl-acceptance.php`
- `gate-6-lifecycle-integration-tests.php`
- `gate-7-database-component-shape.php`
- `gate-8-phpstan-clean.php`

## Component Maturity Gates

| Gate                                                              | Result            | Notes                                                                               |
|-------------------------------------------------------------------|-------------------|-------------------------------------------------------------------------------------|
| `php tooling/components/check-component-status-lock.php`          | PASS              | 30 component statuses validated                                                     |
| `php tooling/components/check-no-unclassified-scaffolding.php`    | RED               | PHP parse error on line 19                                                          |
| `php tooling/components/check-hollow-public-surfaces.php`         | PASS              | 229 files checked                                                                   |
| `php tooling/components/check-component-runtime-assembly.php`     | YELLOW_BY_CONTENT | Exit 0 but reports `0 active flow/surface files scanned`; gate likely ineffective   |
| `php tooling/components/check-component-static-state-safety.php`  | PASS              | 31 static state holders checked                                                     |
| `php tooling/components/check-component-health-doctor-policy.php` | YELLOW_BY_CONTENT | Exit 0 but reports `0 runtime-critical components checked`; gate likely ineffective |
| `php tooling/components/check-component-behavior-proof-map.php`   | PASS              | 14 ACTIVE_GREEN components checked                                                  |
| `php tooling/components/check-component-docs-status-policy.php`   | PASS              | 1 component checked                                                                 |

## Planned / Not Implemented Gates

The following requested gates were searched and not found:

- `tooling/runtime/check-callable-resolution.php`
- `tooling/governance/check-truth-consistency.php`
- `tooling/refactor/check-empty-production-classes.php`

Status: PLANNED / NOT IMPLEMENTED.

## Raw File Gate Details

`php tooling/refactor/check-raw-file-operations.php` reported:

- `MIGRATE_TO_FILESYSTEM`: 4 MUST FIX
- `MIGRATE_TO_STORAGE`: 0
- `NEEDS_DESIGN_DECISION`: 16

MUST FIX files:

- `components/Operations/Logging/System/Capabilities/Writers/RotatingFileWriter.php:74`
-
`components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:134`
- `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CompiledCacheSource.php:26`
- `components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CompiledCacheSource.php:37`

## Broken Reference Audit Details

`php tooling/audit_broken_refs.php` exited 0 but reported missing references:

- 20 missing symbols.
- 6 CRITICAL.
- 14 MINOR.

Important current-root item:

- `Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker\QueueBrokerInterface` missing in
  `framework/System/Capabilities/Queue/Configuration/QueueServiceProvider.php`.

Many other findings are under `.qoder/worktrees/**`; these must be classified before final GREEN because the cleanup
program includes worktree files/counts.

## Baseline Decision

Stage A baseline is RED.

Immediate remediation is allowed only for Stage A blockers:

- PHPStan errors.
- Raw file MUST FIX violations.
- Broken references that affect active current-root code.
- Broken or ineffective maturity gates.
- Missing planned gates if required for Stage A closure.

V5.9 remains BLOCKED.

