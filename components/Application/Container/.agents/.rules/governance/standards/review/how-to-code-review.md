# How To Code Review

Version: 2.1.0
Status: Normative

This is architecture-first, decision-oriented review.
It is not a style-only pass.
It is also a self-healing loop: review means find, fix, validate again, and
close only when the scope is genuinely production-ready or explicitly
escalated to redesign.

## Review Outcome Contract

Every substantial review must end with exactly one:

- `Keep and Improve`
- `Redesign`
- `Rewrite Candidate`

If no decision is possible, the review is incomplete.

## Review Flow

Every review should traverse these phases in order:

1. context gate
2. as-built reconstruction
3. foundational stress test
4. impacted-lane checks
5. findings and decision

## Self-Healing Rule

Code review is not complete when a defect is discovered.
It is complete when the defect is either:

1. fixed in the current scope and revalidated, or
2. explicitly deferred into a tracked backlog item with evidence and reason

Review the real change surface, not only `AGENTS.md`.
Expand outward as needed into adjacent code, tests, docs, runtime behavior,
release paths, security boundaries, and operator evidence until the system can
be defended from a production-readiness perspective.

## Phase 0: Context Gate

Collect before findings:

1. system or slice in scope
2. runtime context (`local`, `stage`, `prod`, or equivalent)
3. in-scope and out-of-scope surfaces
4. compatibility constraints
5. security, performance, and reliability expectations

No context gate means invalid review.

## Phase 1: As-Built Reconstruction

Reconstruct the real flow, not the intended one:

`entry -> decision -> state mutation -> external effect`

Mandatory outputs:

- primary architecture axis
- responsibility map
- mutation points
- failure path map

## Phase 2: Foundational Stress Test

Check whether the current shape scales safely:

1. does complexity compound with each feature
2. are boundaries enforceable by structure
3. are invariants explicit and testable
4. are failure modes diagnosable
5. can another engineer recover the same mental model quickly

## Impacted-Lane Checks

Review all impacted lanes, not only the lane where the change started.

### Transport and API

- transport layer stays thin
- validation runs before side effects
- public outputs stay deterministic and documented
- error mapping is stable and machine-readable

### Domain and Business Logic

- core rules are not hidden in adapters
- state transitions are explicit
- invariants are enforced once, not duplicated
- contract drift is visible

### Data and Storage

- schema or contract changes have migration posture
- rollback or recovery path exists for destructive mutation
- queries, writes, and side effects are scoped honestly
- stateful failure paths are observable

### UI and Runtime

- runtime behavior is deterministic under the same inputs
- accessibility and operator clarity are treated as done criteria
- hidden cross-surface coupling is called out
- naming and ownership boundaries are still honest

### Security and Trust

- secrets stay out of code, logs, and evidence
- auth and authorization boundaries remain explicit
- fail-closed posture is preserved
- no placeholder-success path hides a broken trust boundary

### Observability and Operations

- health, logs, metrics, traces, or equivalent signals are sufficient
- correlation identifiers exist where they matter
- alerts or operator signals match the real failure modes
- recovery instructions align with the shipped system

### Release and Rollback

- validation and release evidence match the change scope
- promotion path remains deterministic
- rollback trigger and recovery signal are explicit
- stateful releases include recovery proof when required

## Finding Format

Every finding must include:

- severity
- title
- symptom
- root cause
- impact
- evidence
- risk
- recommendation

## Severity Standard

- `critical`: trust, safety, outage, or irreversible data-loss risk
- `high`: correctness or release-blocking risk
- `medium`: meaningful debt or regression risk, but not merge-blocking by itself
- `low`: improvement, clarity, or localized cleanup

## Stability Declaration

Allowed values:

- `Not Stable`
- `Stable (Pre-Production)`
- `Production Ready`

`Production Ready` is valid only when all are true:

1. no open `high` or `critical` findings remain in scope
2. required validations are green
3. release evidence is recorded
4. rollback path is defined and credible
5. observability or operator signals are sufficient for touched runtime surfaces
6. smoke or critical-path verification exists for shipped behavior
7. stateful recovery evidence exists when destructive data mutation is in scope

## CI-First Cadence

- prefer CI and automation evidence first
- use delta review for scoped changes when the architecture axis is unchanged
- escalate to broader review for architecture, security, release, or trust changes
- use strict review for production-ready claims, major convergence, or explicit first-principles challenge review

Failed-run triage should include:

1. CI URL or equivalent execution reference
2. failed job or step name
3. error signature or failing excerpt

## Closure Loop

For TODO or BUG closure, and for review-driven self-healing:

1. review the touched files, adjacent contracts, and real flow
2. fix findings and rerun the relevant validations
3. repeat until no open `high` or `critical` findings remain for the closure scope
4. map residual `medium` and `low` items into backlog explicitly
5. close the item only when acceptance criteria, evidence, and gate results agree

## Required Output Shape

A review should list:

1. findings first, ordered by severity
2. open questions or assumptions
3. short change summary only after findings

If no findings are discovered, say that explicitly and still mention residual risks or testing gaps.
