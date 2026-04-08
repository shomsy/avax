# Acceptance Criteria Mapping

This document maps every acceptance criterion to evidence, tests, and validation.

## AC-101: Slice-Local Visibility Is Enforced Through Real Slice Views

**Criterion**: Slice views filter services based on ownership metadata and import/export declarations

**Evidence**:
- `debugGraph('flow.login')` returns only flow.login's services + imported capabilities
- `validate(['flow.login'])` reports cross-slice access violations
- `describeService()['slice']` returns slice metadata

**Tests**:
- `SliceVisibilitySmokeTest.php` - tests slice filtering
- `ExportImportDiagnosticsSmokeTest.php` - tests import/export
- `SliceValidationSmokeTest.php` - tests slice validation

**Validation**:
- PHP lint passes
- Smoke tests pass
- Diagnostics contract validation passes

---

## AC-102: Capability Exports/Imports Are Explicit and Diagnosable

**Criterion**: Every cross-slice access is traceable to explicit export/import declarations

**Evidence**:
- `describeService()['imports']` array shows declared imports
- `describeService()['exports']` array shows declared exports
- Cross-slice violations include both consumer and provider in error message

**Tests**:
- `ExportImportDiagnosticsSmokeTest.php` - tests explicit export/import
- `OwnershipCompositionSmokeTest.php` - tests ownership metadata

**Validation**:
- PHP lint passes
- Smoke tests pass
- Cross-slice errors are actionable

---

## AC-103: Pooled Lifetime Is First-Class, Safe, Benchmarked, and Explainable

**Criterion**: Pooled lifetime is a documented, validated, benchmarked lifetime with clear safety rules

**Evidence**:
- `pooled()` registration works with config
- `resetWith()` or `ResettableInterface` required
- Unsafe usage produces validation warnings
- Benchmarks show reuse efficiency

**Tests**:
- `PooledLifetimeSmokeTest.php` - tests pool registration and reset
- `PooledDiagnosticsSmokeTest.php` - tests pool diagnostics

**Benchmarks**:
- `bench_pooled_reuse` - reuse pooled instance
- `bench_pooled_reset` - reset and reuse
- Expected: pooled_reuse/transient_create < 0.3

**Validation**:
- PHP lint passes
- Smoke tests pass
- Benchmarks pass with expected ratios

---

## AC-104: Async Boundary Behavior Is Explicit, Safe, and Documented (If/When Supported)

**Criterion**: Async boundaries have explicit contracts if/when implemented

**Evidence**:
- Document in `async-boundaries.md`
- Marked as future work
- Current state: synchronous-only

**Tests**: N/A (not implemented)

**Validation**: Documentation completeness

---

## AC-105: Graph Artifacts Are Exportable in Machine-Readable and Human-Readable Formats

**Criterion**: Graph can be exported in JSON and text formats

**Evidence**:
- `debugGraph()` returns JSON-serializable structure
- `compileReport()` includes graph snapshot
- `runtimeReport()` includes live graph state

**Tests**:
- `GraphExportSmokeTest.php` - tests export formats

**Validation**:
- JSON output is valid
- Text output is readable
- Machine-readable exports parse correctly

---

## AC-106: Structural Diff Explains Ownership and Dependency Changes

**Criterion**: Structural diff includes ownership and dependency changes

**Evidence**:
- `debugGraph()['slices']` shows slice manifests
- Compile report includes derived ownership maps
- Structural diff includes slice changes

**Tests**:
- `PolicyAndStructureDiffSmokeTest.php` - tests structure diff

**Validation**:
- Slice manifests present in output
- Ownership metadata preserved in compile

---

## AC-107: Generated/AOT Mode Is First-Class and Benchmarked (If/When Implemented)

**Criterion**: AOT mode is supported and benchmarked if implemented

**Evidence**:
- Compile mode docs exist in `build-mode.md`, `compile-artifact-model.md`
- Current: compiled container is implemented

**Tests**: Implemented via existing compile tests

**Benchmarks**: `cold_boot`, `warm_boot`, `compile_time`

---

## AC-108: Static Analysis Hints Are Generated and CI-Validated (If/When Implemented)

**Criterion**: Static analysis hints are available if implemented

**Evidence**:
- Policy engine docs exist in `policy-engine.md`
- Current: validation catches anti-patterns

**Tests**: Implemented via existing policy tests

---

## AC-109: Graph Pruning Is Safe, Mode-Bound, and Reported (If/When Implemented)

**Criterion**: Graph pruning follows safety rules if implemented

**Evidence**:
- Dead registration detection exists
- Validation reports unreachable services
- Current: dead registrations are diagnostic only

**Tests**: Implemented via `validate()` tests

---

## AC-110: Policy Engine Can Warn/Fail on Architectural Anti-Patterns

**Criterion**: Policy engine detects and reports anti-patterns

**Evidence**:
- `validate()['policy']` returns policy violations
- Detection for: shared_captures_scoped, disposable_transient, duplicate_concept, cyclic_dependency

**Tests**:
- `PolicyEnforcementSmokeTest.php` - tests anti-pattern detection

**Validation**:
- All documented patterns detected
- Error messages are actionable

---

## AC-111: Docs and Diagnostics Cover Every New Capability

**Criterion**: Every new capability is documented

**Evidence**:
- `slice-view-contracts.md` - slice views
- `pooled-lifetime-contracts.md` - pooled lifetime
- `public-contract-matrix-update.md` - API surface
- `testing-guidance-wave.md` - testing guidance

**Tests**: N/A (documentation)

**Validation**:
- All new surfaces documented
- All diagnostics outputs documented

---

## AC-112: Final Report Maps Every Criterion to Evidence, Tests, and Validation

**Criterion**: Every criterion has mapped evidence, tests, and validation

**Evidence**: This mapping document

**Validation**: This document provides complete mapping

---

## Test Location Reference

| Criterion | Test File | Location |
|:----------|:----------|:---------|
| AC-101 | SliceVisibilitySmokeTest.php | `tests/DependencyInjection/Flows/SliceView/` |
| AC-101 | ExportImportDiagnosticsSmokeTest.php | `tests/DependencyInjection/Flows/SliceView/` |
| AC-101 | SliceValidationSmokeTest.php | `tests/DependencyInjection/Flows/SliceView/` |
| AC-102 | ExportImportDiagnosticsSmokeTest.php | `tests/DependencyInjection/Flows/SliceView/` |
| AC-102 | OwnershipCompositionSmokeTest.php | `tests/DependencyInjection/Flows/ResolveService/` |
| AC-103 | PooledLifetimeSmokeTest.php | `tests/DependencyInjection/Flows/RegisterServices/` |
| AC-103 | PooledDiagnosticsSmokeTest.php | `tests/DependencyInjection/Diagnostics/` |
| AC-105 | GraphExportSmokeTest.php | `tests/DependencyInjection/Diagnostics/` |
| AC-106 | PolicyAndStructureDiffSmokeTest.php | `tests/DependencyInjection/Flows/ResolveService/` |
| AC-110 | PolicyEnforcementSmokeTest.php | `tests/DependencyInjection/Diagnostics/` |

---

*This mapping is owned by the architecture-contract agent.*
