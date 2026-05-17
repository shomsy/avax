# Production Readiness Report

Started: 2026-05-01  
Status: **Complete / Enforced**  
Current Readiness: **Architecture: GREEN · Testing/Integrity: GREEN · Production: GREEN_WITH_ACCEPTED_YELLOW_DEBT**  
Roadmap Source: `EVIDENCE/master-plan/avax-master-development-plan.md`  
Current Truth Source: `CURRENT_TRUTH.md`

## Status

**MANDATORY** - This document defines non-negotiable production readiness rules for AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

---

## 0. Purpose

This document is the operational production-readiness report and governance contract for AvaX.

It is not a vision document.  
It is not a marketing document.  
It is not a generic TODO list.

It answers one practical question:

```text
What defines production-readiness, and how is it proven?
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
Architecture direction:        GREEN
Component taxonomy:            GREEN
Autoload integrity:            GREEN
Tests:                         GREEN
Static analysis:               GREEN (0 errors)
Documentation mirror:          GREEN
Runtime safety:                GREEN
Security baseline:             GREEN
Performance baseline:          GREEN
Observability baseline:        GREEN
Production readiness:          GREEN_WITH_ACCEPTED_YELLOW_DEBT
```

### 1.2 Status State Machine

A recursive governance review is clean only when it has zero unresolved findings.

Unresolved YELLOW findings are allowed only as accepted governance debt.

If any YELLOW finding remains, the phase MUST NOT be reported as pure FULL_GREEN.

Allowed final statuses:

- FULL_GREEN: validation clean, gates clean, governance review has zero unresolved findings.
- GREEN_WITH_ACCEPTED_YELLOW_DEBT: validation/gates clean, no BLOCKER/HIGH/MEDIUM remains, but YELLOW findings are
  formally accepted.
- YELLOW_WITH_EXACT_BLOCKERS: findings remain and may affect readiness.
- RED: validation, security, truth, or mandatory gates are broken.

Accepted YELLOW debt MUST include:

- affected files
- exact pattern
- severity
- why it is not HIGH/BLOCKER
- owner
- target phase/version
- expiry or review date
- risk
- mitigation
- evidence location
- V5.9 blocking decision
- truth/backlog entry

Commit is allowed with accepted YELLOW debt only if:

- no BLOCKER/HIGH/MEDIUM review finding remains
- no HIGH/BLOCKER security issue remains
- YELLOW debt is tracked with owner/target/risk/expiry
- truth files clearly say it is not pure FULL_GREEN

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

All these gates are currently passing.

---

## 2. Readiness Color Rules

### GREEN

A section or component MUST be marked GREEN only when current validation evidence proves all requirements are met.

- [ ] expected files exist in canonical taxonomy
- [ ] code is in the correct owner (no technical dumping grounds)
- [ ] no duplicate owner remains
- [ ] namespaces are canonical and PSR-4 compliant
- [ ] autoload passes without warnings or skipped classes
- [ ] relevant checkers (taxonomy, public surface, leaks) pass
- [ ] all tests pass (0 failures)
- [ ] static analysis is green at max level or honestly baselined
- [ ] documentation matches source reality
- [ ] remaining risks are documented and accepted

**Evidence Rule**: A GREEN claim without a timestamped validation report or a pointer to a specific test/checker output
is **UNPROVEN** and MUST be rejected.

**Status:** MANDATORY  
**Severity:** BLOCKER

### YELLOW

A section may be marked YELLOW when:

```text
[ ] main goal is achieved
[ ] non-critical risks remain
[ ] risks are documented
[ ] next step is clear
```

### RED

A section or component MUST be marked RED when any mandatory rule is violated or any of the following exist:

- [ ] autoload fails or has production warnings
- [ ] architecture/taxonomy checker fails
- [ ] namespace drift exists
- [ ] tests cannot load or fail to target current code
- [ ] PublicSurface leaks internals or has hollow behavior
- [ ] runtime safety is unproven or has confirmed leaks
- [ ] source and documentation disagree
- [ ] static analysis (PHPStan/Psalm) has unbaselined errors

