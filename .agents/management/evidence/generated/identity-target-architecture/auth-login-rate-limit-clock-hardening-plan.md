# Auth Login Rate Limit Clock Hardening Plan

Date: 2026-05-21

## Scope

- Remove direct wall-clock reads from `InMemoryLoginRateLimitStorage`.
- Keep time ownership in `LoginRateLimit`, which already receives Auth `Clock`.
- Add focused characterization source proving failed-attempt timestamps use the injected clock.

## Non-Scope

- Do not redesign AuthBuilder.
- Do not change broader throttle/risk systems.
- Do not remove the Auth foundation clock `timestamp()` helper in this slice.
- Do not change session cookie expiry behavior in this slice.

## Design Decision

The storage interface now accepts the recorded timestamp. Storage records state; `LoginRateLimit` owns time decisions.
