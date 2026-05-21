# External Identity OIDC Clock Hardening Threat Analysis

Date: 2026-05-21

## Asset

OIDC request-object lifetime and ID token validity.

## Risk Before

Request-object and ID token validity depended on direct wall-clock calls inside runtime code, reducing deterministic proof and making worker/request-time behavior harder to isolate.

## Change

OIDC runtime paths now use an injected ExternalIdentity clock. Default assembly provides `SystemClock`.

## Fail-Closed Behavior

- Expired request objects are removed and return `null`.
- Expired ID tokens resolve to `null`.
- Invalid signatures, issuer mismatch, malformed segments, and invalid JSON continue to return `null`.

## Residual Yellow

PHPUnit execution remains blocked by Docker socket permissions in this workspace, so behavior is represented by test source and static checks until runtime validation is available.
