# Production Readiness Report

Started: 2026-05-01  
Status: **In progress**  
Current Readiness: **Architecture: YELLOW/GREEN candidate · Testing/Integrity: RED · Production: RED**  
Roadmap Source: `Code-Review-And-ToDo/master-plan/avax-master-development-plan.md`  
Current Truth Source: `CURRENT_TRUTH.md`

---

## 0. Purpose

This document is the operational production-readiness report for AvaX.

It is not a vision document.  
It is not a marketing document.  
It is not a generic TODO list.

It answers one practical question:

```text
What still blocks AvaX from being called production-ready?
```

AvaX is production-ready only when:

```text
architecture, taxonomy, autoload, namespaces, tests, static analysis,
runtime safety, public API stability, security baseline, performance baseline,
observability, documentation, and compatibility bridges all agree.
```

---

## 1. Current Executive Status

### 1.1 Current summary

```text
Architecture direction:        mostly correct
Component taxonomy:            still needs final integrity proof
Autoload integrity:            partially proven, must be revalidated
Tests:                         RED
Static analysis:               RED for components
Documentation mirror:          partially green, must be revalidated after moves
Runtime safety:                not fully proven
Security baseline:             partial
Performance baseline:          partial/planned
Observability baseline:        partial/planned
Production readiness:          RED
```

### 1.2 Immediate production-readiness blocker

The immediate blocker is **not feature absence**.

The blocker is integrity:

```text
1. component taxonomy must be physically correct
2. namespaces must match final ownership
3. composer autoload must be clean
4. tests must load and target canonical classes
5. PHPStan/Psalm must stop reporting migration noise
6. runtime safety must be proven
```

No new feature expansion should run while this report remains RED.

---

## 2. Readiness Color Rules

### GREEN

A section may be marked GREEN only when:

```text
[ ] expected files exist
[ ] code is in the correct owner
[ ] no duplicate owner remains
[ ] namespaces are canonical
[ ] autoload passes
[ ] relevant checkers pass
[ ] tests pass where required
[ ] static analysis is clean or consciously baselined
[ ] docs match source
[ ] remaining risks are documented
```

### YELLOW

A section may be marked YELLOW when:

```text
[ ] main goal is achieved
[ ] non-critical risks remain
[ ] risks are documented
[ ] next step is clear
```

### RED

A section is RED when:

```text
[ ] autoload fails
[ ] architecture checker fails
[ ] namespace drift exists
[ ] tests cannot load
[ ] PublicSurface leaks internals
[ ] runtime safety is unproven
[ ] source and docs disagree
[ ] component PHPStan is not green
```

---

## 3. Global Acceptance Criteria

Production readiness requires all of these to be green.

```text
[ ] CURRENT_TRUTH.md is current and trusted by AGENTS.md.
[ ] Final project tree is frozen and documented.
[ ] Component taxonomy integrity is green.
[ ] Security is a suite, not a component.
[ ] No extra top-level production components exist outside final suites.
[ ] No nested System folders exist inside Capabilities/Foundation/PublicSurface.
[ ] composer dump-autoload -o passes with no skipped production classes.
[ ] composer dump-autoload -o passes with no skipped test classes.
[ ] All canonical tests are green.
[ ] PHPUnit loads the full suite.
[ ] Framework PHPStan is green.
[ ] Component PHPStan is green for every component under components/.
[ ] Psalm is green or has a conscious baseline that does not hide missing classes.
[ ] Broken reference audit has no unresolved internal AvaX/component references.
[ ] Documentation checks are green.
[ ] Docs mirror source.
[ ] Forbidden folder and naming checks are green.
[ ] Superglobal boundary audit is green.
[ ] PublicSurface checker is green.
[ ] Runtime leak checker is green.
[ ] Vendor monolith isolation checker is green.
[ ] PHP-CS-Fixer dry-run is green for the committed scope.
[ ] .agents/management/TODO.md, .agents/management/BUGS.md, and .agents/management/ACTIVE.md are synchronized.
[ ] Every changed component has relevant tests or a documented reason.
[ ] Golden Path App runs using public APIs only.
[ ] Runtime worker safety is proven.
[ ] Security baseline is documented and tested.
[ ] Performance baseline is documented and benchmarked.
[ ] Observability contract is documented and implemented at minimum level.
[ ] Compatibility bridges are documented, tested, and removable.
```

