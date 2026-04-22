# Release/Governance Closure Support

This document provides artifacts for final closure after Codex implementation of slice views and pooled lifetime.

## Acceptance Checklist

### Slice View Features

- [ ] AC-101: Slice-local visibility enforced through real slice views
    - [ ] `debugGraph(sliceId)` filters to specific slice
    - [ ] `validate([sliceIds])` validates only specified slices
    - [ ] Cross-slice access violations detected and reported
    - [ ] Slice manifest available in diagnostics output

- [ ] AC-102: Capability exports/imports explicit and diagnosable
    - [ ] `export()` marks service as available to importers
    - [ ] `import(capabilityId)` declares slice dependency
    - [ ] `describeService()` shows imports/exports arrays
    - [ ] Cross-slice violations include consumer AND provider in message

- [ ] AC-105: Graph artifacts exportable
    - [ ] `debugGraph()` returns JSON-serializable structure
    - [ ] `compileReport()` includes graph snapshot
    - [ ] `runtimeReport()` includes live graph state

### Pooled Lifetime Features

- [ ] AC-103: Pooled lifetime first-class, safe, benchmarked, explainable
    - [ ] `pooled()` registration works
    - [ ] `maxPoolSize()` configures pool limit
    - [ ] `onOverflow()` configures overflow behavior
    - [ ] `resetWith()` declares reset behavior
    - [ ] `resettable()` uses class reset() method
    - [ ] Unsafe usage produces validation warnings
    - [ ] Benchmarks show reuse efficiency

### Supporting Features

- [ ] AC-106: Structural diff explains ownership and dependency changes
    - [ ] Slice manifests in debug output
    - [ ] Ownership metadata in compile artifacts

- [ ] AC-110: Policy engine can warn/fail on architectural anti-patterns
    - [ ] Shared captures scoped detection
    - [ ] Disposable transient detection
    - [ ] Duplicate concept detection
    - [ ] Cyclic dependency detection
    - [ ] Unexported cross-slice access detection

- [ ] AC-111: Docs and diagnostics cover every new capability
    - [ ] Slice view contracts documented
    - [ ] Pooled lifetime contracts documented
    - [ ] Diagnostics outputs documented
    - [ ] Policy rules documented

## Evidence Checklist

### Tests Evidence

- [ ] `SliceVisibilitySmokeTest.php` passes
- [ ] `ExportImportDiagnosticsSmokeTest.php` passes
- [ ] `SliceValidationSmokeTest.php` passes
- [ ] `PooledLifetimeSmokeTest.php` passes
- [ ] `PooledDiagnosticsSmokeTest.php` passes
- [ ] `PolicyEnforcementSmokeTest.php` passes
- [ ] `GraphExportSmokeTest.php` passes

### Documentation Evidence

- [ ] `slice-view-contracts.md` exists and complete
- [ ] `pooled-lifetime-contracts.md` exists and complete
- [ ] `public-contract-matrix-update.md` exists and complete
- [ ] `testing-guidance-wave.md` exists and complete
- [ ] `glossary.md` updated with new terms
- [ ] `ownership-model.md` updated with slice view details

### Diagnostics Evidence

- [ ] `debugGraph()` includes slice manifests
- [ ] `describeService()` includes imports/exports
- [ ] `validate()` returns structured ownership issues
- [ ] `runtimeReport()` includes pool statistics
- [ ] Compile report includes derived slices

### Benchmark Evidence

- [ ] Slice view benchmarks run without errors
- [ ] Pooled lifetime benchmarks run without errors
- [ ] Benchmarks show expected efficiency ratios

## Docs Readiness Checklist

### Contracts

- [ ] Slice view types defined (root, flow, capability, configuration, foundation)
- [ ] Visibility matrix documented
- [ ] Cross-slice access rules documented
- [ ] Pooled lifetime semantics documented
- [ ] Reset-before-reuse contract documented

### Public API

- [ ] New methods documented in public contract matrix
- [ ] Method signatures and behavior documented
- [ ] Error conditions documented
- [ ] Return types documented

### Diagnostics

- [ ] All new diagnostic outputs documented
- [ ] JSON structures documented with examples
- [ ] Policy rule messages documented

### Testing

- [ ] Test guidance for slice visibility documented
- [ ] Test guidance for pooled lifetime documented
- [ ] Test guidance for anti-pattern enforcement documented

## Release Note Delta

### New Features

- **Slice Views**: Logical projections of container service graph filtered by slice (flow, capability, configuration,
  foundation)
- **Cross-Slice Access Control**: Explicit export/import contracts enforced at runtime and validation time
- **Pooled Lifetime**: Reusable pool of service instances with reset-before-reuse semantics
- **Enhanced Diagnostics**: Slice manifests, ownership reports, pool statistics, policy violations

### Breaking Changes

- None expected (all new surfaces are additive)

### Deprecations

- None

### New Errors

- Cross-slice access violations now produce structured diagnostic messages
- Pool overflow now configurable with explicit strategies
- Policy violations now reported in structured format

### Performance Impact

- Slice view operations: minimal overhead (filtering only)
- Pooled lifetime: significant savings for high-frequency services
- Diagnostics: proportional to diagnostics mode setting

## World-Class-Readiness Additions

### Explainability

- Every error message includes context (consumer, provider, slice)
- `describeService()` provides complete ownership metadata
- `debugGraph()` explains dependencies and dependents
- `validate()` groups issues by category

### Debugging

- Slice manifests show what's in each slice
- Impact analysis shows what would break if service changes
- Dead registration detection finds unreachable services
- Duplicate concept detection finds conflicting registrations

### Safety

- Pooled lifetime requires explicit reset contract
- Validation catches unsafe patterns before runtime
- Cross-slice access requires explicit declarations
- Visibility enforcement prevents accidental coupling

### Performance

- Slice filtering is O(n) where n = services in slice
- Pool reuse avoids allocation overhead
- Compile-time graph snapshot available for fast diagnostics
- Metrics collected with minimal overhead

---

*This checklist is owned by the architecture-contract agent. Codex implements and validates.*
