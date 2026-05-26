# Pipeline Ordering Policy

**Status:** accepted
**Date:** 2026-05-01
**Owner:** Platform Core Team

## Context

Middleware execution order was inconsistent. Different components registered middleware in different order expectations. Authentication middleware sometimes ran after logging, leaking authenticated info to logs. Rate limiting sometimes ran after expensive operations.

## Decision

Establish a strict middleware ordering policy based on responsibility:

1. **Error handling** (outermost — catches all exceptions)
2. **Request normalization** (parse body, normalize headers)
3. **Rate limiting** (reject early before expensive operations)
4. **Authentication** (verify identity before logging or processing)
5. **Authorization** (verify permissions after identity is known)
6. **Logging** (log after auth for enriched context)
7. **CORS / Security headers** (apply to response on the way out)
8. **Router / Handler dispatch** (innermost)

## Alternatives Considered

### Alternative 1: Configurable ordering with no constraints

- Pros: Maximum flexibility
- Cons: Inconsistent ordering, hard to reason about, leads to middleware ordering bugs
- Why rejected: Runtime correctness depends on predictable ordering

### Alternative 2: Auto-ordering by dependency analysis

- Pros: No configuration needed
- Cons: Complex, implicit, hard to debug when it makes the wrong decision
- Why rejected: Explicit ordering is clearer

## Consequences

- **Easier:** Predicting middleware behavior, debugging pipeline issues, reasoning about request flow
- **Harder:** Components must register middleware at the correct position (but framework enforces constraints)
- **Migration:** Existing middleware must be re-registered at correct position

## Tradeoffs

- **Gained:** Predictable execution order, security guarantees (auth before processing)
- **Sacrificed:** Flexibility of arbitrary middleware ordering

## When to Revisit

If a new middleware type does not fit any existing category, extend the policy with a new category rather than inserting at an arbitrary position.

## Related

- Dictionary: Middleware Pipeline
- Diagrams: Request Pipeline