---

## 4. Current Baseline

### 4.1 Known green gates from latest framework pass

These were reported as green in the latest framework pass and must be revalidated after taxonomy and namespace changes:

```bash
./vendor/bin/phpunit --no-coverage
./vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw
php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/architecture/check-forbidden-folders.php
php tooling/check-superglobals.php
```

### 4.2 Known blockers

```text
[ ] components/ PHPStan is not green.
[ ] php tooling/audit_broken_refs.php still reports unresolved internal references.
[ ] PHP-CS-Fixer dry-run reports broad repository style drift.
[ ] Management TODO/BUG/ACTIVE lists are not synchronized yet.
[ ] Test layer still needs canonical namespace/import repair.
[ ] Some component-local tests may still exist or may not be canonical.
[ ] Application/Cache has ongoing PHPStan issues.
[ ] Runtime safety proof is incomplete.
[ ] Golden Path App is not yet final production proof.
```

### 4.3 Current baseline command set

Run this exact baseline after each major stage:

```bash
git status --short
composer validate --no-check-publish
composer dump-autoload -o

php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-docs-mirror.php
php tooling/refactor/check-forbidden-folders.php
php tooling/refactor/check-vendor-monolith-isolation.php
php tooling/refactor/check-compat-aliases.php

./vendor/bin/phpunit --no-coverage
./vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw
./vendor/bin/psalm
php tooling/audit_broken_refs.php
php tooling/docs/validate-docs.php
php tooling/docs/validate-docs-mirror-source.php
php tooling/check-superglobals.php
```

If any command is unavailable, record:

```text
command:
status: unavailable
reason:
replacement command:
risk:
```

Do not silently skip validation.

---

## 5. Production Readiness Stages

This report follows the master development plan.

### Stage 00: Current Truth Lock

Status: **REQUIRED**

Goal:

```text
Make CURRENT_TRUTH.md the single operational status file.
```

Required output:

```text
CURRENT_TRUTH.md
Code-Review-And-ToDo/master-plan/phase-status.md
```

Acceptance:

```text
[ ] CURRENT_TRUTH.md exists.
[ ] AGENTS.md instructs agents to read CURRENT_TRUTH.md first.
[ ] Older reports cannot override CURRENT_TRUTH.md.
[ ] Current production readiness status is explicitly RED/YELLOW/GREEN.
```

---

### Stage 01: Final Project Tree Freeze

Status: **REQUIRED**

Goal:

```text
Freeze the final repo, framework, component, test, docs, tooling, examples,
and reference-architecture tree before more repair work.
```

Required output:

```text
Code-Review-And-ToDo/master-plan/avax-master-project-tree.md
Code-Review-And-ToDo/master-plan/component-owner-map.md
```

Acceptance:

```text
[ ] Final root tree documented.
[ ] framework/System tree documented.
[ ] components/ suite tree documented.
[ ] tests/ tree documented.
[ ] docs/ tree documented.
[ ] tooling/ tree documented.
[ ] forbidden production root folders documented.
```

---

### Stage 02: Taxonomy Integrity Green

Status: **BLOCKING**

Goal:

```text
Make the physical repo match the final component taxonomy.
```

Commands:

```bash
find components -type d -path '*System/Capabilities/*/System*' | sort
find components -type d -path '*System/Foundation/*/System*' | sort
find components -type d -path '*System/PublicSurface/*/System*' | sort

composer dump-autoload -o
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
```

Acceptance:

```text
[ ] No nested System folders.
[ ] No extra top-level components outside final suites.
[ ] Security/Hashing/System exists if hashing code exists.
[ ] Security/Secrets/System exists if secrets code exists.
[ ] Security/System/Hashing does not exist.
[ ] Security/System/Secrets does not exist.
[ ] DataLayer is not a real owner.
[ ] DataFoundation is not a real owner.
[ ] CLI/Commands is not a separate runtime owner.
[ ] CLI/UI is folded into CLI/Console.
[ ] Operations/Monitoring is folded into Operations/Observability.
[ ] Application/Cache has no stale Avax\Cache namespace.
[ ] composer dump-autoload passes.
[ ] suite checker passes.
[ ] duplicate owner checker passes.
[ ] namespace drift checker passes.
```

---

### Stage 03: API Classification and Evolution Rules

Status: **REQUIRED BEFORE MASS PUBLICSURFACE COMPLETION**

Goal:

```text
Prevent accidental public API.
```

Required output:

```text
docs/governance/public-api-policy.md
docs/governance/deprecation-policy.md
docs/governance/compatibility-policy.md
Code-Review-And-ToDo/master-plan/api-classification-matrix.md
```

Acceptance:

```text
[ ] @public is defined.
[ ] @internal is defined.
[ ] @experimental is defined.
[ ] @deprecated is defined.
[ ] @removed-in is defined.
[ ] PublicSurface breaking-change rule exists.
[ ] Compatibility alias lifecycle exists.
```

---

### Stage 04: Component Completion

Status: **IN PROGRESS / PARTIAL**

Goal:

```text
Complete components with real lanes, not placeholder folders.
```

A component is complete only when:

```text
[ ] correct suite
[ ] canonical namespace
[ ] meaningful PublicSurface if public API exists
[ ] meaningful Capabilities
[ ] meaningful Flows if orchestration exists
[ ] meaningful Configuration if assembly/config exists
[ ] meaningful Foundation only for local values/failures/primitives
[ ] no placeholder classes
[ ] no describeResponsibility-only classes
[ ] no duplicate owner
[ ] no stale namespace
[ ] how-this-works.md updated
[ ] tests planned or added under root tests/
```

Required output:

```text
Code-Review-And-ToDo/master-plan/component-completion-matrix.md
```

Priority:

```text
1. Application/Facade
2. Application/FeatureFlags
3. Application/Pipeline
4. CLI/Console normalization
5. HTTP incomplete components
6. Identity incomplete components
7. Security components
8. Operations incomplete components
9. DeveloperTools incomplete components
10. Presentation/View vendor isolation
```

---

### Stage 05: Canonical Class Map

Status: **REQUIRED BEFORE TEST REPAIR**

Goal:

```text
Create a machine-readable class truth.
```

Required output:

```text
build/canonical-class-map.json
Code-Review-And-ToDo/master-plan/canonical-class-map.md
```

Each class entry must include:

```text
FQCN
file path
suite
component
lane
status
```

Allowed statuses:

```text
canonical
public-api
internal
experimental
deprecated-bridge
test-fixture
dead
```

Acceptance:

```text
[ ] All production classes mapped.
[ ] All aliases mapped.
[ ] All alias targets exist.
[ ] Tests can be repaired against the map.
```

---

### Stage 06: Autoload and Namespace Repair

Status: **REQUIRED**

Goal:

```text
Make composer, namespaces, aliases, and file paths agree.
```

Commands:

```bash
composer dump-autoload -o
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-compat-aliases.php
```

Acceptance:

```text
[ ] No broken autoload.
[ ] No skipped production classes.
[ ] No stale production namespace.
[ ] No broken alias.
[ ] No old top-level real owner namespace remains except documented bridge.
```

---

### Stage 07: Test Layer Repair

Status: **RED**

Goal:

```text
Turn tests from legacy archaeology into canonical proof.
```

Canonical test namespace:

```php
Avax\Tests\...
```

Canonical test tree:

```text
tests/
  Architecture/
  Unit/
  Integration/
  Feature/
  PublicApi/
  Compatibility/
  Support/
```

Repair order:

```text
1. PSR-4 test namespace repair.
2. Legacy imports rewrite.
3. FakeRouter / FakeContainer repair.
4. ResponseFactory decision.
5. ServiceProviderInterface -> RegisterDependency test repair.
6. PHPUnit data provider strict typing.
7. Component-local tests moved or archived.
```

