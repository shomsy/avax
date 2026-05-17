# Early Governance Gap Report

## Status

**Stage:** V5 Readiness — Early Governance Gap Analysis
**Date:** 2026-05-10
**Mode:** Standard

---

## 1. Purpose

This report identifies the gaps between current AvaX V4 codebase state and the mandatory governance set defined in
`.agents/how-to/how-to-*.md` (15 documents).

This is NOT a full code review. It is an early gap analysis to identify what must be addressed during V5 implementation
readiness.

---

## 2. Governance Inventory

All 15 mandatory how-to documents discovered:

| #  | Document                                       | Purpose                                                                      | Scope            |
|----|------------------------------------------------|------------------------------------------------------------------------------|------------------|
| 1  | `how-to-architecture.md`                       | Fractal flow, recursive ownership, screaming architecture                    | Architecture     |
| 2  | `how-to-architecture-extension-with-ddd.md`    | DDD application, bounded contexts, entities, value objects                   | Architecture     |
| 3  | `how-to-clean-code.md`                         | Correctness, readability, simplicity, naming, error handling                 | Clean Code       |
| 4  | `how-to-code-review.md`                        | Enterprise-grade review process, compliance matrix                           | Review Process   |
| 5  | `how-to-code-style.md`                         | Project-specific formatting, typing, imports, PHP 8.x style                  | Code Style       |
| 6  | `how-to-coding-standards.md`                   | PHP 8.5 expectations, security, DevSecOps, modern features                   | Coding Standards |
| 7  | `how-to-design-components.md`                  | Component lifecycle, canonical shape, platform planes                        | Architecture     |
| 8  | `how-to-document.md`                           | Documentation location, how-this-works, mermaid, debug-first                 | Documentation    |
| 9  | `how-to-dogfooding.md`                         | Internal component reuse, one capability one owner, adoption matrix          | Architecture     |
| 10 | `how-to-modern-php-attributes-di.md`           | PHP 8.0-8.5, attributes, DI/autowiring, compiled metadata, tooling gates     | Coding Standards |
| 11 | `how-to-production-readiness.md`               | Production gates, health checks, runtime safety, failure handling            | Operations       |
| 12 | `how-to-system-performance.md`                 | Hot paths, hidden I/O, latency budgets, memory management                    | Performance      |
| 13 | `how-to-system-security.md`                    | Security boundaries, authN/authZ, secrets, input validation, output encoding | Security         |
| 14 | `how-to-unit-test.md`                          | Behavior-first tests, happy/failure/edge/security scenarios                  | Testing          |
| 15 | `how-to-use-advanced-architecture-patterns.md` | GoF patterns, event sourcing, CQRS, advanced patterns                        | Architecture     |

**Total governance documents found:** 15
**Total applicable to this review:** 15 (all are mandatory)

---

## 3. Gap Analysis by Governance Document

### 3.1 how-to-architecture.md

| Rule                                                  | Status   | Evidence                                       | Gap                                                                               |
|-------------------------------------------------------|----------|------------------------------------------------|-----------------------------------------------------------------------------------|
| Folder says flow/capability, unit says responsibility | Partial  | Most components follow canonical shape         | `components/API/Contracts` uses concept word as folder name — needs justification |
| Fractal flow / recursive ownership                    | Partial  | Framework System follows shape well            | Some component-level folders need ownership verification                          |
| No forbidden structural patterns                      | Partial  | Tooling exists (`check-forbidden-folders.php`) | `Contracts` folder, duplicate `tooling/Architecture/` vs `tooling/architecture/`  |
| Screaming architecture                                | Pass     | Areas and components say domain                | 15 area names scream domain behavior                                              |
| Flow locality rule                                    | Partial  | Framework flows are well-localized             | Need component-level flow ownership verification                                  |
| Shared last rule                                      | Unproven | —                                              | Requires capability ownership scan (Part 4)                                       |

**Severity:** Medium — structural gaps exist but framework baseline is sound.

