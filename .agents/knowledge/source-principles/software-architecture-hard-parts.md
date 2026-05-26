# Source Principles: Software Architecture - The Hard Parts

## Role in Governance

Use this source family when a task changes boundaries, dependency direction, runtime shape, component ownership, deployment assumptions, or an architecture rule.

## Core Principle: Architecture Is Trade-Offs

Architecture work must name the force being optimized and the force being made worse. A design that claims only benefits is not reviewed.

## No Best Practice Without Context

No agent may justify architecture by saying "best practice" alone. The local context must include runtime, data, team ownership, operational risk, and testing constraints.

## Architecture Decision Records

ADRs are required when a decision affects public API, component boundary, runtime behavior, persistence semantics, or long-lived worker safety. ADRs must state alternatives rejected and consequences accepted.

## Fitness Functions

Every durable architecture rule needs an executable check, test, review procedure, or evidence requirement. A rule with no fitness path is YELLOW at best.

## Architectural Quantum

Review the smallest deployable or changeable unit that must move together. If a change crosses that unit, coupling and ownership evidence are required.

## Static Coupling vs Dynamic Coupling

Static coupling is visible in imports, type references, namespaces, and file paths. Dynamic coupling appears through runtime lookup, events, queues, shared state, temporal ordering, or hidden configuration.

## Coupling Dimensions

- static dependency: code reference or import
- dynamic/runtime dependency: lookup, callback, runtime assembly, service locator
- data dependency: shared schema, record, cache key, message shape
- temporal dependency: order-sensitive behavior or lifecycle sequencing
- deployment dependency: units must deploy together
- operational dependency: logging, metrics, retries, runbooks, recovery
- ownership/team dependency: multiple owners must coordinate change
- cognitive dependency: understanding one unit requires private knowledge of another

## Modularity Drivers

- deployability
- scalability
- availability
- security
- testability
- data ownership
- team ownership
- volatility

## Decomposition Decision Matrix

Before splitting or merging boundaries, document driver, benefit, new coupling, removed coupling, runtime cost, data cost, test cost, and rollback path.

## When NOT To Split

Do not split when lifecycle, data invariants, deployment, or ownership are the same and the split only creates calls, adapters, or events with no independent reason.

## Boundary Failure Modes

- public surface owns internal behavior
- service locator fallback hides dependency
- runtime-specific API leaks into core
- boundary has no owner
- architecture decision has no validation path

## Distributed Boundary Risk

Distributed boundaries require latency, retry, partial failure, duplicate delivery, ordering, observability, and reconciliation decisions.

## Architecture Fitness Function Taxonomy

- static: source scan, dependency map, namespace check
- dynamic: runtime doctor, integration test, boot validation
- test: behavioral or contract test
- performance: benchmark or threshold
- security: threat-model check or negative test
- documentation: required ADR/local doc check
- evidence: command output or review proof
- manual-review: checklist with owner and review date

## ADR Acceptance Criteria

An ADR is acceptable only when it has context, decision, alternatives, consequences, fitness path, owner, and review date.

## Agent Review Questions

- What force is optimized?
- What force gets worse?
- What coupling is introduced?
- What coupling is removed?
- What runtime/data/operational risk appears?
- What fitness function prevents drift?
- What is the rollback path?

## Required Evidence

- `architecture-fitness-functions.md`
- `coupling-decision.md`
- ADR when boundary or public API changes
- validation command output

## Checker Mapping

- `tooling/governance/check-architecture-fitness-functions.php`
- `tooling/governance/check-coupling-decisions.php`
- `tooling/sdlc/validate-governance.php`

## Severity Rules

Architecture decision without validation path is HIGH. Runtime-specific leakage into core is HIGH. Hidden service locator fallback in runtime code is BLOCKER.

## Stop Conditions

Stop when owner, trade-off, coupling, fitness path, or rollback path cannot be stated.

