Da. Na osnovu novog dump-a, sledeći ToDo treba malo korigovati. Imaš napredak: `composer` i `autoload` su sada GREEN, PSR-4 production skipovi su rešeni, runtime doctor prolazi. Ali `CURRENT_TRUTH.md` sada ima jednu nelogičnost: piše `Broken refs: GREEN (49 CRITICAL)`. To ne sme biti GREEN dok god postoji 49 critical referenci. To je minimum YELLOW, a realno RED/YELLOW dok se ne dokaže da su false-positive ili test-only. Takođe, V1 nije dokazan, V2 i V3 ostaju zaključani. 

Evo AI TODO za dalje. Kopiraj direktno agentu.

```md
# AvaX AI TODO — V1 Static Integrity Recovery

## Current Truth

Composer: GREEN  
Autoload: GREEN  
Production PSR-4 skips: GREEN  
Runtime doctor: GREEN  
Component suite checker: PASS  
Duplicate owner checker: PASS  
Namespace drift checker: PASS  
Public surface checker: PASS  
Runtime leak checker: PASS  

V1 Kernel Green: NOT PROVEN  
V2 Implementation: LOCKED  
V3 Implementation: LOCKED  

Known blockers:
- Broken references still show 49 CRITICAL references.
- PHPStan is RED with about 12.8k errors.
- Tests are RED in practice because only 12 tests pass for a huge codebase.
- V1 Kernel Green is not proven.
- V2/V3 implementation remains forbidden.

Important correction:
`Broken refs: GREEN (49 CRITICAL)` is internally inconsistent. If 49 CRITICAL refs remain, broken refs are not GREEN. Treat them as YELLOW/RED until proven false-positive, test-only, or fully fixed.

Do not implement V2.
Do not implement V3.
Do not add features.
Do not broadly refactor.
Do not move components.
Do not create placeholder classes.
Do not create dummy compatibility classes.
Do not weaken production types.
```

## Stage 1 — Broken Reference Closure Pass

```md
## Goal

Close the remaining 49 CRITICAL broken references.

Target:
- 49 CRITICAL -> 0 unresolved CRITICAL
or
- every remaining CRITICAL reference is explicitly classified as false-positive / test-only / docs-only / requires-human-decision.

Input:
- CURRENT_TRUTH.md
- Code-Review-And-ToDo/v1-integrity/broken-reference-groups.md
- Code-Review-And-ToDo/v1-integrity/critical-broken-reference-repair-plan.md
- Code-Review-And-ToDo/v1-integrity/critical-broken-reference-repair-report.md
- Code-Review-And-ToDo/v1-integrity/compatibility-bridge-map.md

Output:
- Code-Review-And-ToDo/v1-integrity/final-critical-broken-reference-closure-report.md
```

Tasks:

```md
[ ] Re-run broken reference audit.
[ ] Confirm exact count of remaining CRITICAL refs.
[ ] List every remaining CRITICAL symbol.
[ ] For each remaining CRITICAL ref, classify it as:
    - production-stale-reference
    - test-only-postpone
    - docs-only-stale-reference
    - external-vendor-missing
    - compatibility-bridge-needed
    - false-positive
    - requires-human-decision

[ ] Fix production-stale-reference refs by canonical namespace rewrite.
[ ] Do not create compatibility bridge for internal-only classes.
[ ] If public bridge is required, document it in compatibility-bridge-map.md.
[ ] If vendor class is missing, check composer first before changing code.
[ ] If ref belongs only to tests, classify and postpone to Test Layer Repair.
[ ] If ref belongs only to docs/examples, classify separately and do not block production refs.
```

Commands:

```bash
composer validate --no-check-publish
composer dump-autoload -o

php tooling/audit_broken_refs.php
php tooling/refactor/categorize-broken-refs.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
```

Acceptance:

```md
[ ] composer validate passes.
[ ] composer dump-autoload -o passes.
[ ] no production PSR-4 skips.
[ ] namespace drift checker passes.
[ ] duplicate owner checker passes.
[ ] public surface checker passes.
[ ] runtime leak checker passes.
[ ] 0 unresolved production-critical broken refs.
[ ] remaining test/doc/example refs are classified and do not pretend to be production GREEN.
[ ] CURRENT_TRUTH.md is updated honestly.
```