### 3.2 how-to-architecture-extension-with-ddd.md

| Rule                            | Status   | Evidence                           | Gap                                                                       |
|---------------------------------|----------|------------------------------------|---------------------------------------------------------------------------|
| Bounded context boundaries      | Partial  | Areas provide top-level boundaries | Need explicit bounded context documentation                               |
| Entity/value object discipline  | Unproven | —                                  | Component-level verification needed                                       |
| DDD tactical patterns placement | Unproven | —                                  | Need to verify patterns live in flows/capabilities, not technical buckets |

**Severity:** Low — V4 baseline focuses on framework, not application DDD. This is more relevant for V5
application-layer work.

### 3.3 how-to-clean-code.md

| Rule                        | Status  | Evidence                             | Gap                                                                           |
|-----------------------------|---------|--------------------------------------|-------------------------------------------------------------------------------|
| Correctness and readability | Partial | PHPStan 0 errors, 7451 tests passing | Spot-check needed on complex flows                                            |
| Naming discipline           | Partial | Most names are action-oriented       | Some technical folder names exist (`tooling/Refactor/`, `tooling/PreCommit/`) |
| Error handling              | Partial | `RenderApplicationError` exists      | Need to verify all flows have explicit failure behavior                       |
| Simplicity over cleverness  | Partial | `Avax::create()` is simple           | `RunApplication` internal resolver construction is opaque                     |

**Severity:** Low — code quality baseline is strong.

### 3.4 how-to-code-style.md

| Rule                           | Status  | Evidence                                      | Gap                                              |
|--------------------------------|---------|-----------------------------------------------|--------------------------------------------------|
| `declare(strict_types=1)`      | Pass    | All reviewed files have it                    | —                                                |
| Constructor property promotion | Partial | Used in `RuntimeRequest`, `CreateApplication` | Mixed usage across codebase — needs verification |
| PHP 8.x modern features        | Partial | `readonly`, `final`, typed properties used    | Need full adoption analysis                      |
| Named arguments                | Partial | Used in `Avax.php:62-73`                      | Need to verify consistent usage                  |

**Severity:** Low — style baseline is good, adoption verification needed.

### 3.5 how-to-coding-standards.md

| Rule                      | Status   | Evidence                                      | Gap                                            |
|---------------------------|----------|-----------------------------------------------|------------------------------------------------|
| PHP 8.5 where supported   | Partial  | PHP 8.x features used                         | Need PHP version target verification           |
| Security standards        | Partial  | `SensitiveParameter` usage needs verification | Security component exists but coverage unknown |
| OWASP/NIST/SLSA awareness | Unproven | —                                             | Requires security audit (separate review)      |

**Severity:** Medium — security and PHP version compliance need evidence.

### 3.6 how-to-design-components.md

| Rule                                     | Status  | Evidence                                                              | Gap                                                                |
|------------------------------------------|---------|-----------------------------------------------------------------------|--------------------------------------------------------------------|
| Canonical component shape                | Partial | `System/PublicSurface/Flows/Capabilities/Configuration/Foundation`    | 74 components need individual shape verification                   |
| Component completion standard (15 items) | Fail    | Only 5 READMEs for 74 components                                      | Most components lack documentation, examples, operator diagnostics |
| Platform plane model                     | Partial | Runtime, control plane, contract plane concepts exist                 | Full plane coverage not proven                                     |
| No forbidden top-level System folders    | Partial | Tooling exists                                                        | `Contracts` in API area needs justification                        |
| PublicSurface delegation                 | Pass    | `Avax`, `App` delegate correctly                                      | —                                                                  |
| Flows say action                         | Pass    | Flow names are action-oriented (`HandleIncomingHttp`, `RunMigration`) | —                                                                  |
| Capabilities say ability                 | Partial | Most capability names are ability-oriented                            | Need full capability naming audit                                  |

**Severity:** High — component completion standard is far from met across 74 components.

