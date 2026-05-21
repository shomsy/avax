# JwtAuth Clock Hardening Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tokens/System/Foundation/Time/Clock.php`
- `components/Identity/Tokens/System/Foundation/Time/SystemClock.php`
- `components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php`
- `components/Identity/Tokens/System/Capabilities/JwtAuth/Verification/TokenVerifier.php`
- `components/Identity/Tokens/System/Configuration/Assembly/JwtAuthGraph.php`
- `tests/Unit/Components/Identity/Tokens/JwtAuthRuntimeSafetyTest.php`

## Implementation

`JwtAuth` now receives a Tokens clock and uses it for issued-token and refresh expiry calculations.

`TokenVerifier` now receives the same clock and uses it as the verification time context. The Firebase JWT static timestamp is set only for the decode call and restored in a `finally` block because the dependency exposes no per-call timestamp parameter.

`JwtAuthGraph::hmac()` owns the default `SystemClock` construction while allowing tests/configuration to pass a deterministic clock.

Focused tests now describe deterministic issue timestamps and fail-closed expiry under an injected clock.

## Boundary Result

- Runtime logic no longer calls PHP wall-clock `time()` directly.
- Wall-clock access is isolated to `Tokens/System/Foundation/Time/SystemClock.php`.
- JwtAuth object graph construction remains in Configuration/Assembly.
