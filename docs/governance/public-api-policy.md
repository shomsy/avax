# AvaX Public API Policy

**Date:** 2026-05-02
**Stage:** Stage 03 — API Classification and Evolution Rules
**Status:** ACTIVE

---

## 0. Purpose

This document defines what constitutes a public, internal, experimental, or deprecated API in AvaX.
Every class, method, and function must have a declared stability contract.
No API may be called stable without explicit classification.

---

## 1. Stability Levels

### `@public` — Public API

- Fully supported, semantically versioned, breaking changes require deprecation notice
- Examples: `Avax\Framework\System\PublicSurface\*`, `Avax\Components\*\System\PublicSurface\*`
- May be used by application code, other components, and external packages
- Change policy: major version bump for breaking changes, minor for backward-compatible additions

### `@internal` — Internal API

- Supported but may change at any time without notice
- Examples: `Avax\Framework\System\Capabilities\*`, `Avax\Components\*\System\Capabilities\*`,
  `Avax\Components\*\System\Flows\*`
- May not be used by application code or external packages
- Tooling enforcement: `@internal` annotation marks the class/method as internal
- Change policy: no formal versioning; may break at any commit

### `@experimental` — Experimental API

- Early implementation; may change or be removed without notice
- Examples: new components not yet in canonical taxonomy, preview features
- Change policy: no guarantees; use at your own risk

### `@deprecated` — Deprecated API

- Scheduled for removal; use the replacement API
- Must include `@deprecated` annotation with replacement hint
- Change policy: must remain functional for at least one minor version after deprecation

### `@removed-in` — Removed API

- Completely removed; no longer available
- Change policy: documented removal with migration path

---

## 2. PublicSurface Rule

> **PublicSurface receives. Flows execute. Capabilities power. Configuration assembles. Foundation supports.**

Only classes in `System/PublicSurface/` folders may be `@public`.
Internal implementation classes (`Capabilities`, `Flows`, `Configuration`, `Foundation`) are `@internal` by default.

Rationale: the PublicSurface lane is the stable entry point; everything behind it is an implementation detail.

---

## 3. Breaking Change Definition

A breaking change is any change that causes a previously valid call site to stop working:

```text
[ ] Removing a public method
[ ] Changing a public method signature
[ ] Changing a return type to an incompatible type
[ ] Changing an exception type to a less-specific exception
[ ] Removing a `@public` constant
[ ] Removing a `@public` property or changing its type
```

A non-breaking (backward-compatible) change:

```text
[ ] Adding a new optional parameter with a default
[ ] Adding a new `@public` method to a class
[ ] Adding a new `@public` constant
[ ] Relaxing a return type (covariant return)
[ ] Tightening an exception type (throws more specific exception)
```

---

## 4. Deprecation Process

1. Mark the API `@deprecated` in the docblock
2. Include the replacement in the deprecation message
3. Log a deprecation warning at runtime
4. Keep the deprecated API functional for at least one minor version
5. Announce in the changelog with migration path

Deprecation minimum retention: one minor version cycle.

---

## 5. API Review Contract

Before marking an API as `@public` and before any public API change:

```text
[ ] Review the change against this policy
[ ] Ensure no internal capability leaks into PublicSurface
[ ] Ensure the change is documented in the component's how-to-*.md
[ ] Ensure tests exist for the public contract
[ ] Run php tooling/refactor/check-public-surface.php
```

---

## 6. Enforcement

- PHPStan level 8 enforces type contracts
- PHPStan `@internal` annotation enforcement requires phpstan/phpstan 1.11+
- PublicSurface checker (`check-public-surface.php`) enforces the PublicSurface lane rule
- Git pre-commit hook should warn on unannotated `@public` classes outside PublicSurface

---

## 7. Versioning Scope

AvaX follows Semantic Versioning 2.0.0:

```text
MAJOR.MINOR.PATCH

MAJOR = breaking changes to @public APIs
MINOR = backward-compatible additions to @public APIs
PATCH = backward-compatible bug fixes
```

Internal APIs (`@internal`) are not versioned and may change arbitrarily.

---

## 8. Exceptions

The following are exempt from the public API versioning rules:

```text
[ ] Final classes with no public contract (pure internal implementation)
[ ] Classes marked `@internal` explicitly
[ ] Test fixtures under tests/
[ ] Tooling scripts under tooling/
[ ] Documentation-only classes under docs/
```

---

## 9. Related Documents

- `docs/governance/deprecation-policy.md` — deprecation lifecycle and timelines
- `docs/governance/compatibility-policy.md` — compatibility bridges and alias lifecycle
- `Code-Review-And-ToDo/api/api-classification-matrix.md` — per-component API classification