# FailureBoundary — Compiled Metadata Proof & Reflection Scan

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Prove that compiled metadata is real (not cached reflection), that reflection is confined to compile path,
and that runtime uses only compiled DTOs.

## Compilation Path

### CompileFailurePolicies.php

This is the ONLY place where reflection is used in the entire FailureBoundary component.

```php
public function compile(string $targetClass, string $targetMethod): ?CompiledMethodPolicy
{
    $reflection = new ReflectionMethod($targetClass, $targetMethod);
    $attributes = $reflection->getAttributes();
    // ... reads OnFailure, ReportFailure, Retry, Fallback, DeadLetter, Rethrow, Timeout, RecoverWith
}
```

**Classification:** COMPILE_PATH_ALLOWED

- Reflection happens only when compiling policies for a class::method
- Compilation result is stored as JSON in `CompiledPolicyCache`
- Runtime reads from cache, NOT from reflection

### CompiledPolicyCache.php

```php
public static function get(string $class, string $method): ?CompiledMethodPolicy
{
    $key = self::key($class, $method);
    if (isset(self::$cache[$key])) {
        return self::$cache[$key];
    }
    // Check file cache...
}
```

**Classification:** NO REFLECTION — reads from static cache or file

### ReadCompiledFailurePolicies.php

```php
public function read(string $cacheFile): ?CompiledMethodPolicy
{
    $content = file_get_contents($cacheFile);
    $data = json_decode($content, true);
    // Deserializes to CompiledMethodPolicy DTO
}
```

**Classification:** NO REFLECTION — reads JSON, deserializes to DTO

### WriteCompiledFailurePolicies.php

```php
public function write(CompiledMethodPolicy $policy, string $cacheFile): void
{
    $data = $this->serialize($policy);
    file_put_contents($cacheFile, json_encode($data, JSON_THROW_ON_ERROR));
}
```

**Classification:** NO REFLECTION — serializes DTO to JSON

### ResolveFailurePolicy.php

```php
public function for(FailureContext $context): FailurePolicy
{
    $cached = CompiledPolicyCache::get($context->targetClass, $context->targetMethod);
    if ($cached === null) {
        $compiler = new CompileFailurePolicies();
        $cached = $compiler->compileAndCache($context->targetClass, $context->targetMethod);
    }
    return $cached !== null ? $cached->policy : new FailurePolicy();
}
```

**Classification:** INDIRECT — only triggers compile-on-miss, but itself does not use reflection

## Staleness Detection

`CompiledPolicyCache` checks file modification time:

```php
$sourceFile = $this->locateSourceFile($class, $method);
if ($sourceFile !== null) {
    $sourceMtime = filemtime($sourceFile);
    if ($sourceMtime > $cacheMtime) {
        // Cache is stale, recompile
    }
}
```

This ensures that when a source file changes (attributes added/removed/modified),
the cache is automatically invalidated.

## Corrupt Metadata Rejection

Test proof: `tests/Unit/Framework/FailureBoundary/FailureBoundaryTest.php`

- Test proves that invalid enum values in compiled metadata throw `ValueError`
- Corrupt or tampered metadata is rejected at deserialization time
- No silent degradation

## Runtime Data Flow

```
Request → HttpFailureBoundaryMiddleware
  ↓
RunProtectedAction.run(action, context)
  ↓ (exception thrown)
RunFailurePipeline.for(failure, context, action)
  ↓
ResolveFailurePolicy.for(context)
  ↓
CompiledPolicyCache.get(class, method)  ← NO REFLECTION
  ↓ (hit)
Returns CompiledMethodPolicy DTO
  ↓
ClassifyFailure.decide(failure, policy)  ← uses DTO data
  ↓
match (decision) → pipeline action
```

## Reflection Scan Results

| File                               | `ReflectionClass` | `ReflectionMethod` | `ReflectionParameter` | `getAttributes()` | Other Reflection |
|------------------------------------|-------------------|--------------------|-----------------------|-------------------|------------------|
| `CompileFailurePolicies.php`       | Yes               | Yes                | Yes                   | Yes               | No               |
| `CompiledPolicyCache.php`          | No                | No                 | No                    | No                | No               |
| `ReadCompiledFailurePolicies.php`  | No                | No                 | No                    | No                | No               |
| `WriteCompiledFailurePolicies.php` | No                | No                 | No                    | No                | No               |
| `ResolveFailurePolicy.php`         | No                | No                 | No                    | No                | No               |
| `RunFailurePipeline.php`           | No                | No                 | No                    | No                | No               |
| `RunProtectedAction.php`           | No                | No                 | No                    | No                | No               |
| All other FB files                 | No                | No                 | No                    | No                | No               |

**Result:** Reflection confined to exactly one file: `CompileFailurePolicies.php`

## HOT_PATH_VIOLATION Assessment

| Path                               | Uses Reflection?                                    | Classification                              |
|------------------------------------|-----------------------------------------------------|---------------------------------------------|
| `CompileFailurePolicies.compile()` | Yes                                                 | COMPILE_PATH_ALLOWED                        |
| `CompiledPolicyCache.get()`        | No                                                  | NOT_VIOLATION                               |
| `ResolveFailurePolicy.for()`       | No (compile-on-miss triggers reflection indirectly) | NOT_VIOLATION — compile-on-miss is expected |
| `RunFailurePipeline.for()`         | No                                                  | NOT_VIOLATION                               |
| All runtime hot paths              | No                                                  | NOT_VIOLATION                               |

## Conclusion

| Check                               | Status | Evidence                                              |
|-------------------------------------|--------|-------------------------------------------------------|
| Reflection confined to compile path | GREEN  | Only CompileFailurePolicies.php uses reflection       |
| Runtime uses compiled DTOs          | GREEN  | CompiledMethodPolicy, FailurePolicy, FailureDecision  |
| Static cache prevents re-reflection | GREEN  | CompiledPolicyCache.get/put                           |
| Staleness detection via mtime       | GREEN  | sourceMtime > cacheMtime check                        |
| Corrupt metadata rejected           | GREEN  | Test proves ValueError on invalid enum                |
| No HOT_PATH_VIOLATION               | GREEN  | Zero reflection in runtime hot paths                  |
| Compile-on-miss is expected         | GREEN  | First request compiles, subsequent requests use cache |