Final response format:

```text
Stage: Broken Reference Closure Pass
Status: GREEN / YELLOW / RED

Broken refs before:
- total:
- critical:

Broken refs after:
- total:
- critical:
- unresolved production-critical:

Files changed:
- ...

Commands run:
- ...

Remaining refs:
- ...

Decision:
- Broken refs status: GREEN / YELLOW / RED

Next allowed action:
- ...
```

## Stage 2 — PHPStan Baseline Reality Pass

Do this only after Stage 1.

```md
## Goal

Create a clean PHPStan battle map after broken refs are controlled.

Do not fix all PHPStan errors yet.
First classify accurately.

Input:
- Code-Review-And-ToDo/v1-integrity/phpstan-error-groups.md
- Code-Review-And-ToDo/v1-integrity/phpstan-full-raw.txt
- final broken reference closure report

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-battle-map.md
- Code-Review-And-ToDo/v1-integrity/phpstan-full-after-broken-ref-closure.txt
```

Commands:

```bash
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress \
  > Code-Review-And-ToDo/v1-integrity/phpstan-full-after-broken-ref-closure.txt || true

php tooling/refactor/categorize-phpstan-errors.php
```

Group errors by:

```md
[ ] unknown class
[ ] unknown method
[ ] unknown property
[ ] stale namespace/import
[ ] wrong named argument
[ ] wrong constructor call
[ ] wrong return type
[ ] wrong parameter type
[ ] nullable mismatch
[ ] PHPDoc/generic drift
[ ] multi-class file issue
[ ] test double mismatch
[ ] real production bug
```

Also group by owner:

```md
[ ] framework/System
[ ] components/Application/Cache
[ ] components/Application/Container
[ ] components/HTTP/Request
[ ] components/HTTP/Response
[ ] components/DataStack/Data
[ ] components/DataStack/Database
[ ] components/Operations/Resilience
[ ] components/Operations/MessageBus
[ ] components/Operations/Queue
[ ] tests
```

Acceptance:

```md
[ ] PHPStan error count is recorded.
[ ] Top 20 error families are recorded.
[ ] Production errors are separated from test errors.
[ ] Unknown-class errors are separated from type-quality errors.
[ ] First 5 component repair targets are chosen.
```

## Stage 3 — Multi-Class File Split Pass

I would do this early because the dump clearly shows at least one file with multiple classes, for example `QueryEntry` and `QueryTimeline` living together in the same `QueryTimeline.php` snippet. That is legal PHP, but it is bad for PSR-4 clarity, autoload sanity, and PHPStan repair. 

```md
## Goal

Detect and split production files containing multiple top-level classes/interfaces/enums/traits.

Do not change behavior.
Only split files and update namespaces/imports if needed.

Output:
- Code-Review-And-ToDo/v1-integrity/multi-class-file-split-report.md
```

Create script if missing:

```text
tooling/refactor/audit-multi-class-files.php
```

Script should detect:

```md
[ ] multiple class declarations
[ ] class + interface in same file
[ ] class + enum in same file
[ ] class + trait in same file
[ ] mismatched filename vs primary class
```

Commands:

```bash
php tooling/refactor/audit-multi-class-files.php
composer dump-autoload -o
vendor/bin/phpstan analyse components/DataStack/Database --memory-limit=1G --error-format=raw --no-progress
```

Acceptance:

```md
[ ] all production multi-class files are listed.
[ ] high-impact multi-class files are split.
[ ] QueryEntry and QueryTimeline are separate files if still combined.
[ ] composer dump-autoload -o still passes.
[ ] focused PHPStan does not get worse.
```

## Stage 4 — Component-Scoped PHPStan Repair: framework/System

```md
## Goal

Make framework/System type-clean before component-wide cleanup.

Reason:
framework/System owns runtime lifecycle. If this layer is dirty, V1 Kernel Green cannot be trusted.

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-framework-system-report.md
```

Commands:

```bash
vendor/bin/phpstan analyse framework/System --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-runtime-leaks.php
php avax runtime:doctor
```

Repair only:

```md
[ ] wrong imports
[ ] wrong constructor calls
[ ] wrong named args
[ ] obvious type errors
[ ] stale references
```

Do not:

```md
[ ] change public framework API casually
[ ] add runtime globals
[ ] hide state leaks
[ ] broaden return types to mixed
```

