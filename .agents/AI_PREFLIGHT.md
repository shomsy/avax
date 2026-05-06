# AI Agent Preflight

Before any code edit, the agent MUST confirm:

- [ ] read GOVERNANCE_INDEX.md
- [ ] read active task / EXECUTION.md
- [ ] read CURRENT_TRUTH.md
- [ ] read relevant how-to docs
- [ ] identified active stage
- [ ] identified forbidden scope
- [ ] listed validation commands
- [ ] knows next allowed action

## Required Statement

Before any implementation work, the agent must state:

```
Stage: V1/V2/V3
Status: GREEN/YELLOW/RED
Forbidden: <what is NOT allowed now>
Next Allowed: <what IS allowed now>
Validation: <commands to run>
```

## Hard Rule

If the agent cannot state the active stage and forbidden scope, it must not edit code.

---

This preflight is mandatory for all AI agent work in AvaX.