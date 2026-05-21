# JwtAuth Clock Hardening Threat Analysis

Date: 2026-05-21

## Asset

JWT access and refresh token validity windows.

## Risk Before

JwtAuth and TokenVerifier read wall-clock time directly, making expiry behavior harder to prove and harder to isolate in tests or request-runtime contexts.

## Change

Token time decisions now depend on an injected Tokens clock. Default runtime assembly provides `SystemClock`; tests can provide deterministic time.

## Fail-Closed Behavior

- Revoked tokens still throw before payload acceptance.
- Expired tokens still throw during verification.
- Invalid refresh token type still throws.
- Introspection catches verification failures and reports inactive tokens.

## Residual Yellow

Firebase JWT exposes verification time through `JWT::$timestamp`, a vendor static. The slice brackets that static mutation with immediate restoration in `finally`.

This is safer than unbounded direct wall-clock reads, but a deeper future slice may replace the Firebase decode boundary with a non-static verifier adapter if async/concurrent runtime validation requires it.
