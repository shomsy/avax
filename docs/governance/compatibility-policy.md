# AvaX Compatibility Policy

**Date:** 2026-05-02
**Stage:** Stage 03 — API Classification and Evolution Rules
**Status:** ACTIVE

---

## 0. Purpose

This document defines how AvaX handles backward compatibility, compatibility bridges, and alias lifecycle.
It protects existing applications from breakage while enabling necessary architectural evolution.

---

## 1. Compatibility Tiers

### Tier 1 — Guaranteed Compatibility (`@public`)

Full semantic versioning. Breaking changes only in major versions.

**Affected paths:**

```text
Avax\Framework\System\PublicSurface\*
Avax\Components\*\System\PublicSurface\*
```

### Tier 2 — Recommended Compatibility (`@internal` but documented)

Components that application code should use but are not part of the stable public contract.

**Affected paths:**

```text
Avax\Framework\System\Flows\*
Avax\Components\*\System\Capabilities\*
Avax\Components\*\System\Flows\*
```

### Tier 3 — No Compatibility Guarantee (`@internal`, `@experimental`)

Internal implementation; may change at any time.

**Affected paths:**

```text
Avax\Framework\System\Capabilities\*
Avax\Components\*\System\Configuration\*
Avax\Components\*\System\Foundation\*
```

---

## 2. Compatibility Bridge Rules

Compatibility bridges (class aliases, facades, wrapper classes) are allowed under these conditions:

```text
[ ] The bridge has a documented deprecation timeline
[ ] The bridge is tested (at minimum smoke test)
[ ] The bridge references the canonical (non-bridge) implementation
[ ] The bridge is removed when the migration is complete
[ ] The bridge does not mask real type errors during static analysis
```

Bridges MUST NOT:

```text
[ ] Be created as permanent infrastructure
[ ] Hide missing implementations
[ ] Be used to skip proper API design
[ ] Be left in production code after the transition period
```

---

## 3. Alias Lifecycle

### Phase 1 — Introduction

When an API moves location, introduce a backward-compatible alias:

```php
// Avax\Components\New\Path\NewClass.php (canonical)
namespace Avax\Components\New\Path;

final class NewClass { }

// Avax\Components\Old\Path\OldClass.php (compatibility bridge — DEPRECATED)
namespace Avax\Components\Old\Path;

/**
 * @deprecated since 2.5, use Avax\Components\New\Path\NewClass instead.
 *              Removal in 3.0.
 * @internal
 */
class OldClass extends Avax\Components\New\Path\NewClass {}
```

### Phase 2 — Deprecation Notice

```php
trigger_deprecation(
    'avax/components',
    '2.5',
    'Avax\Components\Old\Path\OldClass is deprecated. Use Avax\Components\New\Path\NewClass instead.'
);
```

### Phase 3 — Removal

Remove in the next major version. Do not keep aliases indefinitely.

---

## 4. Compatibility Test Contract

Every compatibility bridge must have a test:

```php
/**
 * @test
 * @see Avax\Components\Old\Path\OldClass
 */
public function test_old_alias_resolves_to_new_class(): void
{
    $instance = new OldClass();
    $this->assertInstanceOf(NewClass::class, $instance);
}
```

---

## 5. Version Compatibility Matrix

| Version Range | Compatibility Promise                  |
|---------------|----------------------------------------|
| `1.x` → `2.0` | No guarantee (major version jump)      |
| `2.0` → `2.x` | `@public` APIs stable within minor     |
| `2.x` → `3.0` | Breaking changes only in major version |

---

## 6. Related Documents

- `docs/governance/public-api-policy.md` — API stability levels
- `docs/governance/deprecation-policy.md` — deprecation lifecycle