### 3.7 how-to-document.md

| Rule                         | Status   | Evidence                                | Gap                                            |
|------------------------------|----------|-----------------------------------------|------------------------------------------------|
| docs/ canonical location     | Pass     | `docs/` directory exists with structure | —                                              |
| Component README allowed     | Partial  | 5 READMEs exist                         | 69 components lack local ownership summaries   |
| how-this-works documentation | Unproven | —                                       | Need to verify key flows have documentation    |
| Mermaid diagrams             | Unproven | —                                       | Need to verify documentation includes diagrams |
| Debug-first guidance         | Unproven | —                                       | Need to verify docs include troubleshooting    |

**Severity:** Medium — documentation exists but is incomplete relative to 74 components.

### 3.8 how-to-dogfooding.md

| Rule                                             | Status   | Evidence                              | Gap                                                                                               |
|--------------------------------------------------|----------|---------------------------------------|---------------------------------------------------------------------------------------------------|
| One capability, one owner                        | Unproven | —                                     | Requires capability ownership scan (Part 4)                                                       |
| No duplicate implementations                     | Unproven | —                                     | Preliminary: `Filesystem` in Application and Operations, `Storage` in Application and Integration |
| Adoption matrix                                  | Unproven | —                                     | Requires dogfooding adoption matrix (Part 5)                                                      |
| No raw file/process/serialization outside owners | Unproven | —                                     | Requires focused code audit                                                                       |
| PublicSurface thinness                           | Pass     | Framework public surface is thin      | —                                                                                                 |
| Hot-path efficiency                              | Partial  | `App->run()` builds from superglobals | Not hot-path issue but boundary concern                                                           |

**Severity:** High — dogfooding compliance is the primary V5 target and is currently unproven.

### 3.9 how-to-modern-php-attributes-di.md

| Rule                                     | Status   | Evidence                                                     | Gap                                                |
|------------------------------------------|----------|--------------------------------------------------------------|----------------------------------------------------|
| Attribute compilation                    | Unproven | —                                                            | Need to verify attributes exist and have compilers |
| No reflection per request                | Fail     | `RunApplication.php:165` uses `ReflectionMethod` per request | V4 design, V5 must fix                             |
| DI/autowiring discipline                 | Partial  | `RouteFacadeContainer` provides minimal DI                   | Full autowiring not yet implemented                |
| Constructor bloat (0-4 normal)           | Partial  | `Runtime` has 8 params, `Avax` has 5                         | At warning/check threshold                         |
| Compiled metadata vs hot-path reflection | Fail     | No compiled metadata cache exists yet                        | V4 uses runtime resolution                         |
| Superglobal isolation                    | Fail     | `App.php:311-324` reads superglobals directly                | Should be delegated to Request component           |
| Tooling gates                            | Partial  | Many check scripts exist                                     | Need to verify all planned gates exist and pass    |

**Severity:** High — attribute compilation and hot-path reflection are core V5 targets.

### 3.10 how-to-unit-test.md

| Rule                         | Status   | Evidence                  | Gap                                      |
|------------------------------|----------|---------------------------|------------------------------------------|
| 7451 tests passing           | Pass     | CURRENT_TRUTH.md evidence | —                                        |
| Behavior-first tests         | Unproven | —                         | Need spot-check test quality             |
| Happy/failure/edge scenarios | Unproven | —                         | Need component-level test coverage audit |
| Security scenarios           | Unproven | —                         | Requires security test audit             |
| One-act rule                 | Unproven | —                         | Need spot-check test structure           |

**Severity:** Low — test count and pass rate are strong, quality verification needed.

### 3.11 how-to-system-security.md