**Status:** MANDATORY  
**Severity:** BLOCKER

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
[ ] Runtime composition leak checker is green.
[ ] DI container service locator checker is green.
[ ] DI direct instantiation checker is green.
[ ] DI service provider coverage checker is green.
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
[ ] No forbidden patterns in runtime folders (Flows, Capabilities runtime, PublicSurface runtime).
[ ] No service locator pattern in runtime execution paths.
[ ] No class_exists() gating in runtime code.
[ ] No new Build* in runtime code.
[ ] No ?? new fallback in runtime code.
[ ] Gate enforcement tools are path/context-aware, not class-name allowlists.
```

---

## 3.1 Mandatory Recursive Governance Review Before Commit

For the detailed recursive review protocol and required checks, see:

```text
.agents/how-to/how-to-code-review.md
```

**Short version:**

```text
Implementation is not complete when tests pass.
Implementation is complete only when validation passes AND governance review passes.
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
EVIDENCE/master-plan/phase-status.md
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
EVIDENCE/master-plan/avax-master-project-tree.md
EVIDENCE/master-plan/component-owner-map.md
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
EVIDENCE/master-plan/api-classification-matrix.md
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
EVIDENCE/master-plan/component-completion-matrix.md
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
EVIDENCE/master-plan/canonical-class-map.md
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

## 8. Governance Exception Register Protocol

### 8.1 Definition of an Exception

A governance exception is a conscious, documented decision to temporarily or permanently waive a mandatory rule.

### 8.2 Mandatory Registration

Every governance deviation MUST be recorded in the **Exception Register**.
The register is a centralized ledger at `EVIDENCE/accepted-exceptions-ledger.md`.

### 8.3 Required Exception Data

An exception MUST include:

1. **Rule**: The exact rule being waived.
2. **Context**: Path, class, or component affected.
3. **Reason**: Why the rule cannot be followed.
4. **Risk**: What is the impact of waiving this rule?
5. **Mitigation**: How is the risk managed?
6. **Owner**: The person/agent who authorized the exception.
7. **Expiry**: When will the exception be reviewed or cleaned up?

### 8.4 Approval

Exceptions MUST be explicitly accepted by a human reviewer or a higher-tier governance authority.

**Silent exceptions are FORBIDDEN.**

A "workaround" that is not in the register is a governance violation.

**Status:** MANDATORY  
**Severity:** BLOCKER

---

## 9. Component Readiness Matrix

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

## 29. Immediate Next Actions

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
EVIDENCE/production-readiness/component-phpstan-error-groups.md
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

## 30. Forbidden Work Until Report Is YELLOW

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

## 11. Security Must Scream Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Security-sensitive findings MUST be loud, explicit, and blocking by default.

Any OWASP-class weakness, injection risk, authentication bypass, authorization bypass, sensitive data leak, unsafe
deserialization, unsafe redirect, filesystem traversal, command execution risk, SSRF risk, XSS risk, CSRF risk,
SQL/query injection risk, weak cryptography, secret exposure, unsafe logging, or session/cookie weakness MUST be
classified as HIGH or BLOCKER unless proven otherwise.

Security findings MUST NOT be hidden as:

```text
cleanup
style issue
minor refactor note
pre-existing harmless debt
accepted risk without owner/expiry
non-blocking note
code quality nit
low-priority cleanup
```

A security finding may be downgraded only with:

```text
exact threat explanation
affected path
exploitability assessment
mitigation proof
test or gate evidence
owner
expiry if accepted temporarily
truth/backlog entry
explicit stage-blocking decision
```

### Minimum Severity Rule

- exploitable or likely exploitable security weakness = **BLOCKER**
- potential OWASP-class weakness = **HIGH** or **BLOCKER**
- defense-in-depth gap = **MEDIUM** or **HIGH**, depending on blast radius
- documentation-only security clarification = **LOW** only when no exploit path exists

---

## 12. Security Review Trigger Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

Security review is mandatory when a change touches any of the following:

```text
authentication
authorization
roles/permissions
sessions
cookies
CSRF
CORS
redirects
user input
request parsing
validation
serialization/deserialization
database query building
filesystem I/O
file upload/download
logging
secrets
hashing
encryption
HTTP client/server
queues and message payloads
cache keys containing user or user-derived data
template/rendering
command/process execution
event payloads crossing boundaries
webhooks
signed URLs
tokens
API keys
password reset flows
rate limiting
tenant isolation
sandboxing
plugin execution
object storage paths
URL generation
proxy/trusted header handling
```

### Required Review Evidence

If triggered, the review MUST include this table:

| Area | Changed? | Risk checked | Finding | Severity | Fix/mitigation | Blocks commit? |
|---|---:|---|---|---|---:|

### Silent Skip Rule

If the change does not trigger security review, the governance review MUST explicitly state why.

Security review cannot be skipped silently.

---

## 13. Security Commit Block Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

