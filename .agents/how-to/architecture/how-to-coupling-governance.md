# How To Govern Coupling

## Purpose

Coupling is intentional, visible, justified, and testable.

## When Required

Required for changes to PublicSurface, adapters, providers, factories, builders, runtime boundaries, events, queues, cache, HTTP, database, security, Identity/Auth, interfaces, implementations, service providers, or containers.

## Evidence Path

`.agents/management/evidence/generated/<task-name>/coupling-decision.md`

## Coupling Types

- shared lifecycle
- shared knowledge
- flow of knowledge
- runtime dependency
- data dependency
- temporal dependency
- deployment dependency
- ownership dependency

## Rules

- Boundary rule: name the owner and what is outside the boundary.
- Interface rule: interfaces exist to stabilize a dependency direction, not decorate code.
- Shared library rule: shared code must not become hidden ownership.
- Event/message rule: async boundaries need ordering, retry, and idempotency review.
- Builder/factory/provider rule: construction belongs at assembly boundaries.

## Stop Conditions

Stop if a new dependency direction is unclear, circular, runtime-built, or reaches into private internals.

## Severity

Hidden service locator in production behavior is BLOCKER.
Unjustified boundary coupling is HIGH.

## Final Report Requirement

Final reports must state whether coupling evidence was required and where it is stored.

## Coupling Decision Matrix

| Coupling Type | Accept When | Reject When | Required Proof |
|---|---|---|---|
| Static dependency | Direction follows ownership and public contract. | It reaches into private internals. | Dependency graph or review. |
| Runtime dependency | Lifecycle is explicit and assembled at boundary. | Runtime code builds missing objects. | Configuration or provider proof. |
| Data dependency | System of record and derived state are named. | Ownership is ambiguous. | Data correctness evidence. |
| Temporal dependency | Ordering is part of the business rule. | Hidden ordering creates race risk. | Test or failure-mode proof. |
| Deployment dependency | Release coupling is intentional. | Independent releases are claimed but impossible. | ADR or rollout plan. |
| Ownership dependency | Team/component owner is clear. | Nobody owns the boundary. | Owner and review date. |

## Acceptable Coupling Examples

- PublicSurface depends on a flow interface and delegates.
- Configuration provider assembles concrete dependencies.
- Adapter translates an external model into local language.
- Event message carries immutable facts with idempotent handling.

## Unacceptable Coupling Examples

- Component reaches into another component private folder.
- Flow calls a container to discover a required dependency.
- Cache key shape depends on undocumented internal state.
- Public API returns runtime-specific implementation objects.

## Interface Legitimacy Test

An interface is legitimate when it stabilizes a dependency direction, protects a boundary, supports alternate implementations that matter, or expresses a public contract. It is not legitimate when it only mirrors one class.

## Adapter Legitimacy Test

An adapter is legitimate when it translates external semantics. It is not legitimate when it is a pass-through wrapper around private internals.

## Factory / Builder Legitimacy Test

Factories and builders belong at construction boundaries. They must not hide service locator behavior or runtime graph assembly in business code.

## Event / Message Coupling Trade-Off

Events reduce direct runtime dependency but add ordering, retry, idempotency, observability, and eventual consistency obligations. If those obligations are not documented, the event is not governance-ready.

## Temporal Coupling Examples

- `CreateSession` must happen after credential verification.
- Cache invalidation must happen after committed mutation.
- Queue retry must not execute before idempotency key is recorded.

## Hidden Knowledge Examples

- A caller knows which concrete implementation the container returns.
- A test knows private internal sequencing instead of behavior.
- A component knows another component's database shape.

## Accepted Coupling Debt Format

```text
coupling:
severity:
reason:
owner:
mitigation:
expiry_or_review_date:
fitness_function:
```
