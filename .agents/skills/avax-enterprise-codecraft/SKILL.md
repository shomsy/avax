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
