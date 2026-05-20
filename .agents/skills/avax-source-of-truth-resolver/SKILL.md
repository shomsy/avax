---
name: avax-source-of-truth-resolver
description: Prevents agents from using stale TODO/CURRENT_TRUTH/evidence/memory and creating contradictions. Discovers, classifies, and resolves source conflicts with explicit precedence. Use before every task execution, autonomous sweep, architecture decision, or when project state is unclear.
---

# AvaX Source-of-Truth Resolver

## Purpose

Prevent agents from using stale project state sources and creating contradictions.

Every task must resolve a single coherent picture of current project state before code changes.

## Activation Triggers

Activate before:

- every task execution
- autonomous sweep
- architecture decision
- stage transition claim
- validation status claim
- TODO state change
- when project state is unclear
- when evidence contradicts git state
- when CURRENT_TRUTH seems stale
- when learning/memory disagrees with current files

## Source Discovery

Discover and classify:

- current git state (HEAD, working tree, staged)
- AGENTS.md / .agents rules
- active skills
- TODO.md
- fix-this.md
- latest evidence for selected TODO
- CURRENT_TRUTH.md
- EVIDENCE/EXECUTION.md
- how-to rules
- learning / memory files
- older archived evidence
- management files (ACTIVE.md, TIMELINE.md, BUGS.md)

## Conflict Detection

Detect:

- DONE item listed as active
- TODO.md / fix-this.md mismatch
- stale CURRENT_TRUTH
- stale V5/V5.9 direction references
- stale branch/worktree references
- evidence claiming GREEN while validation says YELLOW
- memory contradicting current git
- ACTIVE.md referencing merged/closed items
- EXECUTION.md stage mismatch with git state

## Source Precedence

When sources disagree, apply this precedence:

1. current git state
2. AGENTS.md / .agents rules
3. active skills
4. TODO.md
5. fix-this.md
6. latest evidence for selected TODO
7. CURRENT_TRUTH.md
8. how-to rules
9. learning / memory
10. older archived evidence

## Contradiction Handling

If a contradiction is detected:

1. Classify: STALE_SOURCE, EVIDENCE_MISMATCH, STAGE_CONTRADICTION, BRANCH_CONTRADICTION
2. Determine which source wins by precedence
3. Document the contradiction and resolution
4. If the contradiction cannot be resolved: HARD_BLOCKER

## Stale Source Classification

Classify each stale source:

- HARMLESS_STALE: outdated but not misleading
- MISLEADING_STALE: could cause wrong decision
- ACTIVE_CONTRADICTION: directly conflicts with current state

Any ACTIVE_CONTRADICTION that cannot be reconciled is HARD_BLOCKER.

## Final Source-of-Truth Decision

Before code changes, write:

`source-of-truth-decision.md`

Including:

- sources discovered
- sources classified as current
- sources classified as stale
- contradictions found
- resolution applied
- final truth baseline for this task
- risk of proceeding

## Hard Blocker Conditions

Stop on:

- contradiction that cannot be reconciled by precedence
- mandatory governance files missing
- evidence cannot be written
- git state unclear (detached HEAD, unmerged conflict, corrupted repo)
- CURRENT_TRUTH and git state fundamentally disagree on project phase

## Integration with Other Skills

This skill must be loaded for every task.

It does not replace task-specific skills.
It establishes the truth baseline before any skill can operate correctly.

## Required Evidence

Every task must include:

- `source-of-truth-decision.md`

## Final Rule

No truth resolution, no code.

No contradiction reconciliation, no GREEN.

No evidence, no commit.