Acceptance:

```text
[ ] composer dump-autoload -o has no skipped tests.
[ ] PHPUnit loads the full suite.
[ ] No fatal missing-class errors.
[ ] Failures are behavior failures, not namespace failures.
[ ] Tests target canonical class map.
```

---

### Stage 08: Static Analysis Green

Status: **RED**

Commands:

```bash
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw
vendor/bin/psalm
```

Repair order:

```text
1. Unknown classes.
2. Bad imports.
3. Fake/test double signatures.
4. Constructor mismatches.
5. Invalid generics/PHPDoc.
6. Real production type errors.
```

Acceptance:

```text
[ ] PHPStan passes or has conscious baseline.
[ ] Psalm passes or has conscious baseline.
[ ] Baseline does not hide missing classes.
[ ] Baseline does not hide broken autoload.
```

---

### Stage 09: AvaX Kernel Green

Status: **NOT YET PROVEN**

Must prove:

```text
[ ] Framework boots.
[ ] Config loads.
[ ] Container builds.
[ ] Route registers.
[ ] Middleware runs.
[ ] Request handled.
[ ] Response built.
[ ] Session works.
[ ] Database query works.
[ ] Log written.
[ ] Console command runs.
[ ] Runtime state resets.
```

Commands:

```bash
vendor/bin/phpunit tests/Feature/Framework
vendor/bin/phpunit tests/PublicApi
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
```

Acceptance:

```text
[ ] Kernel feature tests pass.
[ ] Public API tests pass.
[ ] Runtime leak checker passes.
[ ] PublicSurface checker passes.
```

---

### Stage 10: Production Readiness Baseline

Status: **NOT YET GREEN**

Tracks:

```text
Security
Performance
Observability
Runtime safety
Failure handling
Compatibility bridges
Documentation
```

Acceptance:

```text
[ ] Security baseline tests pass.
[ ] Runtime worker safety tests pass.
[ ] Performance cache commands exist or are explicitly planned.
[ ] Observability events are emitted.
[ ] Compatibility bridges are tested and documented.
```

---

## 6. Component Readiness Matrix

Use this matrix as the canonical production-readiness view.

| Suite          | Component           | Taxonomy | Namespace |   Tests | PHPStan | Docs | Runtime-safe | Status |
|----------------|---------------------|---------:|----------:|--------:|--------:|-----:|-------------:|--------|
| Application    | Config              |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Container           |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Cache               |   YELLOW |       RED |     RED |     RED |  RED |          TBD | RED    |
| Application    | DateTime            |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Facade              |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | FeatureFlags        |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Filesystem          |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Localization        |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Pipeline            |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Text                |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Application    | Validation          |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Request             |   YELLOW |       TBD | PARTIAL |     TBD |  TBD |          TBD | RED    |
| HTTP           | Response            |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Router              |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Middleware          |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Session             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Security            |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | Client              |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| HTTP           | ApiVersioning       |   YELLOW |       TBD | PARTIAL |     TBD |  TBD |          TBD | RED    |
| CLI            | Console             |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| DataStack      | Data                |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| DataStack      | Database            |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| DataStack      | Persistence         |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Identity       | Auth                |   YELLOW |       TBD | BLOCKED |     TBD |  TBD |          TBD | RED    |
| Identity       | Access              |   YELLOW |       TBD | PARTIAL |     TBD |  TBD |          TBD | RED    |
| Identity       | Credentials         |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Identity       | Tokens              |   YELLOW |       TBD | PARTIAL |     TBD |  TBD |          TBD | RED    |
| Identity       | ExternalIdentity    |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Identity       | Tenancy             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Security       | Cryptography        |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Security       | Hashing             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Security       | Secrets             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Security       | Redaction           |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Operations     | Events              |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Operations     | Logging             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Operations     | Queue               |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Operations     | MessageBus          |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Operations     | ApplicationWorkflow |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| Presentation   | View                |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| DeveloperTools | Diagnostics         |   YELLOW |       TBD | BLOCKED |     TBD |  TBD |          TBD | RED    |
| DeveloperTools | CodeGeneration      |   YELLOW |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |
| DeveloperTools | Testing             |      TBD |       TBD |     TBD |     TBD |  TBD |          TBD | RED    |