Acceptance:

```md
[ ] framework/System PHPStan passes or remaining issues are classified.
[ ] runtime doctor passes.
[ ] runtime leak checker passes.
```

## Stage 5 — Component-Scoped PHPStan Repair: Application/Cache

```md
## Goal

Make Application/Cache clean because it was recently touched for namespace repair.

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-application-cache-report.md
```

Commands:

```bash
vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpunit tests/Unit/Components/Application/Cache --no-coverage || true
```

Focus:

```md
[ ] stale Avax\Cache references
[ ] wrong named arguments
[ ] facade call drift
[ ] compiled cache contract drift
[ ] readonly promoted property redeclaration
[ ] PHPUnit named-argument issues in cache tests
```

Acceptance:

```md
[ ] Application/Cache PHPStan passes.
[ ] No old Avax\Cache production namespace remains.
[ ] Cache tests pass if present.
[ ] If tests are missing, test gap is recorded.
```

## Stage 6 — Component-Scoped PHPStan Repair: HTTP Request/Response

```md
## Goal

Make request/response stable because they are kernel-path components.

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-http-request-response-report.md
```

Commands:

```bash
vendor/bin/phpstan analyse components/HTTP/Request components/HTTP/Response --memory-limit=1G --error-format=raw --no-progress
```

Focus:

```md
[ ] ServerRequest construction
[ ] Request immutability
[ ] Header normalization
[ ] body parsing
[ ] uploaded files
[ ] ResponseFactory canonical class
[ ] stream/body handling
[ ] emitter types
```

Acceptance:

```md
[ ] HTTP/Request PHPStan passes.
[ ] HTTP/Response PHPStan passes.
[ ] old Avax\HTTP\Response\ResponseFactory is either gone or documented as compatibility bridge.
```

## Stage 7 — Component-Scoped PHPStan Repair: DataStack/Database

```md
## Goal

Make database core analyzable.

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-datastack-database-report.md
```

Commands:

```bash
vendor/bin/phpstan analyse components/DataStack/Database --memory-limit=1G --error-format=raw --no-progress
```

Focus:

```md
[ ] multi-class files
[ ] query grammar types
[ ] query timeline types
[ ] connection contracts
[ ] transaction types
[ ] migration classes
[ ] observability value objects
```

Acceptance:

```md
[ ] DataStack/Database PHPStan passes or remaining errors are classified by family.
[ ] no DataLayer/DataFoundation resurrection.
[ ] no broad rewrite.
```

## Stage 8 — Component-Scoped PHPStan Repair: Operations Core

```md
## Goal

Clean existing runtime muscles before V2 engine work.

Output:
- Code-Review-And-ToDo/v1-integrity/phpstan-operations-core-report.md
```

Commands:

```bash
vendor/bin/phpstan analyse \
  components/Operations/Resilience \
  components/Operations/MessageBus \
  components/Operations/Queue \
  components/Operations/ApplicationWorkflow \
  components/Operations/Observability \
  --memory-limit=1G --error-format=raw --no-progress
```

Focus:

```md
[ ] Retry
[ ] Timeout
[ ] CircuitBreaker
[ ] Idempotency
[ ] MessageBus
[ ] Queue
[ ] Saga
[ ] Compensation
[ ] Observability context
```

Acceptance:

```md
[ ] Operations core PHPStan passes or remaining errors are classified.
[ ] no V2 Reliability Engine expansion.
[ ] no new features.
```

## Stage 9 — Test Reality Plan, not full test writing yet

```md
## Goal

Turn “12 tests pass” into an honest V1 proof plan.

Output:
- Code-Review-And-ToDo/v1-integrity/v1-test-expansion-plan.md
```

Tasks:

```md
[ ] Count PublicSurface classes.
[ ] Count Kernel flow classes.
[ ] Count architecture checker scripts.
[ ] Map existing tests to PublicSurface classes.
[ ] Map existing tests to framework/System flows.
[ ] Map existing tests to architecture checkers.
[ ] Mark missing V1 proof tests.
```

Required first tests to plan:

