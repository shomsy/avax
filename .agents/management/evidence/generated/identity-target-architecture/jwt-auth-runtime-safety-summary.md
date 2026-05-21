# JwtAuth Runtime Safety Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tokens/System/Capabilities/JwtAuth/JwtAuth.php`
- `components/Identity/Tokens/System/Capabilities/JwtAuth/TokenBlacklist.php`
- `components/Identity/Tokens/System/Capabilities/JwtAuth/Tokens/TokenBlacklist.php`
- `components/Identity/Tokens/System/Configuration/Assembly/JwtAuthGraph.php`
- `tests/Unit/Components/Identity/Tokens/JwtAuthRuntimeSafetyTest.php`

## Implementation

`JwtAuth` is now an instance runtime with explicit `JwtSigner`, `TokenVerifier`, and `TokenBlacklist` dependencies.

`JwtAuthGraph::hmac()` owns default HMAC JWT runtime assembly.

Both token blacklist classes now use instance arrays instead of static revoked-token state.

The new test source proves token issue/verify behavior, revoked-token fail-closed behavior, and revocation isolation between two JwtAuth runtimes.

## Boundary Result

- Static signer/verifier/blacklist state removed from `JwtAuth`.
- Static blacklist revoked-token arrays removed.
- Assembly moved to `Configuration/Assembly`.
