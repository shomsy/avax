# JwtAuth Runtime Safety Plan

Date: 2026-05-21

## Slice Scope

- Remove static signer/verifier/blacklist state from `JwtAuth`.
- Move default JwtAuth assembly to `JwtAuthGraph`.
- Convert token blacklist storage to instance state.
- Add characterization test source proving revocation state does not leak between JwtAuth runtime instances.

## Out of Scope

- JWT clock injection.
- JWT payload schema redesign.
- Duplicate `JwtTokens.php` multi-class cleanup beyond blacklist static state.

## Compatibility

No tracked repo call sites use `JwtAuth::configure()` or other static JwtAuth methods. New assembly path is `JwtAuthGraph::hmac()`.
