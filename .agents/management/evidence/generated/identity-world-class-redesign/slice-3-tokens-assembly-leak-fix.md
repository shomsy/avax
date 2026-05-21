# Slice 3: Tokens Static Factory & Assembly Leak Fix

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Remove assembly logic from `Tokens` PublicSurface and move it to `Configuration/Assembly/TokensGraph`.

Per AvaX architecture law: PublicSurface must not own runtime machinery or object graph assembly.
Configuration/Assembly owns construction.

## Changes

### Created
- `components/Identity/Tokens/System/Configuration/Assembly/TokensGraph.php` — new graph class with `hmac()` and `fromRuntime()` static methods that assemble the Tokens object graph

### Modified
- `components/Identity/Tokens/System/PublicSurface/Tokens.php`
  - Removed imports for concrete InMemory/Hmac classes (no longer needed in PublicSurface)
  - `hmac()` and `fromRuntime()` now delegate to `TokensGraph` instead of doing assembly inline
  - Both methods annotated `@deprecated` with migration message to use `TokensGraph` directly

## Public API Compatibility

- `Tokens::hmac(string $secret): self` — preserved, delegates to TokensGraph
- `Tokens::fromRuntime(...): self` — preserved, delegates to TokensGraph
- Constructor signature unchanged
- No new public methods
- No behavior change

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity"
Tests: 236, Assertions: 790, Failures: 0, Errors: 0
```

## Risk Assessment

- Zero behavior change — backward compatibility fully preserved
- Static factories now delegate one level, adding negligible call overhead (not a hot path)
- The `TokensServiceProvider` already uses constructor injection properly and is unaffected
