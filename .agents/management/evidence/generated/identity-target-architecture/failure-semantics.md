# Identity Target Architecture Failure Semantics

## Failure Points

- Missing old `IdentityConfig`: fixed by provider registration of `IdentityConfiguration`.
- Root default auth session: fails closed as guest/no-session.
- Root static DSL assembly: still creates a default runtime through a builder; failure would occur when a sub-surface constructor fails.
- Token defaults: still use the existing token assembly path and require later security review.

## Exception And Message Safety

No new exception messages include secrets, tokens, passwords, user PII, SQL, or filesystem paths.

## Worker State

The new `GuestSessionIdentity` stores no mutable state. This slice does not remove existing static state from legacy sub-surfaces.

## Classification

FAIL_CLOSED for guest auth defaults.
ACCEPTED_YELLOW for remaining static sub-surface state and token default hardening.
