# ADR-0003: Session Management

## Status

proposed

## Context

AvaX needs a session management strategy that:
- Stores server-side session state securely
- Supports session creation, renewal, and termination
- Works across different runtimes (PHP-FPM, FrankenPHP, RoadRunner, long-lived workers)
- Does not leak session state between requests in long-lived workers
- Supports concurrent sessions per user
- Allows targeted session invalidation (logout, password change, security event)
- Is observable for security monitoring

The challenge is maintaining session security while supporting multiple runtimes and ensuring no session state leaks between requests in persistent workers.

## Decision

AvaX will use a server-side session capability with the following properties:

1. **Session storage**: Server-side, accessed through a session capability, not raw storage
2. **Session identifier**: Unique, cryptographically random, transmitted securely (cookie)
3. **Session lifecycle**: Explicit creation, renewal, and termination through the session capability
4. **Per-request isolation**: Sessions are loaded per-request and must not persist in static/mutable state between requests in long-lived workers
5. **Concurrent sessions**: Users may have multiple active sessions; each is independently manageable
6. **Invalidation**: Sessions can be individually invalidated (logout) or bulk invalidated (password change, security event)
7. **Binding**: Sessions are bound to authenticated identity and optionally tenant context

Session management fails closed: if a session cannot be loaded, validated, or is expired, the request is treated as unauthenticated.

## Consequences

- Session state is isolated per-request, safe for long-lived workers
- Sessions can be individually targeted for invalidation
- Session events are observable for security monitoring
- Session capability is independently testable
- No static/mutable session state persists between requests

## Trade-offs

Gained:
- Worker-safe session management (no state leaks)
- Targeted session invalidation
- Observable session lifecycle
- Clear session capability boundary

Given up:
- Simplicity of session-as-global-state
- Some performance (session must be loaded per-request)
- Direct access to raw session storage