A commit is FORBIDDEN if the current change introduces, exposes, or leaves unresolved any security issue classified as
BLOCKER, HIGH, OWASP-class weakness, authentication bypass, authorization bypass, injection risk, XSS risk, CSRF risk,
SSRF risk, unsafe redirect, unsafe deserialization, path traversal, command execution risk, secret exposure, sensitive
data logging, weak cryptography/hashing, session/cookie weakness, unsafe file upload/download, database query injection
risk, unsafe event payload crossing trust boundary, unsafe queue payload handling, unsafe tenant boundary, or unsafe
plugin/sandbox execution.

### Required Action

1. Do not commit.
2. Document the finding.
3. Fix it first.
4. Rerun validation.
5. Rerun security review.
6. Rerun recursive governance review.
7. Commit only when security review is clean.

### Temporary Acceptance

A security issue may remain only if final status is YELLOW or RED. Never GREEN.

If temporarily accepted, it MUST have:

```text
exact issue
affected path
severity
exploitability assessment
owner
mitigation
expiry or version
backlog or truth entry
evidence
explicit decision whether it blocks the current stage
```

### GREEN Commit Rule

A GREEN commit with unresolved security issue is forbidden.

---

## 14. Gate Self-Test Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Every mandatory validation gate or architecture test MUST have at least one negative test case (proving it fails when
the rule is violated).

- A gate that cannot fail is not a gate.
- A gate with "0 scans" or "0 violations" is UNPROVEN until the scanner's ability to find violations is verified.
- Mandatory gate without self-test or proof is BLOCKER for production-ready GREEN status.

Required examples:

```text
Runtime composition gate must fail on:
  $dependency ?? new Dependency()
  new BuildSomething() in runtime
  $middleware[] = new SomeMiddleware(...)
  class_exists() runtime wiring

DI gate must fail on:
  hidden fallback construction
  container service locator in runtime code

Git gate must fail on:
  .phpunit.cache/**
  .qoder/worktrees/**
  forbidden local AI cache files

Security gate must fail on:
  obvious SQL/query injection pattern
  unsafe redirect pattern
  sensitive logging pattern
  path traversal pattern
```

Rules:

- gate without self-test or proof is YELLOW at minimum
- mandatory gate without self-test or proof cannot close BLOCKER
- gate that scans zero active files is FAIL, not PASS
- gate PASS with RED content is FAIL

## 15. No Zero-Scan Gate Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

A mandatory gate that scans zero active files is failed, not passed.

A gate result MUST report:

```text
scanned files count
excluded files count
exclusion reasons
active findings count
accepted exceptions count
false positives count
exit code
final classification
```

If a gate cannot determine its scan scope, it is unreliable and cannot support GREEN status.

NOT_FOUND is not PASS.
UNAVAILABLE is not PASS.
SKIPPED is not PASS unless explicitly allowed by stage scope.
Exit 0 with RED content is not PASS.

## 16. Quality Ratchet Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

When a quality metric improves, the new better baseline becomes the floor.

Future passes MUST NOT regress below the last proven baseline unless explicitly accepted as YELLOW or RED with owner,
expiry, risk, and recovery plan.

Tracked metrics include:

```text
PHPStan error count
PHPUnit errors/failures
mutation score if available
runtime composition findings
runtime assembly findings
public surface violations
component status lock coverage
health/doctor check coverage
security findings
performance benchmark baseline
weak test count
ignored/suppressed static-analysis count
gate self-test coverage
component health-check coverage
```

Important rule:

- If a metric is established at 0, it MUST stay at 0.
- If a metric is not yet 0, the accepted current baseline becomes the temporary floor until improved.

Remaining nonzero debt MUST be classified with:

```text
exact count
owner
target stage/version
risk
blocking decision
evidence
```

Quality ratchet evidence must include:

| Metric | Previous baseline | New baseline | Command | Evidence file | Owner | Blocks next stage? |
|--------|------------------:|-------------:|---------|---------------|-------|-------------------:|

---

## 19. Security and Performance Trigger Cross-Rule

Security review MUST be triggered by changes to areas listed in:

```text
.agents/how-to/how-to-system-security.md §44
```

Performance review MUST be triggered by changes to areas listed in:

```text
.agents/how-to/how-to-system-performance.md §44
```

If triggered, review evidence must include the compliance table. If not triggered, review must say why.

## 20. Large Unit Review Thresholds

Mandatory review triggers:

```text
Class over 300 lines:            mandatory responsibility review
Method over 50 lines:            mandatory extraction or explanation review
Constructor with 8+ dependencies: mandatory design review
PublicSurface over 150 lines:    mandatory behavior leak review
Builder over 300 lines:          BLOCKER until classified
ServiceProvider over 250 lines:  mandatory split review
Test class over 500 lines:       mandatory test organization review
```

