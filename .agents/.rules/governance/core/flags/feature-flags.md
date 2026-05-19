# Feature Flags — Conditional Governance Activation

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines feature flags that control which governance subsystems
are active. Flags enable gradual adoption — new projects can start with
minimal governance and enable subsystems as they mature.

Inspired by feature flag systems that gate runtime behavior through a
layered override model.

---

## 1) Flag Catalog

| Flag | Default | Scope | Description |
|:---|:---:|:---|:---|
| `strict_review` | ON | project | Enforce multi-pass review from `how-to-strict-review.md` |
| `memory_extraction` | ON | global | Auto-extract memories after tasks (Phase 1) |
| `memory_consolidation` | OFF | global | Auto-consolidate memory summaries (Phase 2) |
| `memory_injection` | ON | global | Inject `memory_summary.md` at session start (Phase 3) |
| `continuous_learning` | ON | project | Capture observations and allow the learning pipeline to run |
| `instincts_enabled` | ON | project | Allow instinct generation and application from learned patterns |
| `approval_required` | ON | project | Require human approval for T2+ operations |
| `auto_approval_rules` | ON | project | Use `approved-commands.rules` for prefix matching |
| `dangerous_op_detection` | ON | global | Always detect dangerous operations (§2 of approval-policy) |
| `auto_backlog_update` | ON | project | Auto-update TODO.md on task completion |
| `hooks_enabled` | ON | project | Enable lifecycle hooks from `hooks-policy.md` |
| `offload_notes` | ON | global | Include offload notes in final responses |
| `evidence_required` | ON | project | Require timestamped evidence for DoD |
| `naming_standard` | ON | project | Enforce naming rules from `naming-standard.md` |
| `profile_resolution` | ON | global | Use `profile-resolution-algorithm.md` for stack resolution |
| `security_gates` | ON | global | Enforce security gates from `security/**` |

---

## 2) Override Precedence

Flags are resolved in the following priority order (highest wins):

1. **Session-level override** — Explicit agent instruction for this session.
   _Example_: "Disable strict_review for this task."
2. **Project-level** — Set in root `AGENTS.md` project definitions.
   _Example_: `strict_review: OFF` for a prototype repo.
3. **Global** — Set in `.agents/AGENTS.md` or this file.
4. **Default** — The value in the table above.

### Override Syntax
In root `AGENTS.md`, flags are overridden under the project definitions:

```markdown
## 5) Feature Flag Overrides

| Flag | Value |
|:---|:---:|
| `strict_review` | OFF |
| `memory_consolidation` | ON |
```

---

## 3) Flag Semantics

### ON
The governance subsystem is active. All rules from the referenced document
are enforced.

### OFF
The governance subsystem is inactive. Its rules are still documented but
not enforced. The agent MAY note when it would have triggered a disabled
gate:

```
ℹ️ Note: strict_review is disabled for this project.
   Would have triggered multi-pass review for this change.
```

---

## 4) Feature-Gate Lifecycle

All non-trivial flags should be managed as feature gates with an explicit
lifecycle instead of permanent ad hoc toggles.

Use these maturity stages:

| Stage | Intended Use | Default Posture | Compatibility Promise |
|:---|:---|:---|:---|
| `Alpha` | internal proving and narrow pilots | OFF | no compatibility guarantee |
| `Beta` | broader validation in real repos | explicit opt-in or carefully chosen default | config shape should stabilize |
| `GA` | standard reusable capability | ON or policy-driven | safe to depend on operationally |
| `Deprecated` | scheduled removal | keep current behavior but warn | replacement and sunset date required |
| `Removed` | no longer available | OFF and ignored | references must be cleaned up |

Shared baseline flags in this file should be treated as `GA` unless explicitly
marked otherwise by a future update.

Project-local flags should declare at least:

- `name`
- `stage`
- `owner`
- `default`
- `introduced`
- `affected surfaces`
- `rollback or kill-switch path`
- `expiry review date` for `Alpha` and `Beta`

---

## 5) Compatibility And Disabled-Path Rules

Enterprise-grade feature gates must fail predictably.

- A disabled gate must remove, ignore, or reject its gated behavior in a stable
  way.
- If a gate controls schema, generated adapters, or API surface, disabled fields
  should be dropped before validation or omitted from generated output.
- A repo must not document an `Alpha` path as the default operational baseline.
- Installers, scaffolds, and adapters must not silently assume a gated feature
  exists when the gate is OFF.
- If a gate affects security, approval, or trust behavior, OFF must never widen
  privilege by accident.

This is the same operational idea used by mature platforms: gate behavior must
be explicit both when enabled and when disabled.

---

## 6) Immutable Flags

Some flags cannot be set to OFF by project-level overrides. They can only
be disabled at session-level by explicit human instruction:

| Flag | Reason |
|:---|:---|
| `dangerous_op_detection` | Core safety — never disable by default |
| `security_gates` | Core safety — never disable by default |
| `evidence_required` | Audit trail integrity |

---

## 7) Flag Dependencies

Some flags have upstream dependencies. If the upstream flag is OFF, the
downstream flag is implicitly OFF regardless of its own setting:

```
hooks_enabled ──► memory_extraction (PostTask hook fires extraction)
hooks_enabled ──► auto_backlog_update (PostTask hook fires update)
hooks_enabled ──► continuous_learning (PostToolUse hook fires observation capture)
continuous_learning ──► instincts_enabled (no learning input → no instinct generation)
memory_extraction ──► memory_consolidation (no extractions → nothing to consolidate)
memory_extraction ──► memory_injection (no extractions → nothing to inject)
approval_required ──► auto_approval_rules (no approval → rules unused)
```

---

## 8) Flag Review And Retirement

Every `Alpha`, `Beta`, or `Deprecated` flag should be reviewed on a defined
cadence.

At review time, choose one:

1. promote to the next stage
2. keep current stage with a new review date and explicit reason
3. deprecate and define a migration path
4. remove the gate and clean dependent docs, scaffolds, adapters, and tests

Flags become governance debt when they no longer represent a real decision.
Dead flags should be removed, not left as ceremonial switches.

---

## 9) Relationship to Other Standards

| Standard | Controlled By Flag |
|:---|:---|
| `hooks-policy.md` | `hooks_enabled` |
| `memory-lifecycle.md` | `memory_extraction`, `memory_consolidation`, `memory_injection` |
| `continuous-learning.md` | `continuous_learning`, `instincts_enabled` |
| `approval-policy.md` | `approval_required`, `auto_approval_rules`, `dangerous_op_detection` |
| `how-to-strict-review.md` | `strict_review` |
| `naming-standard.md` | `naming_standard` |
| `quality-gates.md` | `evidence_required` |
| `security/**` | `security_gates` |
| `profile-resolution-algorithm.md` | `profile_resolution` |
