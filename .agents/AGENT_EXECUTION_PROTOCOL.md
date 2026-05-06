# Agent Execution Protocol

## 1. Read Phase

The agent must read:

1. `AGENTS.md` (root contract)
2. `.agents/GOVERNANCE_INDEX.md` (navigation map)
3. `CURRENT_TRUTH.md` (project state)
4. `Code-Review-And-ToDo/EXECUTION.md` (active task)
5. `.agents/management/ACTIVE.md` (active stage)
6. relevant `.agents/how-to/*.md` (governance rules)
7. relevant `.agents/skills/**` (task playbook)
8. relevant `.agents/management/memories/**` (durable context)
9. relevant `.agents/business-logic/**` (domain meaning)
10. relevant source and tests

## 2. Classify Phase

The agent must classify task type:

```text
architecture          - folder/naming/structure work
component design      - new component or capability
code refactor         - editing existing code
test repair           - fixing tests
documentation         - docs generation
security              - security-sensitive work
performance          - performance-sensitive work
recovery            - restore old behavior from backups
validation          - prove green, run checkers
review               - audit, evaluate code
```

## 3. Scope Phase

The agent must state:

```text
in scope:
out of scope:
stage restrictions:    (V1/V2/V3/V4)
files expected to change:
files forbidden to change:
```

## 4. Plan Phase

The agent must produce a small execution plan.

No broad refactor without explicit approval or active task.

## 5. Execute Phase

The agent edits only the allowed scope.

No skeleton classes.
No fake green.
No silent stage expansion.
No forbidden folder creation.

## 6. Validate Phase

The agent runs focused validation first.

Run full validation when required by scope.

## 7. Report Phase

Every execution ends with:

```text
Stage:
Status:           (GREEN/YELLOW/RED/BLOCKER)
Files changed:
Validation commands:
Validation summary:
Remaining risks:
Next allowed action:
```

## Hard Stop

If the agent cannot identify active stage, forbidden scope, and validation plan, it must not edit code.

---

This protocol is mandatory for all AI agent work in AvaX.