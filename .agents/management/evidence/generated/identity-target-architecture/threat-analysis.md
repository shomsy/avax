# Identity Target Architecture Threat Analysis

## Protected Assets

- Authentication session state
- Authorization decisions
- Token operations
- Tenant/user security context

## Attacker Model

- Unauthenticated caller using the default root DSL
- Authenticated caller trying to inherit stale session/admin state
- Developer accidentally using root DSL defaults as production-grade token configuration

## Threats And Mitigations

- Stale authentication state leak: mitigated for the new root default auth path by `GuestSessionIdentity`, which never resolves a user/session and clears as a no-op.
- PublicSurface hidden assembly: reduced by moving construction from `Identity` into `Configuration/Builders/IdentityRuntime`.
- Direct global hour lookup in policy conditions: reduced by allowing explicit `DateTimeImmutable` context in `AttributeCondition::withinHours()`.
- Token default hardening: NOT CLOSED. Existing HMAC convenience assembly remains and must be handled in a dedicated token/security slice.

## Fail Closed

- Default root `Identity::auth()` remains guest-only unless a real backend is assembled elsewhere.
- `GuestSessionIdentity::resolveUserId()` returns null.
- `GuestSessionIdentity::resolvePhishingResistant()` returns false.

## Classification

PARTIAL_WITH_ACCEPTED_YELLOW. No new fail-open auth path was introduced, but full token/default runtime hardening remains out of scope.
