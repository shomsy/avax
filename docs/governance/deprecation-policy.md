# AvaX Deprecation Policy

**Date:** 2026-05-02
**Stage:** Stage 03 — API Classification and Evolution Rules
**Status:** ACTIVE

---

## 0. Purpose

This document defines how AvaX handles the lifecycle of deprecated APIs — from announcement to removal.
Deprecation is not optional; once an API is marked deprecated it must follow this policy.

---

## 1. When to Deprecate

Deprecate an API when:

```text
[ ] It is superseded by a better-designed replacement
[ ] It has a security vulnerability that cannot be fixed without breaking
[ ] It carries legacy namespace or architecture that blocks progress
[ ] It duplicates functionality available elsewhere with clearer semantics
```

Do NOT deprecate:

```text
[ ] To force migration for cosmetic reasons
[ ] To remove a feature without replacement
[ ] To change an internal capability (use @internal, not @deprecated)
```

---

## 2. Deprecation Lifecycle

### Phase 1 — Announce

Mark the API `@deprecated` and document:

```php
/**
 * @deprecated since 2.3, use {@see NewApi::create()} instead.
 *              Removal planned in 3.0.
 */
```

Add runtime warning (optional but recommended for critical paths):

```php
trigger_deprecation('avax/framework', '2.3', 'OldApi is deprecated, use NewApi::create() instead.');
```

### Phase 2 — Functional Retention

The deprecated API must remain **fully functional** for at minimum:

```text
1 minor version cycle after deprecation announcement
```

e.g. deprecated in 2.3 → must work at least through 2.x → removable in 3.0

### Phase 3 — Removal

- Announce removal in changelog with migration path
- Mark documentation and migration notes with `@removed-in <version>`
- Remove the deprecated code only in a major version bump
- Ensure the replacement API has been available since at least one minor version before removal

---

## 3. Minimum Deprecation Notice

| Deprecation Type | Minimum Notice           | Removal Timeline    |
|------------------|--------------------------|---------------------|
| Security removal | 1 minor version          | As soon as feasible |
| Breaking change  | 1 minor version          | Next major version  |
| Feature removal  | 2 minor versions         | Major version       |
| Namespace change | 2 minor versions + alias | Major version       |

---

## 4. Alias Strategy for Namespace Changes

When a namespace must change:

```php
// NEW location
namespace Avax\Components\New\Location;

// OLD location aliased for backward compatibility
class_alias(
    'Avax\Components\Old\Location\OldClass',
    'Avax\Components\New\Location\OldClass',
    ['deprecation' => 'avax/components', '2.5', 'Use Avax\Components\New\Location\NewClass instead.']
);
```

The alias should trigger deprecation notices when used.

---

## 5. Deprecated API Rules

```text
[ ] A deprecated API must not throw new exceptions (except Security removals)
[ ] A deprecated API must not introduce new behavior (additions OK, changes not)
[ ] A deprecated API must remain type-compatible with existing call sites
[ ] Documentation must be updated to show the replacement
[ ] The deprecation notice must be readable in IDE tooltips and during static analysis
```

---

## 6. Related Documents

- `docs/governance/public-api-policy.md` — API classification and stability levels
- `docs/governance/compatibility-policy.md` — compatibility bridges and alias lifecycle