| Rule                            | Status   | Evidence                                   | Gap                                                                               |
|---------------------------------|----------|--------------------------------------------|-----------------------------------------------------------------------------------|
| Security boundaries             | Unproven | —                                          | `components/Security/` exists but boundary coverage unknown                       |
| Authentication vs authorization | Unproven | —                                          | Identity and Security areas exist, implementation unknown                         |
| Secrets handling                | Unproven | —                                          | V5 plan identifies Session ID logging and SerializableClosure encryption as P0/P1 |
| Input validation                | Partial  | `components/Application/Validation` exists | Coverage unknown                                                                  |
| Output encoding                 | Unproven | —                                          | Requires security audit                                                           |

**Severity:** High — identified P0/P1 blockers (session ID logging, callable encryption) need fixing.

### 3.12 how-to-system-performance.md

| Rule                          | Status   | Evidence                                     | Gap                                                                |
|-------------------------------|----------|----------------------------------------------|--------------------------------------------------------------------|
| Hot path discipline           | Fail     | Per-request reflection in `RunApplication`   | V4 design, V5 must fix                                             |
| Hidden I/O                    | Partial  | `App->run()` does file read on `php://input` | Expected at boundary but should be explicit                        |
| Latency budgets               | Unproven | —                                            | V5.5 benchmarking will address                                     |
| Memory management             | Unproven | —                                            | Long-lived worker safety (V4-03) claimed GREEN, needs verification |
| No unbounded public operation | Unproven | —                                            | Requires performance audit                                         |

**Severity:** Medium — hot-path reflection is the primary performance gap.

### 3.13 how-to-production-readiness.md

| Rule                        | Status   | Evidence                         | Gap                                                |
|-----------------------------|----------|----------------------------------|----------------------------------------------------|
| Health checks               | Unproven | —                                | Component completion requires health/doctor checks |
| Runtime safety              | Partial  | `runtime:doctor` command claimed | Need to verify command exists and passes           |
| Failure handling            | Partial  | `RenderApplicationError` exists  | Need per-component failure model verification      |
| Doctor evidence             | Unproven | —                                | `php avax runtime:doctor` output needed            |
| Production-readiness report | Unproven | —                                | CURRENT_TRUTH.md claims but needs evidence         |

**Severity:** Medium — production readiness claims need evidence artifacts.

### 3.14 how-to-use-advanced-architecture-patterns.md

| Rule                        | Status   | Evidence                   | Gap                                                                 |
|-----------------------------|----------|----------------------------|---------------------------------------------------------------------|
| GoF pattern placement       | Unproven | —                          | Need to verify patterns live in capabilities, not technical buckets |
| Event sourcing / CQRS       | Roadmap  | V5/V5.5 planning documents | Not yet implemented                                                 |
| Advanced pattern discipline | Unproven | —                          | Need to verify existing patterns follow governance                  |

**Severity:** Low — most advanced patterns are roadmap items, not V4 gaps.

### 3.15 how-to-code-review.md

| Rule                                                 | Status  | Evidence                                     | Gap                                 |
|------------------------------------------------------|---------|----------------------------------------------|-------------------------------------|
| Review process defined                               | Pass    | Document exists                              | —                                   |
| Governance compliance matrix required                | Pass    | This report serves as early matrix           | Full review needed later            |
| Hard gates (as-built flow, primary axis, invariants) | Pass    | Provided in early-system-review-inventory.md | —                                   |
| Decision framework                                   | Pending | —                                            | Final decision requires full review |

**Severity:** Low — process is defined, execution of full review is pending.

---

## 4. Governance Compliance Summary

