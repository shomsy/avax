# Architecture Decisions and Fitness Functions Governance

## Status

**MANDATORY** — This document defines non-negotiable rules for recording architecture decisions and executing architecture fitness functions in AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.
- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **BLOCKER**: violation prevents GREEN status.

---

## 1. Purpose

This standard translates the lessons of *Software Architecture: The Hard Parts* (Ford, Richards, Sadalage, Dehghani) into concrete, reviewable rules. It ensures that all architecture choices are driven by explicit trade-offs and protected by automated fitness functions.

---

## 2. Architecture Decision Rule (ADR)

### 2.1 Rule of Explicit Trade-Offs

Every significant architectural change **MUST** be backed by a written Architecture Decision Record (ADR) located in `docs/architecture/decisions/` or `.agents/management/decisions/`.
An architectural change is considered significant if it affects:
- The database access model or persistence boundary.
- The transport or messaging layer (HTTP, Queue, Events).
- Dependency injection, service registration, or compilation boundaries.
- Sibling component dependencies.

### 2.2 Trade-Off Analysis Requirements

An ADR **MUST NOT** be purely optimistic. It **MUST** explicitly weigh the following tradeoffs:
- **Coupling vs. Scalability:** How does increasing/decreasing coupling affect system scaling?
- **Complexity vs. Maintainability:** Does the new abstraction introduce cognitive load that offsets its flexibility?
- **Performance vs. Safety:** Does the runtime boundary introduce latency to protect invariants?

### 2.3 Canonical ADR Template

Every ADR **MUST** use the following markdown template:

```markdown
# ADR-XXXX: <Short Descriptive Title>

**Status:** Proposed | Accepted | Superseded
**Date:** YYYY-MM-DD
**Author:** <Name/Agent>

## 1. Context and Problem Statement
Describe the technical context, the specific problem being solved, and why it matters now.

## 2. Decision
State the exact architectural choice being made. Describe the design, the target boundaries, and how it aligns with AvaX principles.

## 3. Trade-Off Analysis (Mandatory)
Weigh the forces involved. Use the following structured table:

| Force | Selected Option Impact | Rejected Alternatives Impact |
|---|---|---|
| **Coupling** | e.g., Efferent coupling to Component X increases | e.g., Low coupling but duplicates data |
| **Scalability** | e.g., Component X can scale independently | e.g., Monolithic bottleneck |
| **Complexity** | e.g., Exposes a new boundary facade | e.g., Fragmented class layout |

## 4. Rejected Alternatives
List at least two alternative choices and explain exactly why they were rejected.

## 5. Risks and Mitigation
What could fail, leak, or slow down as a result of this decision? How will we mitigate it?

## 6. Fitness Functions (Proof)
Which automated check or test validates that this decision's architectural constraints are not violated over time?

## 7. Revisit Condition
When must this decision be re-evaluated? (e.g., "when database size exceeds 10M rows" or "if transport latency exceeds 50ms").
```

### 2.4 Classifications

- **BLOCKER:** Missing ADR for a significant architectural change (e.g., introducing a new database model, changing HTTP middleware execution logic).
- **HIGH:** ADR exists but fails to document rejected alternatives or the Trade-Off Analysis matrix.
- **MEDIUM:** ADR has no revisit condition or does not specify the associated fitness functions.

---

## 3. Architecture Fitness Function Rule

### 3.1 Definition of a Fitness Function

An **Architecture Fitness Function** is an automated script, test, or static analysis gate that continuously validates that the system's structural constraints (e.g., coupling direction, naming conventions, namespace limits) are preserved.

### 3.2 Mandatory Automated Checks

AvaX enforces the following fitness functions during validation:
1. **Namespace Drift Check:** `php tooling/refactor/check-namespace-drift.php` (must verify no classes live outside their namespace).
2. **Public Surface Boundary Check:** `php tooling/refactor/check-public-surface.php` (must verify internal classes do not leak).
3. **Component Shape Check:** `php tooling/refactor/check-component-canonical-shape.php` (must verify components conform to System/PublicSurface/Flows/Capabilities structure).
4. **Deptrac Coupling Check:** `vendor/bin/deptrac analyse` (must verify no horizontal or circular dependency violations).

### 3.3 Execution and Verification Rules

- All fitness functions **MUST** run during the pre-commit and CI phases.
- Any failure in an architectural fitness function **MUST** fail the build immediately.
- Stale or disabled fitness functions are treated as a **BLOCKER** violation.

### 3.4 Classifications

- **BLOCKER:** Any failure output from a mandatory fitness function, or disabling a fitness function to bypass static analysis.
- **HIGH:** Adding a new component without registering it in the relevant coupling checks (e.g. `deptrac.yaml` or component shape validators).
- **YELLOW:** Fitness functions pass, but lack negative assertions that prove they actually fail when a violation is introduced (see `avax-test-evidence-quality`).
