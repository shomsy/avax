# Auth Session Clock Hardening Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/Foundation/Clock.php`
- `components/Identity/Auth/System/Capabilities/Identity/Sessions/Runtime/NativeSessionStore.php`
- `tests/Unit/Components/Identity/Auth/AuthClockCharacterizationTest.php`

## Implementation

`Clock::timestamp()` now delegates to `Clock::now()`.

`NativeSessionStore::invalidate()` now expires the native session cookie with a fixed past timestamp instead of reading wall-clock time.

Focused test source proves `timestamp()` follows the overridable `now()` source.

## Boundary Result

Auth no longer contains direct `time()` calls in foundation clock helper or native session invalidation.
