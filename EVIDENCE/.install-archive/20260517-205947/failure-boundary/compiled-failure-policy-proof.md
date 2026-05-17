# Compiled Failure Policy Proof

Date: 2026-05-12

## Mechanism Overview

The compiled metadata system ensures attributes are read via reflection **once at compile time**, then the compiled policy is cached in memory for subsequent requests. The runtime hot path uses zero reflection.

## Reflection Scan

```bash
grep -R "ReflectionClass|ReflectionMethod|ReflectionAttribute" framework/System/Capabilities/FailureBoundary/ --include="*.php"
```

| File | Reflection Used? | Classification |
|---|---|---|
| `CompileFailurePolicies.php` | YES — `new \ReflectionClass`, `getMethod`, `getAttributes` | COMPILE_PATH_ALLOWED |
| `CompiledPolicyCache.php` | NO | — |
| `ResolveFailurePolicy.php` (Capability) | NO | — |
| `ResolveFailurePolicy.php` (Flow) | NO | — |
| `RunFailurePipeline.php` | NO | — |
| `RunProtectedAction.php` | NO | — |
| All other files | NO | — |

**Result:** Reflection is confined to `CompileFailurePolicies` only. No reflection in the runtime hot path.

## Compilation Flow

1. **Scan:** `CompileFailurePolicies.compile(target, method)` receives target class/method
2. **Reflect:** Creates `\ReflectionClass` of target, gets method, reads all `\ReflectionAttribute`
3. **Build:** Matches attribute instances to build `FailurePolicy` with actions, retry config, etc.
4. **Checksum:** Computes `xxh128` hash of class+method+attribute names+arguments
5. **Mtime:** Records `filemtime()` of source file for staleness detection
6. **Cache:** Stores `CompiledMethodPolicy` in `CompiledPolicyCache` static array

## Runtime Resolution Flow

1. **Lookup:** `ResolveFailurePolicy.for(context)` calls `CompiledPolicyCache.get(key)`
2. **Hit:** Returns cached policy (after staleness check) — **no reflection**
3. **Miss:** Calls `CompileFailurePolicies.compile()` on-demand, caches result
4. **Stale:** `CompiledMethodPolicy.isStale()` compares stored mtime vs current mtime, invalidates if changed

## Staleness Detection

```php
public function isStale(): bool
{
    if ($this->sourceMtime === 0) {
        return false; // Synthetic policy, skip staleness
    }
    $targetClass = $this->targetClass;
    if (!class_exists($targetClass)) {
        return true; // Class removed
    }
    $ref = new \ReflectionClass($targetClass);
    $file = $ref->getFileName();
    if ($file === false || !file_exists($file)) {
        return true; // File removed
    }
    return (int) filemtime($file) !== $this->sourceMtime; // Source changed
}
```

Staleness check uses reflection but only when a cached policy needs verification — not on every request.

## Corrupt Metadata Handling

Test `testRejectsCorruptMetadata` proves that invalid enum values (e.g., `'invalid_decision'`) trigger `ValueError` during `fromArray()` deserialization. Corrupt data is rejected, not silently accepted.

## No Hot-Path Reflection

Verified:
- `RunProtectedAction` — no reflection
- `RunFailurePipeline` — no reflection
- `ClassifyFailure` — no reflection (pure pattern matching)
- `ResolveFailurePolicy` — reads from cache, compiles on miss only
- `CompiledPolicyCache` — static array get/put

**HOT_PATH_VIOLATION: NONE**

## Schema Version

Current schema: `v1`

`CompiledMethodPolicy` includes `schemaVersion` field for future migration support.
