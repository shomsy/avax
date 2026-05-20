# Source Of Truth Decision

## Scope

- mode: HARNESS-FULL / autonomous backlog loop
- active TODO: `TODO-006`
- active branch: `architecture/todo-006-framework-entrypoint-object-graph`
- active worktree: `/home/shomsy/projects/avax-todo-006`
- base after fast-forward: `683e6fb250acc18ede8cda109f5b3fcc8a8efda7`

## Sources Classified Current

- current git state
- `AGENTS.md` version 3.0.0
- `.agents/GOVERNANCE_INDEX.md`
- `.agents/skills/**` task-relevant skills
- `.agents/how-to/**` task-relevant rules
- `TODO.md`
- `fix-this.md`
- latest post-round evidence under `.agents/management/evidence/generated/post-round-002-*`
- review reconciliation evidence under `.agents/management/evidence/generated/review-reconciliation/`
- TODO-006 Slice A evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/`

## Sources Classified Stale Or Advisory

- `CURRENT_TRUTH.md`: explicitly stale.
- `EVIDENCE/EXECUTION.md`: older V5.9/AuthBuilder direction; advisory for this task.
- `.agents/management/CURRENT_TRUTH.md`: older truth file.
- `.agents/management/TODO.md`: older management board.
- `.agents/management/ACTIVE.md`: older active-stage file.
- old V5.9/AuthBuilder notes: advisory until TODO-007.
- archives and legacy evidence: advisory only.

## Contradictions And Resolution

| Conflict | Classification | Resolution |
|---|---|---|
| `CURRENT_TRUTH.md` historical GREEN claims vs current RED remediation board | STALE_SOURCE | Use `fix-this.md`, `TODO.md`, latest evidence, and current git state. |
| `EVIDENCE/EXECUTION.md` V5.9/AuthBuilder direction vs TODO-006 next | STAGE_CONTRADICTION | Current AGENTS 3.0 and latest post-round evidence select TODO-006. |
| TODO-006 branch originally behind main by one AGENTS commit | BRANCH_CONTRADICTION | Fast-forwarded branch to `683e6fb250ac` before validation. |
| Previous Slice A evidence referenced old HEAD `f04cb8a1c582` | EVIDENCE_MISMATCH | Superseded by this source-of-truth decision and updated current-truth evidence. |
| Documentation skill referenced by skills index but file absent | HARMLESS_STALE | Documentation authoring uses `how-to-document.md`; missing skill is YELLOW/NO_BLOCKER for Slice A. |

## Final Truth Baseline

- TODO-006 is the only active implementation task.
- TODO-006 Slice A is valid to finalize.
- TODO-006 remains open after Slice A because additional public entrypoint object-graph findings remain.
- TODO-007 must not start before TODO-006 is closed or hard-blocked.
- Main must remain clean and unpushed by this agent.

## Risk Of Proceeding

Risk is LOW for Slice A finalization because ownership is clear and the branch has been fast-forwarded to the current AGENTS contract.

Risk remains YELLOW for the overall TODO because residual TODO-006 findings remain after Slice A.