| Governance Document                          | Rules Checked |   Pass | Partial |  Fail | Unproven | Highest Severity |
|----------------------------------------------|--------------:|-------:|--------:|------:|---------:|------------------|
| how-to-architecture.md                       |             6 |      2 |       3 |     0 |        1 | Medium           |
| how-to-architecture-extension-with-ddd.md    |             3 |      0 |       1 |     0 |        2 | Low              |
| how-to-clean-code.md                         |             4 |      1 |       2 |     0 |        1 | Low              |
| how-to-code-style.md                         |             4 |      1 |       2 |     0 |        1 | Low              |
| how-to-coding-standards.md                   |             3 |      0 |       1 |     0 |        2 | Medium           |
| how-to-design-components.md                  |             7 |      2 |       2 |     0 |        3 | High             |
| how-to-document.md                           |             5 |      1 |       1 |     0 |        3 | Medium           |
| how-to-dogfooding.md                         |             6 |      1 |       1 |     0 |        4 | High             |
| how-to-modern-php-attributes-di.md           |             7 |      0 |       2 |     3 |        2 | High             |
| how-to-unit-test.md                          |             5 |      1 |       0 |     0 |        4 | Low              |
| how-to-system-security.md                    |             5 |      0 |       1 |     0 |        4 | High             |
| how-to-system-performance.md                 |             5 |      0 |       1 |     1 |        3 | Medium           |
| how-to-production-readiness.md               |             5 |      0 |       1 |     0 |        4 | Medium           |
| how-to-use-advanced-architecture-patterns.md |             3 |      0 |       0 |     0 |        3 | Low              |
| how-to-code-review.md                        |             4 |      3 |       0 |     0 |        1 | Low              |
| **TOTAL**                                    |        **67** | **12** |  **18** | **4** |   **33** | **High**         |

---

## 5. Governance Findings

### Governance Finding: Per-Request Reflection in Hot Path

- **Governance Source:** `how-to-modern-php-attributes-di.md` -> §7.3 Reflection Forbidden In
- **Required Rule:** No reflection on every controller invocation
- **Observed Gap:** `RunApplication.php:165` uses `ReflectionMethod->invokeArgs()` per request
- **Where It Fails:** `framework/System/Flows/RunApplication/RunApplication.php:151-168`
- **Why It Matters:** Performance degradation per request, violates compiled metadata discipline
- **Required Action:** Implement attribute compilation at boot time, use compiled metadata at runtime
- **Suggested Fix:** V5 must introduce `CompileControllerAttributes` and compiled route table
- **Severity:** High

### Governance Finding: Superglobal Access Outside Request Component

- **Governance Source:** `how-to-modern-php-attributes-di.md` -> §16 Superglobal Isolation Rule
- **Required Rule:** All superglobals must be isolated behind AvaX Request object
- **Observed Gap:** `App.php:311-324` reads `$_SERVER`, `php://input` directly
- **Where It Fails:** `framework/System/PublicSurface/App.php:311-324`
- **Why It Matters:** Breaks superglobal isolation contract, makes testing harder
- **Required Action:** Delegate superglobal access to Request component
- **Suggested Fix:** Create `BuildRuntimeRequestFromEnvironment` flow in appropriate component
- **Severity:** Medium

### Governance Finding: Component Completion Standard Not Met

- **Governance Source:** `how-to-design-components.md` -> §4 Component Completion Standard
- **Required Rule:** 15 items required per component (API, behavior, fakes, config, health, failure, tests, docs, etc.)
- **Observed Gap:** Only 5 of 74 components have READMEs; most lack documentation, examples, operator diagnostics
- **Where It Fails:** Across 69 of 74 components
- **Why It Matters:** Components are not platform-ready without completion evidence
- **Required Action:** Systematic component completion pass
- **Suggested Fix:** V5 dogfooding phase should drive completion
- **Severity:** High

### Governance Finding: Dogfooding Compliance Unproven

- **Governance Source:** `how-to-dogfooding.md` -> Core Law
- **Required Rule:** One capability = one owner, no duplicate implementations
- **Observed Gap:** Potential duplicates: Filesystem (Application + Operations), Storage (Application + Integration)
- **Where It Fails:** `components/Application/Filesystem/`, `components/Operations/Filesystem/`,
  `components/Application/Storage/`, `components/Integration/ObjectStorage/`
- **Why It Matters:** Duplicate capabilities increase maintenance cost and create ambiguity
- **Required Action:** Capability ownership scan and adoption matrix
- **Suggested Fix:** Parts 4 and 5 of this readiness pass
- **Severity:** High

