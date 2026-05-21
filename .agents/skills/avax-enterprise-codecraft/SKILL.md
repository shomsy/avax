---
name: avax-enterprise-codecraft
description: Full software design quality gate covering HLD/system design, LLD/component design, and Codecraft/OOP with SOLID, cohesion, coupling, trade-off analysis, and NFR evaluation. Use for every production-code change, architecture change, refactor, PublicSurface/DI/runtime/security/performance change, or any task mentioning OOP, SOLID, cohesion, coupling, HLD, LLD, system design, enterprise-grade quality, or 11++.
---

# AvaX Enterprise Codecraft

## Purpose

This is not only an OOP/code-style skill. It is a full software design quality gate.

Every production-code implementation, refactor, architecture cleanup, runtime change, PublicSurface change, DI/container change, security-sensitive change, persistence change, HTTP change, filesystem/cache/queue change, or framework-internal change must pass three design levels before commit:

1. High-Level Design (HLD) / System Design
2. Low-Level Design (LLD) / Component Design
3. Codecraft / OOP implementation review

## Trigger

Use this skill for:

- every production-code change
- every architecture change
- every refactor
- every PublicSurface change
- every DI/container/runtime change
- every security-sensitive change
- every performance-sensitive change
- every task mentioning OOP, SOLID, cohesion, coupling, HLD, LLD, system design, enterprise-grade quality, or 11++

Load together with:

- `avax-enterprise-remediation`
- `avax-autonomous-backlog-loop` for autonomous sweeps
- `review` skill
- `testing` skill
- `validation` skill
- `security` skill when relevant
- `performance` skill when relevant
- `avax-component-dogfooding` for component reuse and platform coherence
- `avax-runtime-performance-cache` for performance-sensitive work

## Runtime Performance and Cache Gate

Production code must consider runtime performance and cache safety.

HLD/LLD must classify hot path impact.

Caching requires `cache-design.md`.

Performance-sensitive work must load `avax-runtime-performance-cache`.

## Component Dogfooding Gate

Production code must pass component dogfooding review.

Before creating new logic, check whether an AvaX component/capability already owns the concern.

Advanced OOP means high cohesion, low coupling, and first-party component reuse through correct boundaries.

See `avax-component-dogfooding` for detailed rules.

## High-Level Design Gate (HLD / System Design)

Before changing production code, the agent must answer:

- What system capability or flow is affected?
- What is the business/system purpose of this change?
- Which bounded area owns the behavior?
- Which upstream and downstream components are involved?
- What are the public entrypoints?
- What internal components participate?
- What data/control flow changes?
- What lifecycle phase is affected: boot, compile, configure, runtime, request, worker, reset, shutdown?
- What runtime modes are affected: PHP-FPM, long-lived worker, async, CLI, tests?
- What non-functional requirements are affected?
  - security
  - performance
  - latency
  - throughput
  - memory
  - reliability
  - availability
  - maintainability
  - extensibility
  - testability
  - observability
  - operability
  - debuggability
  - portability
  - backward compatibility
- What are the failure modes?
- What must fail closed?
- What must be observable/loggable?
- What must remain stable for public API users?
- What is the trade-off being accepted?
- What is intentionally out of scope?

The agent must write or update:

`design-before-code.md`

For architecture-heavy tasks, also write:

`high-level-design.md`

## Low-Level Design Gate (LLD / Component Design)

Before implementation, the agent must answer:

- Which classes/functions/files will change?
- What is the exact responsibility of each changed unit?
- What is the expected call flow before and after the change?
- What dependencies are injected?
- What dependencies are assembled?
- Where does object graph construction happen?
- Where are invariants enforced?
- What errors/exceptions can occur?
- How are errors classified?
- What is the rollback or fallback behavior?
- What tests prove the behavior?
- Which edge cases must be covered?
- Which negative cases must fail?
- What static analysis or governance gate proves the design?

The agent must write or update:

`low-level-design.md`

For small non-architecture tasks, LLD may be embedded in `design-before-code.md`, but it must still be explicit.

## SOLID / Cohesion / Coupling Gate

Every production-code change must pass this checklist:

### Single Responsibility Principle

- Does this unit have one reason to change?
- Is it mixing orchestration, construction, validation, IO, security, and formatting?
- If yes, split by real ownership, not by fake wrapper extraction.

### Open/Closed Principle

- Can new behavior be added through a clear extension boundary?
- Or does every new variant require editing central conditionals?
- Are extension points explicit and bounded?

### Liskov Substitution Principle

- Do implementations preserve the contract?
- Are interface implementations behaviorally compatible?
- Are exceptions/failure modes compatible?

### Interface Segregation Principle

- Are interfaces small and role-specific?
- Is a class forced to depend on methods it does not use?
- Are we creating god interfaces?

### Dependency Inversion Principle

- Does high-level policy depend on abstractions?
- Are low-level details injected or assembled at the correct boundary?
- Is runtime code constructing dependencies that should come from configuration/provider/assembly?

### Builder and Assembly Review

