# Auth Login Rate Limit Clock Hardening Threat Analysis

Date: 2026-05-21

## Asset

Login brute-force throttling state.

## Risk Before

The in-memory storage wrote failed-attempt timestamps using direct wall-clock `time()`, making lockout behavior harder to prove and harder to isolate under deterministic tests.

## Change

The flow supplies the timestamp from its injected clock when recording failures.

## Fail-Closed Behavior

Rate-limit checks still deny when attempts exceed the threshold and the elapsed time is below decay.

Storage reset behavior is unchanged.

## Residual Yellow

PHPUnit execution remains blocked by Docker socket permissions in this workspace.
