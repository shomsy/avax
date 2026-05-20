# Implementation Summary

## Problem

`Secrets` is a static facade holding a `SecretStore` instance. In long-lived worker runtimes (ReactPHP, Swoole, RoadRunner), the same worker process handles multiple requests. Secrets stored during Request A would persist for Request B because the static `$secretStore` is never cleared between requests.

## Changes

### 1. Secrets::reset() — NEW method
**File:** `components/Security/Secrets/System/PublicSurface/Secrets.php`

Added `public static function reset(): void` that replaces the static `$secretStore` with a fresh `InMemorySecretStore()`. This clears all stored secrets and prevents cross-request leakage.

### 2. StaticStateReset — Wired Secrets::reset()
**File:** `framework/System/Capabilities/StateReset/StaticStateReset.php`

Added `Secrets::reset()` call in `resetState()` — step 5, between ExternalState and ShutdownSequence. This ensures Secrets are cleared during every StateResetRegistry::resetAll() cycle, which is called by WorkerLoop after every request.

## Files Changed

| File | Change |
|------|--------|
| components/Security/Secrets/System/PublicSurface/Secrets.php | Added reset() method |
| framework/System/Capabilities/StateReset/StaticStateReset.php | Added Secrets::reset() call |
| tests/Unit/Components/Security/Secrets/SecretsSecurityTest.php | NEW — 5 tests |

## What Did NOT Change

- InMemorySecretStore (no changes needed — instance state, held statically by Secrets)
- WorkerLoop (no changes needed — already calls StateResetRegistry::resetAll())
- StateResetRegistry (no changes needed — StaticStateReset already registered)
- ExternalState (already has reset() and wired)
- GlobalEventListenerState (has reset() but not wired — separate priority)
