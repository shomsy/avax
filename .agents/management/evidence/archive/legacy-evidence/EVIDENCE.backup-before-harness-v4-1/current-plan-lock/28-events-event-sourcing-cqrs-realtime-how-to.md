# Evidence: Events, Event Sourcing, CQRS & Realtime How-To Governance Document

**Date:** 2026-05-12
**Branch:** main
**Commit before change:** 1fee87631

## Purpose

This evidence file records the creation of the AvaX governance document for events, listeners, event sourcing, CQRS, and
realtime architecture.

## Why This Is Governance-Only

This is a documentation/governance execution. No production code was changed.

- No V5.7 code implemented
- No event runtime code modified
- No onEvent()->do() implemented
- No emit() implemented
- No event sourcing engine implemented
- No CQRS infrastructure implemented
- No WebSocket/SSE/realtime runtime implemented
- No V5.7-01 started
- No V5.8/V5.9/V6 started
- No placeholder source classes created
- No production behavior changed

## Files Created

| File                                                                     | Purpose                                                                                                                                                                                                                                                  |
|--------------------------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | Governance document defining AvaX event model, DSL, listeners, event sourcing, CQRS, realtime, outbox, inbox, sagas, observability, governance event sourcing, testing, tooling, production readiness, anti-patterns, decision matrix, roadmap alignment |

## Files Modified

| File                          | Change                                                          |
|-------------------------------|-----------------------------------------------------------------|
| `.agents/GOVERNANCE_INDEX.md` | Added new how-to to Document Scope table and Task Routing table |

## Sections Included in New Document

1. Purpose
2. Core Vocabulary (33 terms)
3. Command vs Event vs Listener rules
4. AvaX Event/Listener Public Model (no EventInterface/ListenerInterface requirement)
5. AvaX Fluent Events DSL (onEvent()->do(), emit(object))
6. Attributes and Compiled Metadata (#[ListensTo], no hot-path reflection)
7. Event-Driven Architecture Rules (when to use, when not, listener removal rule)
8. Dogfooding Rules (must dogfood before GREEN, container warning)
9. Event Sourcing (not default, when to use, required concepts, future capability)
10. CQRS (pressure-driven, not folder style, AvaX structure rule)
11. Projections and Read Models (idempotent, rebuildable, observable)
12. Outbox/Inbox (afterCommit discipline, retryable, idempotent)
13. Sagas and Process Managers (explicit state, compensation, observable)
14. Realtime and Live Delivery (filtered DTOs, auth, backpressure, delivery semantics)
15. Pub/Sub, Queues, Async Event Handling (execution modes, no inactive mode claims)
16. Event Versioning and Upcasting (schema version, upcaster, stable names)
17. Event Observability (metrics, correlation ID, payload redaction)
18. Failure and Retry Rules (bubble default, failure policies, no silent swallow)
19. Database Lifecycle Events (V5.8 roadmap, afterCommit discipline)
20. Governance Event Sourcing (evidence as event log, CURRENT_TRUTH as projection)
21. Architecture Placement Rules (canonical owner, convergence table)
22. Testing Rules (events, event sourcing, realtime test requirements)
23. Tooling and Gates (planned gates, gate rules)
24. Production Readiness Checklist (events, event sourcing, realtime checklists)
25. Anti-Patterns (14 forbidden patterns with explanations)
26. Decision Matrix (9 need-to-pattern mappings)
27. Roadmap Alignment (V5.7, V5.8, V5.9, V6.x with honest status)
28. Relationship to Other Governance Documents (10 document relationships)
29. Final Law

## Key Rules Established

- Events are plain readonly objects — no EventInterface required
- Listeners are concrete invokable classes — no ListenerInterface required
- emit(object $event) is the primary API, not class-string emission
- onEvent() registers, emit() dispatches — they must not cross
- #[ListensTo] is compiled at boot, not scanned at runtime
- Event sourcing is not the default persistence model
- CQRS is pressure-driven, not a folder style
- Realtime messages must use filtered DTOs, not raw domain events
- AfterCommit is the rule for external side effects
- Evidence is the governance event log
- CURRENT_TRUTH is a projection

## Alignment with V5.7 Design Lock

The document aligns with V5.7 design lock decisions:

- Canonical owner: components/Operations/Events/
- Four event systems identified with convergence decisions
- Event model: plain readonly objects, invokable listeners
- DSL: onEvent()->do(), emit()
- #[ListensTo] attribute: compiled, not reflected
- PSR-14: optional adapter, duck-typed stoppable
- Compiled registry: in-memory for V5.7, disk persistence is ROADMAP

## Validation Commands

```bash
# Governance scope — markdown only, full code validation optional
git status --short
composer validate --no-check-publish
composer dump-autoload -o
```

## Validation Results

| Command                                | Result                            |
|----------------------------------------|-----------------------------------|
| `git status --short`                   | Shows only .agents/ files changed |
| `composer validate --no-check-publish` | Pending                           |
| `composer dump-autoload -o`            | Pending                           |

## Remaining Risks

- Document is large (29 sections). May need review for consistency with existing how-to style.
- Some sections overlap with `how-to-use-advanced-architecture-patterns.md`. Cross-references are explicit but overlap
  should be verified.
- Governance index update adds one row to each of two tables. No breaking changes.

## Next Allowed Action

Review the document for consistency, then commit if validation passes.

V5.7-01 (Canonical Owner Convergence implementation) remains the next implementation stage, not started.
