# Hooks Policy — Agent Lifecycle Interception Points

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines the lifecycle hooks that agents MUST respect when
operating under the Agent Harness OS. Hooks provide structured interception
points that enable auditing, gating, and context injection at critical
boundaries in the SDLC workflow.

Inspired by event-driven interception patterns where lifecycle hooks decouple
governance enforcement from primary execution logic.

---

## 1) Hook Event Catalog

Agents MUST recognize and fire the following hook events at the described
boundaries. Not all hooks are blocking — see Disposition Semantics below.

| Hook ID | Fires When | Blocking | Default Disposition |
|:---|:---|:---:|:---|
| `PreTask` | Before any task implementation begins | Yes | Continue |
| `PostTask` | After task implementation completes | No | Log |
| `PreCommit` | Before git commit is created | Yes | Continue |
| `PostCommit` | After git commit succeeds | No | Log |
| `PreReview` | Before code review begins | Yes | Inject-Context |
| `PostReview` | After code review completes | No | Archive |
| `PreRelease` | Before release candidate is cut | Yes | Gate |
| `PostRelease` | After release completes | No | Log |
| `PreValidation` | Before running test/validation suite | No | Continue |
| `PostValidation` | After validation results are known | Yes | Gate |

---

## 2) Disposition Semantics

Each hook returns exactly one disposition that determines how the agent
proceeds:

### Continue
Proceed without modification. The hook has inspected the state and found
no reason to intervene.

### Abort
Block the current operation. The hook has detected a governance violation.
The agent MUST:
1. Log the abort reason with timestamp.
2. Record the finding in `.agents/management/BUGS.md` or relevant backlog.
3. Notify the user with a clear explanation.

### Inject-Context
Proceed, but append governance guidance to the agent's working context.
Typical uses:
- `PreReview` injects the review checklist from `how-to-code-review.md`.
- `PreTask` injects relevant profile pack rules.
- `PreRelease` injects the release policy from `release-and-rollback-policy.md`.

### Gate
Proceed only if all quality gates pass. The agent MUST:
1. Evaluate all applicable gates from `quality-gates.md`.
2. If any gate fails → fall back to Abort disposition.
3. If all gates pass → proceed as Continue.

### Escalate
Require explicit human approval before proceeding. The agent MUST:
1. Present the hook context and rationale to the user.
2. Pause execution until human responds.
3. Record the escalation and decision with timestamp.

### Log
Non-blocking observation. The agent records the event for audit but does
not modify execution flow.

### Archive
Non-blocking. The agent persists the hook output (e.g., review findings)
to the appropriate location in `.agents/review/` or `.agents/management/`.

---

## 3) Hook Execution Protocol

### 3.1 Ordering
When multiple hooks fire at the same boundary (e.g., a plugin adds a custom
`PreTask` hook alongside the governance `PreTask`), they execute in
precedence order:
1. Governance hooks (this file)
2. Profile-specific hooks
3. Project-specific hooks (root `AGENTS.md` overrides)

### 3.2 Short-Circuit
If any blocking hook returns `Abort`, subsequent hooks at the same boundary
are skipped.

### 3.3 Context Accumulation
Multiple `Inject-Context` dispositions at the same boundary are
concatenated. The agent receives all injected context, not just the last.

---

## 4) Standard Hook Implementations

### PreTask
1. Classify the task lane (from `execution-policy.md`).
2. Resolve the governance stack (from `profile-resolution-algorithm.md`).
3. Check feature flags (from `feature-flags.md`).
4. Assign trust tier (from `approval-policy.md`).
5. Write the task routing manifest and selected rule pack (from
   `execution/routing/prompt-to-governance-flow.md`).

### PostTask
1. Extract task summary for memory system (from `memory-lifecycle.md`).
2. Update backlog in `.agents/management/TODO.md`.
3. Record evidence with timestamp.
4. Close the task routing manifest with final status and evidence references.

### PreReview
1. Inject the appropriate review checklist.
2. Inject coding standards from `how-to-coding-standards.md`.
3. Inject naming standards from `naming-standard.md`.

### PreRelease
1. Gate on all quality gates passing.
2. Verify DoD from `quality-gates.md`.
3. Check release policy from `release-and-rollback-policy.md`.

---

## 5) Custom Hooks

Adopting repositories MAY define custom hooks in their root `AGENTS.md` by
extending this catalog. Custom hooks:
- MUST NOT override governance hook dispositions.
- MAY add new hook IDs at any lifecycle boundary.
- MUST document their disposition and blocking behavior.

---

## 6) Audit Trail

All hook firings MUST be logged with:
- Hook ID
- Timestamp (ISO 8601)
- Disposition returned
- Brief rationale (one line)

Evidence format:
```
[2026-04-02T16:30:00+02:00] PreTask → Continue: Task classified as bugfix, profile=typescript
[2026-04-02T16:45:00+02:00] PostTask → Log: Memory extracted, backlog updated
[2026-04-02T16:50:00+02:00] PreReview → Inject-Context: Injected strict review checklist
```

## 7) Baseline Runtime Scripts

The reusable baseline hook implementations live in `.agents/hooks/`:
- `session-start.sh`
- `pre-task.sh`
- `pre-tool-use.sh`
- `post-tool-use.sh`
- `post-task.sh`
- `resolve-task-context.py`

Compatible clients MAY call these scripts directly or adapt them through their
native hook system. Projects MAY extend them locally, but the shared scripts are
the portable baseline for observation and trust enforcement.
