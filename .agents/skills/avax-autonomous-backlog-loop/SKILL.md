---
name: avax-autonomous-backlog-loop
description: Autonomous task-by-task backlog execution for maximum remediation sweeps under AvaX governance. Use when user asks to "radi sto vise", "nastavi sam", "bez dodatnih promptova", "zatvori listu", "maximum sweep", "autonomous backlog loop", "task po task", "dok ima tokena".
---

# AvaX Autonomous Backlog Loop

## Purpose

Autonomous task-by-task backlog execution for Codex/Qoder maximum remediation sweeps.

Works under AvaX governance, not instead of it.

PARTIAL is not STOP. Continue until TODO_CLOSED or HARD_BLOCKER.

## Activation Triggers

- "radi sto vise"
- "nastavi sam"
- "bez dodatnih promptova"
- "zatvori listu"
- "maximum sweep"
- "autonomous backlog loop"
- "task po task"
- "dok ima tokena"

## Mandatory Boot Order

1. **Load `avax-enterprise-remediation`** — this is the mandatory bootloader skill
2. **Run full `.agents` context harvest** — discover, classify, and index all `.agents` resources
3. **Load `avax-source-of-truth-resolver`** — resolve current project state and source precedence
4. **Route to task-relevant skills** — discover and apply all matching `.agents/skills/**`
5. **Read TODO.md / fix-this.md / evidence** — load the active backlog and current evidence
6. **Plan execution** — select highest-priority TODO and determine first safe slice

## Full `.agents` Context Harvest

Discover, classify, and index:

```
.agents/skills/**
.agents/how-to/**
.agents/management/**
.agents/management/learning/**
.agents/management/memory/** (if present)
.agents/management/evidence/**
AGENTS.md
.agents/AGENTS.md (if present)
TODO.md
fix-this.md
CURRENT_TRUTH.md
EVIDENCE/EXECUTION.md
```

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

## Autonomous Loop Rule

```
PARTIAL is not STOP.
PARTIAL_WITH_YELLOW is not STOP.
TODO_PARTIAL means continue next smallest safe slice.
Continue until TODO_CLOSED or HARD_BLOCKER.
After TODO_CLOSED, move to next highest-priority TODO.
Do not ask user between slices.
Do not stop after focused validation GREEN if task remains open.
```

## Hard Stop Conditions

Stop only on:

- dirty main that cannot be classified
- unclear ownership boundary
- required public API break without evidence
- new validation failure in changed files
- security behavior cannot be proven fail-closed
- context/source truth contradiction that cannot be reconciled
- missing mandatory governance files
- evidence cannot be written
- branch/worktree contamination
- human architecture decision required

## Branch/Worktree Discipline

```
one active slice = one branch/worktree/evidence/review/merge candidate
no implementation on main
merge sequentially
no self-push
no force push
no staging local files
```

## Per-Slice Flow

1. **Plan** — select TODO, read evidence, determine safe slice
2. **Implement** — obey governance, how-to rules, and skill routing; load `avax-component-dogfooding` when component reuse may be involved; load `avax-runtime-performance-cache` when runtime/cache/performance is relevant; load `avax-security-threat-model` for security-sensitive slices; load `avax-api-compatibility-contract` for public API changes; load `avax-observability-failure-semantics` for failure-prone code
3. **Test** — run focused validation; load `avax-test-evidence-quality` for test-related slices
4. **Evidence** — write proof of what was done; re-run component dogfooding review per slice; preserve performance/cache evidence per slice; write source-of-truth-decision.md
5. **Governance review** — check against applicable how-to rules
6. **Commit** — only if validation GREEN and review clean
7. **Self-review** — verify no unrelated files, no skipped rules
8. **Merge if ready** — sequential merge into target branch
9. **Post-merge validation** — verify clean state after merge
10. **Continue** — select next TODO or slice

## Final Handoff

Always write `QODER_HANDOFF.md` before stopping.

Include:

```
last TODO processed
slice status
validation summary
remaining TODOs
reason for stopping
next allowed action
```

## Required Output Per Execution

Return:

1. bootloader skill loaded
2. context harvest summary
3. skills routed
4. TODOs discovered and prioritized
5. per-slice status
6. validation summary
7. evidence written
8. QODER_HANDOFF.md path
9. final decision:

- AUTONOMOUS_LOOP_COMPLETED
- AUTONOMOUS_LOOP_STOPPED_HARD_BLOCKER
- AUTONOMOUS_LOOP_CONTEXT_EXHAUSTED

10. one-sentence reason

## Final Rule

No bootloader, no code.

No context, no loop.

No evidence, no merge.

No governance, no GREEN.

## Practical Test Pyramid in Autonomous Loops

Autonomous backlog execution must follow the test pyramid for validation.

See:

- `how-to-unit-test.md` — Section 91: Practical Test Pyramid Rule

During autonomous sweeps, agents must:

- run focused tests for changed units first (fast feedback)
- then component tests, then contract tests, then integration
- then broad/canonical suite, then E2E only for critical journeys
- never run E2E before focused tests
- verify test portfolio health (many fast, fewer broad, minimal E2E)
- reject test evidence that only runs one pyramid layer

Autonomous loops that run tests out of pyramid order waste context and produce false confidence.