- Does the builder own a real cohesive assembly responsibility?
- Is the builder merely hiding a long constructor?
- Does the builder receive Container as a service locator?
- Does the builder assemble unrelated capabilities into a god object?
- Does the builder have a clear dependency boundary?
- Does the builder use existing AvaX components where appropriate?
- Is the builder naming consistent with the assembly context?
- Does the builder method name reveal intent (not defaulting to build() blindly)?
- Is the builder placed in the correct location (Configuration/Builders vs Capabilities)?
- Does the builder improve testability, not weaken it?
- Does the builder leak into runtime flows or PublicSurface?

If any builder question cannot be answered positively, the builder must not exist.

For complete builder governance, see:

- `how-to-architecture.md` — Section 13.3 Builders Rule
- `how-to-dependency-injection.md` — Section 8 Fluent DSL Design Principles
- `how-to-dependency-injection.md` — Section 6.8 Builder Placement Rule

### High Cohesion

- Do methods and properties belong together?
- Does the class have a clear conceptual center?
- Can the class be explained in one plain sentence?
- Are related decisions kept together?

### Low Coupling

- Does this unit know too much about other layers?
- Does PublicSurface know internal machinery?
- Does runtime know configuration assembly?
- Does a flow know concrete infrastructure details?
- Does a test require too many unrelated collaborators?

### Coupling Direction

Allowed direction:

- PublicSurface delegates inward
- Configuration/Assembly builds object graphs
- Flows execute use cases
- Capabilities own reusable behavior
- Foundation owns low-level primitives
- Tests observe behavior

Forbidden direction:

- Runtime flow assembling missing object graph dependencies
- PublicSurface doing real work
- Domain/capability code reaching into global app/container shortcuts
- Low-level infrastructure dictating high-level policy
- Service locator hidden behind convenience APIs

## Hard Enterprise OOP Boundary Gate

Production code must respect enterprise OOP boundaries.

See:

- `how-to-design-components.md` — Section 31: Hard Enterprise OOP Boundary Rules
- `how-to-architecture.md` — Section 54: Hard Enterprise OOP Boundary Rules

Every production-code change must evaluate:

- **Horizontal Blindness:** Does this change create direct dependency between sibling components? If so, it must flow through a parent orchestrator.
- **Boundary Value Objects:** Are raw primitives (string, int, array) crossing subsystem boundaries? They must be wrapped in value objects.
- **Command/Query Clarity:** Does this method both mutate state and return data? It must be split into separate command and query.
- **HLD/LLD Mirror:** Does the code structure match the architecture map? If architecture says A coordinates B and C, code must not wire B directly to C.
- **Single Preferred Entry:** Does this subsystem expose multiple uncontrolled entry points? There must be one gateway.

Violations are classified as:

- BLOCKER: unmitigated horizontal coupling, raw primitives across security boundaries, mixed command/query in security-critical path
- HIGH: boundary violations with partial mitigation
- ACCEPTED_YELLOW: documented trade-off with owner, risk, mitigation, expiry

## System Design Principles Gate

For every meaningful change, evaluate:

- scalability impact
- throughput impact
- latency impact
- memory impact
- consistency model
- concurrency safety
- worker safety
- state reset behavior
- idempotency
- retry behavior
- failure isolation
- backpressure or overload behavior, if relevant
- observability
- logging safety
- sensitive data handling
- configuration safety
- deployment/migration risk
- backward compatibility
- operability in production
- testability
- maintainability
- future extension path

Classify each as:

- IMPROVED
- UNCHANGED
- ACCEPTED_YELLOW
- DEGRADED_BLOCKER
- NOT_APPLICABLE

Any DEGRADED_BLOCKER blocks commit.

## Design Classification

Before commit, classify the implementation:

- HLD_SOUND
- LLD_SOUND
- REAL_OBJECT_MODEL
- ACCEPTABLE_SIMPLE_PROCEDURAL_BOUNDARY
- TOO_MECHANICAL
- FAKE_OOP
- ARCHITECTURE_THEATER
- NEEDS_REDESIGN

Commit is forbidden if classification is:

- TOO_MECHANICAL
- FAKE_OOP
- ARCHITECTURE_THEATER
- NEEDS_REDESIGN

Unless explicitly documented as accepted YELLOW with owner, risk, mitigation, and expiry.

## Required Evidence for Production Code Changes

Every production-code task must include:

- `design-before-code.md`
- `high-level-design.md` for architecture/system-impact tasks
- `low-level-design.md` for all non-trivial implementation changes
- `ownership-boundary.md`
- `implementation-summary.md`
- `test-proof.md`
- `validation-output.md`
- `governance-review.md`
- `final-decision.md`

Security-sensitive tasks must also include:

- `threat-analysis.md`
- `negative-test-proof.md`

Runtime/worker/DI/PublicSurface tasks must also include:

- `runtime-safety-proof.md`
- `dependency-boundary.md`

## Final Output

Every task using this skill must report:

1. HLD summary
2. LLD summary
3. SOLID assessment
4. cohesion/coupling assessment
5. system design impact
6. ownership decision
7. dependency boundary decision
8. public API compatibility
9. failure-mode handling
10. tests proving behavior
11. codecraft classification
12. accepted YELLOW, if any
13. remaining design risk

## Final Rule

No design, no code.

No evidence, no commit.

No cohesion, no GREEN.

No coupling analysis, no merge.
