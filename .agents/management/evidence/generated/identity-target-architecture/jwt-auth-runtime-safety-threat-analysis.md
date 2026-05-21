# JwtAuth Runtime Safety Threat Analysis

Date: 2026-05-21

## Asset

JWT signing configuration, token verification, and revocation state.

## Threats

- Revoked-token state leaking across tests, requests, workers, or tenants through static arrays.
- Static signer/verifier configuration being reused unexpectedly in long-lived workers.
- Revocation checks failing open after runtime reuse.

## Mitigation

- `JwtAuth` receives signer, verifier, and blacklist as explicit runtime dependencies.
- `JwtAuthGraph::hmac()` assembles a fresh runtime with a fresh blacklist.
- `TokenBlacklist` state is instance-owned.
- `verify()` fails closed for revoked tokens with `RuntimeException('Token has been revoked')`.
- New test source proves two runtimes do not share revocation state.

## Remaining Yellow

`JwtAuth` still uses `time()` and `random_bytes()` directly. `random_bytes()` is appropriate for token IDs; deterministic time/Clock injection is deferred to a later token hardening slice.

Runtime execution is ENVIRONMENT_YELLOW until PHP/PHPUnit can run outside the Docker socket permission issue.