```text
tests/Feature/Framework/BootApplicationFeatureTest.php
tests/Feature/Framework/HandleIncomingHttpFeatureTest.php
tests/Feature/Framework/RunConsoleCommandFeatureTest.php
tests/Feature/Framework/RequestScopeIsolationFeatureTest.php
tests/Feature/Framework/WorkerStateResetFeatureTest.php

tests/PublicApi/FrameworkPublicApiTest.php
tests/PublicApi/ContainerPublicApiTest.php
tests/PublicApi/CachePublicApiTest.php
tests/PublicApi/HttpPublicApiTest.php
tests/PublicApi/DatabasePublicApiTest.php

tests/Architecture/PublicSurfaceBoundaryTest.php
tests/Architecture/RuntimeLeakBoundaryTest.php
tests/Architecture/ComponentSuiteStructureTest.php
tests/Architecture/NamespaceDriftTest.php
```

Acceptance:

```md
[ ] Test expansion plan exists.
[ ] Critical public APIs are mapped.
[ ] Kernel flows are mapped.
[ ] Missing tests are prioritized.
[ ] No fake coverage claim is made.
```

## Stage 10 — V1 Kernel Green Proof Pass

Only after prior stages.

```md
## Goal

Prove or reject V1 Kernel Green with full evidence.

Output:
- Code-Review-And-ToDo/v1-integrity/v1-kernel-green-proof-report.md
```

Commands:

```bash
composer validate --no-check-publish
composer dump-autoload -o

php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-compat-aliases.php

vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
vendor/bin/psalm --no-progress

php tooling/audit_broken_refs.php
php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/check-superglobals.php
php avax runtime:doctor
```

Acceptance:

```md
[ ] Composer validate passes.
[ ] Autoload passes.
[ ] No production PSR-4 skips.
[ ] Architecture checkers pass.
[ ] Public surface checker passes.
[ ] Runtime leak checker passes.
[ ] Broken refs have 0 unresolved production-critical refs.
[ ] Full V1 test suite passes.
[ ] PHPStan passes or has honest non-masking baseline.
[ ] Psalm passes or is explicitly removed from required gates.
[ ] Docs validate.
[ ] Docs mirror source.
[ ] Runtime doctor passes.
```

If any fails:

```text
V1 Kernel Green = NOT PROVEN
V2 Implementation = LOCKED
V3 Implementation = LOCKED
```

If all pass:

```text
V1 Kernel Green = PROVEN
V2 Implementation = MAY UNLOCK
V3 Implementation = PLANNING ONLY
```

## One-shot prompt for next agent

```text
You are working on AvaX V1 Static Integrity Recovery.

Read:
1. CURRENT_TRUTH.md
2. Code-Review-And-ToDo/EXECUTION.md
3. TODO.md
4. Code-Review-And-ToDo/v1-integrity/broken-reference-groups.md
5. Code-Review-And-ToDo/v1-integrity/critical-broken-reference-repair-report.md
6. Code-Review-And-ToDo/v1-integrity/phpstan-error-groups.md

Current truth:
Composer GREEN.
Autoload GREEN.
Production PSR-4 skips GREEN.
Runtime doctor GREEN.
V1 Kernel Green NOT PROVEN.
V2 LOCKED.
V3 LOCKED.
PHPStan RED.
Tests RED.
Broken refs still show 49 CRITICAL, so do not call broken refs GREEN until proven.

Task:
Run Stage 1 only: Broken Reference Closure Pass.

Do not implement V2.
Do not implement V3.
Do not add features.
Do not broaden architecture.
Do not create placeholders.
Do not repair tests yet.
Do not start broad PHPStan repair until remaining critical refs are closed or classified.

Goal:
Reduce remaining CRITICAL broken refs to zero unresolved production-critical refs, or classify every remaining one with evidence.

Required output:
Code-Review-And-ToDo/v1-integrity/final-critical-broken-reference-closure-report.md

Required validation:
composer validate --no-check-publish
composer dump-autoload -o
php tooling/audit_broken_refs.php
php tooling/refactor/categorize-broken-refs.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php

Final response:
Stage
Status
Files changed
Commands run
Broken refs before/after
Remaining critical refs
Remaining risks
Next allowed action
```

To je sledeći pravi potez. Ne PHPStan odmah. Prvo zatvori tih **49 CRITICAL** ili ih precizno skini sa “production blocker” liste. Posle toga PHPStan više neće biti šum nego prava mapa grešaka.