Threshold trigger requires documented decision. No large unit may be called GREEN without review decision.

## 21. Examples Are Architecture Rule

Examples, GoldenPath apps, documentation snippets, generated examples, and tests are source material for humans and AI.
They MUST show canonical style.

They MUST NOT show: manual runtime service assembly, hidden fallback dependencies, direct new of runtime services,
service locator in runtime code, fake providers, deprecated APIs as primary examples, old names after canonical rename,
shortcuts that violate governance, weak test patterns, fake GREEN evidence, or security-sensitive shortcuts without
warning.

If examples must show low-level/manual usage, they must be clearly labeled as advanced/internal/testing-only.

GoldenPath examples MUST be canonical. If examples teach an anti-pattern, the codebase will reproduce it.

## 22. Canonical Term Registry Rule

One concept must have one canonical name. Check the registry at `docs/governance/canonical-terms.md` before introducing
or accepting new terminology.

## 23. PublicSurface Factory Boundary Rule

PublicSurface may expose public factories only when they create public value/result objects or protect users from
internal construction details. PublicSurface factories MUST NOT assemble runtime service graphs, instantiate runtime
services, access the container as service locator, or create
middleware/dispatchers/resolvers/clients/stores/loggers/repositories/framework runtime services.

Allowed: `Responses::json()` delegates to `CreateHttpResponse` and returns `Response`.
Forbidden: `Responses::json()` creates new `CreateHttpResponse` internally.

PublicSurface may create produced public values. PublicSurface must not assemble machinery.

## 24. DDD Factory vs Runtime Assembly Rule

A DDD factory owns meaningful creation of domain/value/result objects when construction has invariants, policy, or
language meaning. A DDD factory MUST NOT assemble framework runtime service graphs.

Allowed: `CreateReleaseCandidate` creates `ReleaseCandidate` with invariants.
Forbidden: `RuntimeFactory` creates `Router`, `EventDispatcher`, `Logger`, `MiddlewareStack`, `DatabaseConnection`.

If a class assembles runtime services, it belongs in `ServiceProvider`, `System/Configuration`, or
`System/Configuration/Builders` — not in a DDD factory.

## 25. Governance Exception Register Rule

A documented exception is valid only when recorded in the exception register at:

```text
EVIDENCE/accepted-exceptions-ledger.md
```

Exception without owner and expiry is not an exception. It is unresolved governance debt.

## 26. Semantic PHPDoc GREEN Status Rule

### Status

**MANDATORY**
**Severity:** BLOCKER/HIGH/MEDIUM/LOW (see below)

### Rule

Semantic PHPDoc is part of AvaX architecture readability. Missing or fake PHPDoc can block GREEN.

Severity:

```text
BLOCKER:
- missing semantic PHPDoc on PublicSurface production class
- missing semantic PHPDoc on runtime-critical class
- missing semantic PHPDoc on security-sensitive class
- missing or wrong @throws on public API method
- PHPDoc that lies about behavior, security, or public API
- PHPDoc that hides runtime assembly or service locator

HIGH:
- missing semantic PHPDoc on ordinary production class touched in current pass
- missing PHPDoc on public/protected method touched in current pass
- missing array shape/generic/iterable/callable/mixed boundary docs

MEDIUM:
- missing PHPDoc on private non-trivial methods
- unclear intent in internal docblocks

LOW:
- wording polish only
```

### Anti-Spam Rule

Mandatory PHPDoc does not allow decorative PHPDoc. A required docblock that merely repeats code is still a violation.

### Cross-Reference

```text
.agents/how-to/how-to-document.md — Semantic PHPDoc Rule
```

---

## 27. Critical Quality Signal Rule

### Status

**MANDATORY**
**Severity:** HIGH (escalates to BLOCKER — see below)

### Severity Escalation

Default severity is HIGH.

Escalates to **BLOCKER** when the issue threatens:

```text
security
data integrity
runtime safety
long-lived worker safety
truth/evidence integrity
public API compatibility
dependency graph correctness
rollback/recovery safety
```

Examples that MUST be BLOCKER when active:

```text
exploitable security issue
data corruption risk
request state stored in singleton
unresolved runtime composition leak in active runtime
fake GREEN
gate PASS with RED content
mandatory gate scans zero active files
hidden fallback dependency in runtime
missing required dependency discovered in business/runtime code
service locator in business/runtime code
```

### Rule