Legend:

```text
TBD = not audited in this report version
PARTIAL = some targeted tests or implementation exists
YELLOW = promising but not fully proven
RED = not production-ready
```

---

## 7. Work Log

### 2026-05-01 - Component Completion Pass

Status: **Partial hard-fail closure complete; full production readiness still blocked by unavailable command execution.
**

Scope:

```text
Identity/Access
Identity/Security
Identity/Tokens
Identity/Auth
HTTP/ApiVersioning
HTTP/Request
HTTP/Response
DeveloperTools/CodeGeneration
DeveloperTools/Diagnostics
DataStack/Data
DataStack/Database
Operations/ApplicationWorkflow
Operations/Events
CLI/Console
```

Closed hard-fail signals:

```text
[ ] Removed all runtime NotImplementedException / "not yet implemented" component paths found by text audit.
[ ] Added real admin elevation state and authorization checking for Identity/Access.
[ ] Added pending/approve/apply security change behavior for Identity/Security.
[ ] Added real authorization-code, exchange, introspection, and revocation behavior for Identity/Tokens.
[ ] Replaced the base64-only auth token codec and always-false token store with HMAC token encoding and revocation tracking.
[ ] Added missing API version registry and resolver capabilities for HTTP/ApiVersioning.
[ ] Replaced incomplete PSR upload/header behavior in HTTP/Request.
[ ] Added missing request body parsers, runtime request creation, and request builder behavior.
[ ] Replaced code-generation TODO stubs and forbidden Services generation with capability/action generation.
[ ] Corrected DeveloperTools/Diagnostics namespace ownership and removed fake database/cache readiness results.
[ ] Added concrete classes to previously empty component folders.
```

Verification completed before environment execution limit:

```bash
./vendor/bin/phpunit tests/Unit/Components/Identity/Access/AccessPublicSurfaceTest.php tests/Unit/Components/Identity/Security/SecurityChangeWorkflowTest.php tests/Unit/Components/Identity/Tokens/TokensPublicSurfaceTest.php tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php tests/Unit/Components/HTTP/Request/ServerRequestTest.php
```

Result:

```text
OK (7 tests, 22 assertions)
```

Verification blocked after additional Auth/Diagnostics/Request completion:

```bash
./vendor/bin/phpunit tests/Unit/Components/Identity/Auth/AuthTokenCapabilityTest.php tests/Unit/Components/DeveloperTools/Diagnostics/HealthCheckTest.php tests/Unit/Components/Identity/Access/AccessPublicSurfaceTest.php tests/Unit/Components/Identity/Security/SecurityChangeWorkflowTest.php tests/Unit/Components/Identity/Tokens/TokensPublicSurfaceTest.php tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php tests/Unit/Components/HTTP/Request/ServerRequestTest.php
```

Required next action:

```text
Run the blocked PHPUnit command locally and record result here.
```

Text-audit status:

```text
No empty directories remain under components/.
No matches remain for:
Simple placeholder
TODO: Implement
NotImplementedException
not yet implemented
Not implemented
// Implementation
new Request(...)
Placeholder for
```

Caution:

```text
Text-audit clean does not mean production-ready.
It only means obvious placeholder text was removed.
```

---

### 2026-05-01 - Application Cache

Status: **In progress / RED**

Command:

```bash
./vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
```

Findings:

```text
[ ] Public cache facade and compiled cache facade still use old named arguments.
[ ] AvaxCache still calls cache-store contracts with old argument names.
[ ] CacheResult redeclares promoted readonly properties.
[ ] Several cache tests use PHPUnit named arguments, which PHPStan rejects because PHPUnit marks those APIs as no-named-arguments.
```

Required fixes:

