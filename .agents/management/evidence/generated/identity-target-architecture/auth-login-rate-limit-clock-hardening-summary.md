# Auth Login Rate Limit Clock Hardening Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/Flows/Login/RateLimit/LoginRateLimitStorageInterface.php`
- `components/Identity/Auth/System/Flows/Login/RateLimit/InMemoryLoginRateLimitStorage.php`
- `components/Identity/Auth/System/Flows/Login/RateLimit/LoginRateLimit.php`
- `tests/Unit/Components/Identity/Auth/LoginRateLimitClockCharacterizationTest.php`

## Implementation

`LoginRateLimit::recordFailed()` now reads the injected Auth clock and passes the timestamp into storage.

`InMemoryLoginRateLimitStorage` no longer calls `time()`; it stores the timestamp supplied by the flow.

Focused test source proves failed-attempt timestamps follow the injected clock and normalized identifier path.

## Boundary Result

Login rate limit runtime time ownership is deterministic and remains inside the flow object that already receives a clock.
