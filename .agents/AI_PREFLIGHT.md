# AI Preflight

Before editing code, the agent must answer all sections.

## 1. Mode

- **Standard Mode** (default)
- **Harness-Full Mode** (only when explicitly requested: "uradi po pravilima .agents")

## 2. Active Stage

Read:

- `CURRENT_TRUTH.md`
- `Code-Review-And-ToDo/EXECUTION.md`
- `.agents/management/ACTIVE.md`

Answer:

```text
Active stage: V1 / V2 / V3 / V4
Status: GREEN / YELLOW / RED
Allowed scope:
Forbidden scope:
Next allowed action:
```

## 3. Governance

Read:

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- relevant `.agents/how-to/*.md`
- `.agents/AGENT_EXECUTION_PROTOCOL.md`

List:

```text
Governance docs read:
Rules that apply:
Rules not applicable:
```

## 4. Skill Match

Check `.agents/skills/index.md`.

If the task matches a skill, read the skill.

```text
Matched skill:
Skill file read:
```

## 5. Memory Check

Check `.agents/management/memories/**` when task is architecture, recovery, naming, or long-running work.

```text
Memory files read:
Relevant memory:
```

## 6. Learning Check

Check `.agents/management/learning/**` before repeating similar work.

```text
Learning files read:
Relevant lesson:
```

## 7. Business Logic

Check `.agents/business-logic/**` for terminology and domain meaning.

```text
Business logic read:
```

## 8. Validation Plan

Before editing, list validation commands.

```text
Focused validation:
Full validation:
Reports to update:
```

## Hard Stop

**If the agent cannot identify active stage, forbidden scope, and validation plan, it must not edit code.**

---

Every execution ends with:

```text
Stage:
Status: GREEN / YELLOW / RED / BLOCKER
Files changed:
Validation commands:
Validation summary:
Remaining risks:
Next allowed action:
```