```text
[ ] Update cache facade calls to canonical argument names.
[ ] Update compiled cache facade calls to canonical argument names.
[ ] Update AvaxCache calls to match current cache-store contract.
[ ] Fix CacheResult readonly promoted property redeclaration.
[ ] Replace PHPUnit named arguments in cache tests with positional arguments.
[ ] Re-run PHPStan for components/Application/Cache.
[ ] Re-run relevant cache unit tests.
```

Acceptance:

```bash
./vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
vendor/bin/phpunit tests/Unit/Components/Application/Cache
```

---

## 8. Immediate Next Actions

Run in this order.

### 8.1 Confirm taxonomy integrity

```bash
find components -type d -path '*System/Capabilities/*/System*' | sort
find components -type d -path '*System/Foundation/*/System*' | sort
find components -type d -path '*System/PublicSurface/*/System*' | sort

composer dump-autoload -o
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
```

### 8.2 Re-run blocked targeted tests

```bash
./vendor/bin/phpunit tests/Unit/Components/Identity/Auth/AuthTokenCapabilityTest.php tests/Unit/Components/DeveloperTools/Diagnostics/HealthCheckTest.php tests/Unit/Components/Identity/Access/AccessPublicSurfaceTest.php tests/Unit/Components/Identity/Security/SecurityChangeWorkflowTest.php tests/Unit/Components/Identity/Tokens/TokensPublicSurfaceTest.php tests/Unit/Components/HTTP/ApiVersioning/ApiVersionTest.php tests/Unit/Components/HTTP/Request/ServerRequestTest.php
```

### 8.3 Fix Application/Cache PHPStan

```bash
./vendor/bin/phpstan analyse components/Application/Cache --memory-limit=1G --error-format=raw --no-progress
```

### 8.4 Audit broken references

```bash
php tooling/audit_broken_refs.php
```

### 8.5 Group component PHPStan errors

```bash
./vendor/bin/phpstan analyse components --memory-limit=1G --error-format=raw --no-progress
```

Output:

```text
Code-Review-And-ToDo/production-readiness/component-phpstan-error-groups.md
```

Groups:

```text
1. unknown classes
2. stale namespaces
3. wrong named arguments
4. wrong constructor calls
5. bad fake/test double signatures
6. PHPDoc/generic drift
7. real production type errors
```

---

## 9. Forbidden Work Until Report Is YELLOW

```text
[ ] Do not add System Design Kit.
[ ] Do not add benchmarks.
[ ] Do not add plugin architecture.
[ ] Do not add new infrastructure adapters.
[ ] Do not add new public APIs unless required to stabilize existing ones.
[ ] Do not rewrite components from scratch.
[ ] Do not loosen production types to satisfy legacy tests.
[ ] Do not create placeholder classes.
[ ] Do not call a component complete without tests or documented test plan.
```

---

## 10. Agent Execution Contract

Every agent working on production readiness must report:

```text
1. Stage name.
2. Scope.
3. Files changed.
4. Files intentionally not touched.
5. Validation commands run.
6. Command output summary.
7. Remaining risks.
8. Final GREEN/YELLOW/RED status.
```

No raw conversational summary counts as completion.

If command execution is blocked by environment limits:

```text
[ ] Record the exact command.
[ ] Record when it was blocked.
[ ] Record what was changed before the block.
[ ] Do not mark validation as complete.
```

---

## 11. Final Verdict

Current verdict:

```text
RED
```

Reason:

```text
AvaX has meaningful architecture and component recovery progress, but production readiness is blocked by component PHPStan errors, broken reference audit issues, incomplete test-layer repair, incomplete runtime safety proof, and incomplete component-level validation.
```

Next target verdict:

```text
YELLOW
```

YELLOW requires:

```text
[ ] taxonomy integrity green
[ ] composer autoload green
[ ] no unresolved internal broken references
[ ] targeted component completion tests green
[ ] Application/Cache PHPStan green
[ ] component PHPStan errors grouped and reduced
[ ] test layer repair plan active
```

GREEN requires all global acceptance criteria.
