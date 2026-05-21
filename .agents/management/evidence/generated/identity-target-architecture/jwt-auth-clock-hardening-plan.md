# JwtAuth Clock Hardening Plan

Date: 2026-05-21

## Scope

- Replace direct `time()` calls in JwtAuth issue/refresh and verification paths with an injected clock.
- Keep the slice inside Tokens ownership.
- Keep JwtAuth assembly in `System/Configuration/Assembly/JwtAuthGraph.php`.
- Add focused characterization tests for deterministic token timestamps and fail-closed expiry.

## Non-Scope

- Do not redesign all token signing.
- Do not change the public `Tokens` facade.
- Do not touch AuthBuilder.
- Do not remove unrelated raw time usage in other Identity components in this slice.

## Design Decision

Tokens gets a tiny `System/Foundation/Time` primitive because existing framework/Auth clocks expose a different shape and would couple this token runtime to Auth internals.

`SystemClock` is the only Tokens file expected to call PHP wall-clock `time()`.