### Governance Finding: Constructor Bloat at Warning Threshold

- **Governance Source:** `how-to-modern-php-attributes-di.md` -> §6.2 Constructor Bloat Rules
- **Required Rule:** 8+ dependencies = architecture warning
- **Observed Gap:** `Runtime` has 8 constructor parameters
- **Where It Fails:** `framework/System/Capabilities/Runtime/Runtime.php`
- **Why It Matters:** Indicates potential responsibility accumulation
- **Required Action:** Review Runtime responsibilities, consider extraction
- **Suggested Fix:** May be acceptable for central abstraction — needs explicit justification
- **Severity:** Medium

### Governance Finding: Duplicate Tooling Directories

- **Governance Source:** `how-to-architecture.md` -> §20 Forbidden Structural Patterns
- **Required Rule:** No duplicated capabilities, no unclear ownership
- **Observed Gap:** `tooling/Architecture/` and `tooling/architecture/` both exist with duplicated check scripts
- **Where It Fails:** `tooling/Architecture/check-*.php` and `tooling/architecture/check-*.php`
- **Why It Matters:** Maintenance confusion, unclear which is canonical
- **Required Action:** Consolidate tooling directories
- **Suggested Fix:** Keep `tooling/Architecture/` (PascalCase), remove `tooling/architecture/`
- **Severity:** Low

---

## 6. Governance Exceptions

No governance exceptions are requested in this report.

All findings above require action. No rules are waived.

---

## 7. Governance Coverage Summary

```
Governance documents found: 15
Governance documents applied: 15
Rules checked: 67
Passed: 12
Partial: 18
Failed: 4
Unproven: 33
Highest severity: High
```

Unproven count is high because many rules require component-level verification that is out of scope for this early
report. Parts 4 and 5 will address the unproven items.

---

## 8. Risk Assessment

| Risk Area                  | Level  | Description                                            |
|----------------------------|--------|--------------------------------------------------------|
| Hot-path performance       | High   | Per-request reflection must be eliminated in V5        |
| Component completeness     | High   | 69 of 74 components incomplete per completion standard |
| Dogfooding compliance      | High   | Capability ownership and adoption unproven             |
| Security P0/P1 blockers    | High   | Session ID logging, callable encryption need fixing    |
| Superglobal isolation      | Medium | App reads superglobals directly                        |
| Constructor bloat          | Medium | Runtime at 8 params warning threshold                  |
| Documentation completeness | Medium | 69 components lack documentation                       |
| Tooling fragmentation      | Low    | Duplicate directories but scripts exist                |
| Test quality               | Low    | 7451 tests pass, quality needs spot-check              |
| DDD compliance             | Low    | Framework-focused, application DDD not yet relevant    |

---

## 9. Next Steps

1. **Part 4:** Build capability ownership map to resolve unproven dogfooding items
2. **Part 5:** Create dogfooding adoption matrix
3. **Part 6:** Create initial tooling gates (security blockers, raw file operations, component adoption)
4. **Part 7:** Fix identified P0/P1 blockers:
    - Session ID logging redaction
    - SerializableClosure encryption
    - Service locator facade discipline
5. **Part 8:** Run validation suite and write final report

---

## 10. Evidence Pointers

| Claim                      | Evidence                                                       |
|----------------------------|----------------------------------------------------------------|
| Per-request reflection     | `framework/System/Flows/RunApplication/RunApplication.php:165` |
| Superglobal access         | `framework/System/PublicSurface/App.php:311-324`               |
| Component README count     | `find components -name "README.md" -type f` = 5                |
| Component count            | `find components -mindepth 2 -maxdepth 2 -type d` = 74         |
| Tooling duplication        | `ls tooling/Architecture/` and `ls tooling/architecture/`      |
| Runtime constructor params | Source code review of `Runtime` class                          |
| Governance document count  | `find .agents/how-to -name "how-to-*.md"` = 15                 |
