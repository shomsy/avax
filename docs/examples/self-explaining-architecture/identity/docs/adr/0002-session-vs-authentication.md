# Session vs Authentication Separation

**Status:** accepted
**Date:** 2026-05-01
**Owner:** Platform Security Team

## Context

Session management and authentication were initially combined. Authentication created sessions, sessions contained authentication state, and the boundary between them was unclear. This caused confusion about where session expiry, refresh, and revocation logic belonged.

## Decision

Separate Authentication and Session into distinct capabilities:

- **Authentication**: Verifies credentials, returns authentication result
- **Session**: Manages session lifecycle (create, read, update, delete, expire)

Authentication creates a session as a side effect but delegates session management to the Session capability. Session does not authenticate; it manages state after authentication.

## Alternatives Considered

### Alternative 1: Keep combined

- Pros: Fewer classes, fewer files
- Cons: Blurred responsibility, hard to change one without affecting the other
- Why rejected: Security-sensitive code needs clear ownership

### Alternative 2: Authentication owns sessions entirely

- Pros: Simple caller API
- Cons: Authentication grows to own unrelated session lifecycle logic
- Why rejected: Authentication should focus on verifying identity, not managing state

## Consequences

- **Easier**: Independent evolution of auth and session logic; testing each in isolation
- **Harder**: Callers must interact with two capabilities for full auth+session flow
- **Migration**: Combined auth+session calls refactored into two-step flows

## Tradeoffs

- **Gained**: Clear ownership, independent testability, separate change axes
- **Sacrificed**: One-call convenience for auth+session operations

## When to Revisit

If a new pattern emerges that makes auth+session always-together operations more natural, consider a facade. But the separation must remain underneath.

## Related

- ADR 0001: Token Lifecycle Separation
- Dictionary: Authentication, Session
