# Management Model

Version: 3.0.0
Status: Normative

## Purpose

Define the standard management structure for any repository using the Agent
Harness operating system.

## Structure

```
.agents/management/
├── CURRENT.md          # Operational truth now
├── ACTIVE.md           # Active work board
├── TODO.md             # Planned work queue
├── BUGS.md             # Defect/regression queue
├── DECISIONS.md        # Architecture/process decisions (ADR-lite)
├── RISKS.md            # Accepted debt and risk register
├── STATUS.md           # GREEN/YELLOW/RED snapshot
└── evidence/           # Machine evidence (see evidence-model.md)
```

## File Responsibilities

### CURRENT.md

Single source of "what is the system right now."

- current version/state
- active profiles
- active risks
- operational notes

If CURRENT.md disagrees with other management files, investigate.

### ACTIVE.md

Visual live board for currently active work.

- mirrors only active items from TODO.md and BUGS.md
- cards reference canonical queue item IDs
- closed items leave the board and move to evidence

### TODO.md

Canonical active implementation queue.

- newest items first
- outcome-oriented entries
- includes acceptance criteria
- timestamped status updates

### BUGS.md

Canonical active defect and regression queue.

- newest items first
- user-visible failure description
- includes severity, risk, and expected fixed behavior

### DECISIONS.md

ADR-lite decision log for non-trivial choices.

- records context, decision, and consequences
- status: proposed | accepted | superseded

### RISKS.md

Accepted debt and risk register.

Every accepted risk requires:
- owner
- target resolution
- risk description
- expiry date
- evidence link
- blocking decision

Risks without expiry dates are governance bugs.

### STATUS.md

GREEN/YELLOW/RED snapshot of the project.

- GREEN: all gates pass, no open HIGH/CRITICAL
- YELLOW: accepted debt with owner/target/expiry
- RED: open CRITICAL, broken validation, or truth disagreement

## Lifecycle

| Transition | From | To |
|:---|:---|:---|
| Work starts | TODO | ACTIVE (in_progress) |
| Work completes | ACTIVE | evidence + DONE |
| Bug fixed | BUGS (open) | BUGS (fixed) → evidence |
| Bug verified | BUGS (fixed) | BUGS (closed) |
| YELLOW debt accepted | finding | RISKS + DECISIONS |
| YELLOW debt resolved | RISKS (open) | RISKS (closed) |
| Release shipped | ACTIVE | evidence/releases/ |

## Rules

1. All management files live in `.agents/management/`.
2. ACTIVE.md and queue files must stay in sync.
3. Closed items leave ACTIVE.md — they do not accumulate.
4. STATUS.md is updated when gates or risk posture changes.
5. RISKS.md is reviewed during every release or major phase.
6. DECISIONS.md captures non-trivial choices — not every edit.
