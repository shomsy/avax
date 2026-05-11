# Declarative Failure Boundary

## Problem

AvaX HTTP kernels had no centralized try/catch around pipeline execution. Unhandled exceptions propagated to the SAPI without being converted to error responses. The `HandleIncomingHttp` flow caught all exceptions at line 73 and returned a generic 500 "Internal Server Error", discarding the exception type, message, and stack trace.

Controllers, jobs, and commands duplicated error-handling with local try/catch blocks, making it impossible to guarantee consistent failure behavior across the system.

## Solution

The Declarative Failure Boundary replaces scattered try/catch blocks with a single controlled boundary where:

1. **PHP attributes declare failure behavior** — `#[OnFailure]`, `#[ReportFailure]`, `#[Retry]`, etc.
2. **Compiled metadata caches policies** — Reflection happens once at compile time, never on the hot path
3. **A failure pipeline classifies and routes each failure** — Retry, Fallback, MapToResult, DeadLetter, Rethrow, or ReportOnly

## Why Centralize Try/Catch

Centralization provides:
- **Consistent behavior** — Every failure follows the same classification and routing
- **Observability** — All failures are reported through a single pipeline
- **Configurability** — Per-method policies via attributes, not hardcoded catch blocks
- **Testability** — One boundary to test, not dozens of scattered try/catch blocks
- **Safety** — Cleanup is guaranteed by the finally block

## What Local Try/Catch Is Still Allowed

Not every try/catch should be replaced. The following are legitimate:

| Pattern | Example | Keep? |
|---|---|---|
| Lifecycle boundary | BootApplication, WorkerLoop | YES |
| Domain-specific recovery | Transaction retry on deadlock | YES |
| External adapter wrapping | PDO → QueryException | YES |
| Resource cleanup | try/finally for connection release | YES |
| Continue-on-failure | StateResetRegistry, callback loops | YES |

See `EVIDENCE/failure-boundary/try-catch-inventory.md` for the full scan.

## How OnFailure Works

```php
#[OnFailure(ValidationFailed::class, respondWith: 422, messageKey: 'validation_failed')]
#[OnFailure(AuthenticationFailed::class, respondWith: 401)]
public function store(Request $request): Response
{
    // If ValidationFailed is thrown → 422 with 'validation_failed' key
    // If AuthenticationFailed is thrown → 401
    // Anything else → propagates (not swallowed)
}
```

The `#[OnFailure]` attribute is repeatable. Each declaration maps an exception class to an HTTP status code and optional message key.

## How ReportFailure Works

```php
#[ReportFailure(channel: 'http')]
public function handle(): void
{
    // When a failure occurs, it is reported to the 'http' channel
}
```

For the MVP, ReportFailure writes structured context via `error_log()`. When the Observability component is available, it will use the canonical logging interface.

## How Compiled Metadata Works

1. **Compile time (once per method):**
   - `CompileFailurePolicies` reads all attributes via reflection
   - Builds a `CompiledMethodPolicy` with all rules
   - Computes checksum from source file mtime + attribute hash
   - Stores in `CompiledPolicyCache` (static in-memory array)

2. **Runtime (every request):**
   - `ResolveFailurePolicy` reads from `CompiledPolicyCache` — zero reflection
   - Cache miss triggers compile-on-demand
   - Staleness detected via file mtime comparison

3. **No hot-path reflection:** Reflection is confined to the compile path only.

## How HTTP Integration Works

The `HttpFailureBoundaryMiddleware` wraps the entire downstream pipeline:

```
Request → FailureBoundaryMiddleware → RunProtectedAction → $next → catch → Pipeline → Response
```

The middleware is registered in `AppKernel` via a class_exists guard to avoid hard coupling between components and framework:

```php
// In AppKernel::createDefaultMiddlewareStack():
if (class_exists(BuildFailureBoundary::class)
    && class_exists(HttpFailureBoundaryMiddleware::class)) {
    $builder = new BuildFailureBoundary();
    $middleware[] = new HttpFailureBoundaryMiddleware($builder->build());
}
```

Unmapped exceptions propagate through the middleware and are caught by `HandleIncomingHttp`'s outer catch (the lifecycle safety net), which returns a generic 500.

## How to Test It

```bash
# Unit tests
vendor/bin/phpunit --filter FailureBoundary --no-coverage

# E2E adoption tests
vendor/bin/phpunit tests/E2E/FailureBoundaryAdoptionTest.php --no-coverage

# Gates
php tooling/failure-boundary/check-attributes-compiled.php
php tooling/failure-boundary/check-local-try-catch.php
php tooling/failure-boundary/check-dogfooding.php
php tooling/refactor/check-failure-boundary-adoption.php

# PHPStan
vendor/bin/phpstan analyse framework/System/Capabilities/FailureBoundary --memory-limit=1G
```

## What Is Deferred

| Attribute | Status | Reason |
|-----------|--------|--------|
| Timeout | Compiled but not enforced | Requires fiber-level or pcntl_alarm support |
| RecoverWith | Compiled but not enforced | Requires well-defined recovery handler interface |

| Capability | Status | Reason |
|-----------|--------|--------|
| ReportFailure | MVP (error_log) | Replace when Observability component exists |
| DeadLetter | MVP (JSON log) | Replace when Queue component exists |
| Retry | Standalone | Replace when Resilience component exists |

## What Must Not Be Duplicated

- No second ErrorHandling directory
- No duplicate retry engine in other components
- No duplicate dead-letter queue implementation
- No duplicate error reporting/logger implementation
- No local try/catch that duplicates the boundary's decision routing

Ownership is defined in `EVIDENCE/failure-boundary/ownership-decision.md`.
