# ARCHITECTURE NOTES

## Phase 0

- System Type: shared library / internal foundation component
- Primary Consumers: internal application code and other foundation components
- Runtime Context: mixed
- Lifecycle: stable utility core under structural normalization
- Intended Use-Cases:
  - immutable-style collection transforms
  - raw array ergonomics through `Arrhae`
  - dot-path reads/writes and data reshaping
- Anti-Use-Cases:
  - ORM/entity modeling
  - validation framework replacement
  - ad-hoc dumping ground for unrelated helpers
- Non-Goals:
  - generic DTO framework
  - schema or serialization protocol layer
- Public API Stability Requirement: moderate
- Backwards Compatibility: required for root facades
- Performance Budget: typical in-memory collection workloads

Primary axis:

> This system is fundamentally organized around **collection capabilities**.

Secondary axis:

> Secondary axis: **root façade split** between `Arrhae` and `Collection`.

This is how the system actually works.

1. Callers enter through `Arrhae` or `Collection`.
2. Root facades own the public API and delegate most behavior into `Collections/*`.
3. `Internal/` provides low-level primitives like dot paths, normalization, mutation guards, and thresholds.
4. Capability slices (`Read`, `Write`, `Transform`, `Aggregate`, `Order`, `Search`, `Convert`, `Strings`, `Create`) each own one family of operations.
5. Component-specific exceptions terminate through `Foundation/Exceptions`.

# FINDINGS

### Finding: Contract path did not match namespace

- Symptom: `CollectionInterface` was skipped by Composer PSR-4 loading.
- Root Cause: the contract lived at component root while its namespace declared `Contracts`.
- Impact: the public contract boundary was structurally invalid.
- Evidence: moved `Foundation/DataModeling/CollectionInterface.php` to `Foundation/DataModeling/Contracts/CollectionInterface.php`.
- Risk Level: High

### Finding: Component-local tests lived in the production autoload tree

- Symptom: Composer reported DataModeling characterization tests from `Foundation/tests/...` as PSR-4 violations.
- Root Cause: tests were stored under `Foundation/` instead of `tests/`.
- Impact: production autoload was polluted by test files and noisy warnings hid real component issues.
- Evidence: moved characterization tests to `tests/Foundation/DataModeling/*`.
- Risk Level: Medium

### Finding: Arrhae immutability lock was effectively disabled

- Symptom: `lock()`, `toImmutable()`, and mutation guards did not enforce anything.
- Root Cause: `assertNotLocked()` was a no-op and `isLocked()` always returned false.
- Impact: callers could believe they had immutable state while mutation remained permitted.
- Evidence: repaired in `Foundation/DataModeling/Arrhae.php` with `CollectionMutationGuard`.
- Risk Level: High

### Finding: Documentation mirror was incomplete and structurally ambiguous

- Symptom: the component had markdown mirrors and a `docs/` folder, but several ownership folders were undocumented and repo-level docs were absent.
- Root Cause: the original refactor stopped after code slicing and did not finish governance artifacts.
- Impact: reviewability and future refactor safety degrade quickly.
- Evidence: completed missing `how-this-works.md` files and added `docs/Foundation/DataModeling/*`.
- Risk Level: Medium

### Finding: Collection API still has one legacy edge

- Symptom: `Collection::pull()` does not fit naturally with the immutable-first rest of the API.
- Root Cause: historical method shape returns `mixed` while the component otherwise returns new instances for write-like operations.
- Impact: this is a future maintenance trap and should not be expanded further without explicit contract review.
- Evidence: `Foundation/DataModeling/Collection.php`, `Contracts/CollectionInterface.php`.
- Risk Level: Medium

# DECISION

Keep and Improve. The component axis is sound: root facades are small and capability slices are explicit. The main problems were structural incompleteness and one legacy API seam, not a fundamentally wrong architecture. The correct next action is incremental hardening, not redesign.

# DECISIONS-LOG

- 2026-04-23: normalized `CollectionInterface` into a PSR-4-safe `Contracts` boundary.
- 2026-04-23: moved DataModeling characterization tests out of production autoload scope.
- 2026-04-23: kept `Arrhae` and `Collection` as separate public entries instead of merging them.
- 2026-04-23: documented the remaining `pull()` contract tension instead of hiding it.

# NEXT STEPS

- Add explicit consumer-approved tests for `Collection::pull()` before changing its public behavior.
- Run the DataModeling characterization suite in an environment with PHPUnit installed.
- Continue migrating long-form docs toward `docs/Foundation/DataModeling`.
