# Auth Session Clock Hardening Threat Analysis

Date: 2026-05-21

## Asset

Session invalidation and Auth time source consistency.

## Risk Before

Auth had direct wall-clock reads in the clock helper and session invalidation path.

## Change

The clock helper now delegates through `now()`, allowing tests/subclasses to provide deterministic time. Session cookie invalidation uses a fixed expired timestamp.

## Fail-Closed Behavior

The invalidation cookie remains expired because timestamp `1` is in the past.

Session data clearing and `session_destroy()` behavior are unchanged.

## Residual Yellow

PHPUnit execution remains blocked by Docker socket permissions in this workspace.
