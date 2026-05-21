# Auth Session Clock Hardening Plan

Date: 2026-05-21

## Scope

- Remove direct `time()` from Auth foundation clock helper.
- Remove direct `time()` from native session cookie invalidation.
- Add focused characterization source proving `Clock::timestamp()` delegates to `now()`.

## Non-Scope

- Do not redesign NativeSessionStore.
- Do not change session cookie policy shape.
- Do not touch PHP session lifecycle beyond deterministic expiry timestamp.

## Design Decision

Cookie invalidation can use a fixed past timestamp. It does not need current wall-clock time.