The review MUST loudly flag anything that threatens security, data integrity, runtime safety, long-lived worker safety,
dependency graph correctness, public API compatibility, static analysis baseline, test reliability, performance hot
paths, observability of failures, rollback/recovery safety, container verification, request scope isolation, tenant
isolation, state reset safety, or failure boundary correctness.

The following must not pass silently:

```text
hidden fallback construction
runtime service assembly
service locator usage in business code
missing dependency checks in business or runtime code
mutable static state without reset proof
request state stored in singleton
fake ServiceProvider
fake PublicSurface
broad try/catch swallowing errors
broad PHPStan ignores
weak tests
assertTrue(true)
evidence claiming GREEN while validation says otherwise
gate PASS with RED content
mandatory gate with zero scanned files
fake compatibility shim
duplicate canonical concepts
large builders acting as hidden containers
examples showing non-canonical style
```

### Core Principle

If it can create a security hole, corrupt data, hide a runtime failure, break long-lived workers, or fake correctness,
it must scream in review.

---

## 17. Status State Machine Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Status labels must be exact. The following are the ONLY valid statuses:

### GREEN

All of the following MUST be true:

```text
all mandatory validation for the scope is clean
mandatory gates pass cleanly
governance review is clean
evidence exists
truth matches validation
no unclassified active blockers remain
no unresolved HIGH/BLOCKER security issue exists
no mandatory gate has RED content
no fake GREEN claim exists
```

### YELLOW

All of the following MUST be true:

```text
known exact blockers or debt remain
every remaining issue has owner, target, risk, and blocking decision
validation may be partially clean
truth is honest
work may be accepted only as evidence or partial closure
unresolved security issue may remain only with explicit owner, mitigation, expiry, and truth/backlog entry
```

### RED

Any of the following is true:

```text
validation is broken
truth is broken
mandatory gates are unreliable
active runtime/security/data-loss blockers remain
status cannot support next-stage work
commit/push is forbidden unless explicitly committing evidence-only RED/YELLOW closure is approved by workflow
```

### Forbidden Patterns

```text
"pre-existing" is not a status — every pre-existing issue must be classified as GREEN/YELLOW/RED
"Remaining YELLOW: None" when PHPStan or gates have findings — forbidden
"PASS" with RED content — forbidden
"GREEN" with missing mandatory validation — forbidden
"pre-existing" without owner/target/risk — forbidden
"security note" without severity — forbidden
"non-blocking security issue" without mitigation/expiry/evidence — forbidden
```

---

## 18. Component Status Ownership Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

Every component must have an explicit status before production readiness claims.

### Allowed Statuses

```text
ACTIVE_GREEN
ACTIVE_YELLOW
ROADMAP
SCAFFOLD
LABS_ONLY
EVIDENCE_ONLY
PURE_FOUNDATION
TEST_ONLY
DEPRECATED
```

### Required Fields

Every status entry MUST include:

| Field                          | Required          |
|--------------------------------|-------------------|
| component path                 | yes               |
| status                         | yes               |
| owner                          | yes               |
| reason                         | yes               |
| production autoload decision   | yes               |
| ServiceProvider requirement    | yes when ACTIVE   |
| health/doctor requirement      | yes               |
| test requirement               | yes               |
| security review requirement    | yes when relevant |
| performance review requirement | yes when relevant |
| V5.9 blocking decision         | yes when relevant |

### Enforcement

A component with no status MUST NOT be silently treated as ACTIVE_GREEN.

Gates must use component status ownership:

```text
ServiceProvider gates must respect component status
health gates must respect component status
autoload gates must respect component status
runtime gates must respect component status
production-readiness gates must respect component status
```

A ROADMAP/SCAFFOLD/LABS_ONLY component MUST NOT leak into production runtime autoload unless explicitly justified.

---

## 28. Final Verdict

> NOTE: This section is HISTORICAL. The current project state is GREEN across all V1-V5 stages as proven by
> CURRENT_TRUTH.md (2026-05-15). 8351 tests pass, PHPStan 0 errors, all gates GREEN. The content below is preserved to
> document the previous RED state.

Historical verdict (pre-V2 convergence):

```text
RED
```

Historical reason:

```text
AvaX had meaningful architecture and component recovery progress, but production readiness was blocked by component PHPStan errors, broken reference audit issues, incomplete test-layer repair, incomplete runtime safety proof, and incomplete component-level validation.
```

Current actual status (2026-05-15):

```text
GREEN — V1 Kernel Green proven, V2-V5 all complete, all production-readiness gates PASS.
```

See `CURRENT_TRUTH.md` for current evidence.
