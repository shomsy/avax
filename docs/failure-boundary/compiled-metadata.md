# Compiled Metadata

## Why Compile?

Reading PHP attributes through reflection on every request is expensive. The Failure Boundary compiles attributes into metadata **once** at startup or on first access, then reads the compiled result without reflection.

## Compilation Flow

```
Method with #[OnFailure(...)]
  → CompileFailurePolicies.compile(class, method)
  → Reflection reads attributes (once)
  → Builds FailurePolicy with actions, retry, fallback, etc.
  → Computes checksum from source file mtime + attribute hash
  → CompiledMethodPolicy stored in CompiledPolicyCache
  → Subsequent reads: cache hit → no reflection
```

## Static Cache

The `CompiledPolicyCache` uses a static in-memory array (same pattern as `DataShape`):

```php
CompiledPolicyCache::put('UserController::store', $compiled);
$cached = CompiledPolicyCache::get('UserController::store');
```

**Staleness detection:** On cache read, the policy checks if the source file has been modified (`filemtime`). If stale, the cache entry is invalidated and recompilation happens on next access.

## File Artifact (Optional)

Compiled policies can also be written to JSON files for persistence across process restarts:

```php
$writer = new WriteCompiledFailurePolicies('/path/to/artifacts');
$writer->write($compiled);

$reader = new ReadCompiledFailurePolicies('/path/to/artifacts');
$read = $reader->read('UserController', 'store');
```

## Schema

Each compiled policy includes:

```json
{
  "schema_version": 1,
  "target_class": "UserController",
  "target_method": "store",
  "checksum": "abc123...",
  "source_mtime": 1715443200,
  "compiled_at": 1715443200,
  "policy": {
    "actions": [
      {
        "exception_class": "ValidationFailed",
        "decision": "map_to_result",
        "status_code": 422
      }
    ],
    "retry_max_attempts": 3,
    "retry_backoff": "exponential",
    "retry_delay_ms": 100,
    "report_channel": "http"
  }
}
```

## Hot Path Guarantee

**No reflection happens in the hot path.** The `RunProtectedAction` flow reads only from the compiled cache. Reflection occurs only during:

1. First access to a method with failure attributes
2. After source file modification (staleness detection)
3. Explicit cache invalidation
