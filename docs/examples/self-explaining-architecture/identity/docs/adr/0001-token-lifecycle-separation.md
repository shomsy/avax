# Token Lifecycle Separation

**Status:** accepted
**Date:** 2026-05-01
**Owner:** Platform Security Team

## Context

Token handling was initially implemented as a single capability that issued, validated, refreshed, and revoked tokens. As the token system grew, the single capability became complex, had multiple change reasons, and was difficult to test. Every token change risked breaking unrelated token operations.

## Decision

Split token lifecycle into three distinct capabilities:

- **IssueToken**: Creates signed tokens with claims and expiry
- **ValidateToken**: Verifies signature, expiry, issuer, and claims
- **RefreshToken**: Issues new tokens from valid refresh tokens

Each capability has its own test suite, its own failure modes, and its own change axis.

## Alternatives Considered

### Alternative 1: Keep single TokenManager

- Pros: Simple, few classes
- Cons: Multiple change reasons, hard to test, any change risks all token operations
- Why rejected: Violates Single Responsibility Principle; risk profile too high for security-critical code

### Alternative 2: Strategy pattern with pluggable token handlers

- Pros: Flexible, extensible
- Cons: Premature abstraction, indirection without proven need
- Why rejected: Overengineering for current requirements

## Consequences

- **Easier**: Testing individual token operations; reasoning about token behavior; changing one operation without affecting others
- **Harder**: Slightly more classes; callers must know which capability they need
- **Migration**: TokenManager calls replaced with specific capability calls
- **Testing**: Each capability has focused tests; negative tests for every failure mode

## Tradeoffs

- **Gained**: Cohesion, testability, change isolation
- **Sacrificed**: Convenience of single-class token API
- **Accepted risk**: Slight increase in component count is justified by security-critical nature

## When to Revisit

If token operations grow to 10+ per capability, consider whether the split needs refinement. If a new token operation would cross capability boundaries, reconsider the decomposition.

## Related

- ADR 0002: Session vs Authentication Separation
- ADR 0003: Stateless Token Design
- Dictionary: Token, Authentication
