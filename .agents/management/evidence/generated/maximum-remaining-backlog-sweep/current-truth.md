# Maximum Remaining Backlog Sweep - Current Truth

## Current Git State

- generated at: `2026-05-20 19:50:35 CEST`
- integration branch: `main`
- current main HEAD: `683e6fb250acc18ede8cda109f5b3fcc8a8efda7`
- current main latest commit: `683e6fb25 agents.md update`
- current TODO-006 branch: `architecture/todo-006-framework-entrypoint-object-graph`
- current TODO-006 worktree: `/home/shomsy/projects/avax-todo-006`
- TODO-006 branch base after fast-forward: `683e6fb250acc18ede8cda109f5b3fcc8a8efda7`
- main dirty status during preflight: CLEAN
- branch dirty status: TODO-006 Slice A files and evidence only
- pushed by this agent: NO

## Source Conflicts Found

- `CURRENT_TRUTH.md` is explicitly stale and contains historical GREEN claims below its stale warning.
- `EVIDENCE/EXECUTION.md` still points to older V5.9/AuthBuilder direction.
- `.agents/management/CURRENT_TRUTH.md`, `.agents/management/TODO.md`, and `.agents/management/ACTIVE.md` are older management files and do not override `fix-this.md` plus latest evidence.
- `TODO.md` still contains some older board rows, but latest post-round evidence and `fix-this.md` mark TODO-001 through TODO-005 plus Phase0 DONE.
- Previous Slice A evidence in this branch referenced old HEAD `f04cb8a1c582`; current branch was fast-forwarded to `683e6fb250ac`.
- The branch contained one unrelated `BootDslEngine::verifyContainer()` TODO-comment hunk; it was removed before validation.

## Authoritative Current Remediation Sources

Use these for current remediation:

1. current git state
2. `AGENTS.md` version 3.0.0
3. `.agents/GOVERNANCE_INDEX.md`
4. active task skills:
   - `avax-enterprise-remediation`
   - `avax-source-of-truth-resolver`
   - `avax-autonomous-backlog-loop`
   - `avax-enterprise-codecraft`
   - `avax-component-dogfooding`
   - `avax-runtime-performance-cache`
   - `avax-api-compatibility-contract`
   - `avax-test-evidence-quality`
   - `avax-observability-failure-semantics`
   - `avax-security-threat-model`
   - `review`
   - `validation`
   - `testing`
   - `refactor`
   - `performance`
   - `security`
5. `TODO.md`
6. `fix-this.md`
7. latest post-round evidence:
   - `.agents/management/evidence/generated/post-round-002-backlog-reconciliation/summary.md`
   - `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`
   - `.agents/management/evidence/generated/post-round-002-ready-merge/main-merge-validation.md`
8. TODO-006 evidence under this directory

## Stale Or Advisory Sources

- `CURRENT_TRUTH.md`: historical after its stale warning.
- `EVIDENCE/EXECUTION.md`: historical/advisory when it conflicts with current remediation evidence.
- `.agents/management/CURRENT_TRUTH.md`: stale older GREEN snapshot.
- `.agents/management/TODO.md`: advisory unless current evidence points to the same task.
- `.agents/management/ACTIVE.md`: advisory unless current evidence points to the same task.
- old V5.9/AuthBuilder evidence: advisory until TODO-007 becomes active.
- archive, legacy evidence, generated aggregate dumps, and old review files: advisory only.

## Completed TODOs That Must Not Be Selected Again

- Phase0 truth reconciliation.
- `TODO-001`
- `TODO-002`
- `TODO-003`
- `TODO-004`
- `TODO-005`
- `TODO-016`
- `TODO-017`
- `TODO-018`
- `TODO-019`
- `TODO-026`
- `TODO-026a`
- `TODO-026b`
- `TODO-031`

## Active Remaining TODOs

P0:

- `TODO-006` - framework public entrypoint object-graph assembly.
- `TODO-007` - AuthBuilder split.

P1:

- `TODO-008` through `TODO-015`.

P2:

- `TODO-020` through `TODO-025`, `TODO-027`, `TODO-028`, `TODO-029`.

P3 / accepted debt:

- `TODO-030`
- `TODO-032`

## Why TODO-006 Is Next

- `fix-this.md` cleanup order marks TODO-001 through TODO-005 DONE and names `TODO-006` as NEXT.
- Latest post-round backlog reconciliation says TODO-006 is the next recommended batch.
- `finding-clusters.md` maps `CLUSTER-007` to TODO-006 with normalized severity BLOCKER.
- TODO-007 is explicitly after TODO-006.

## Final Source-Of-Truth Decision

Proceed with TODO-006 Slice A finalization on `architecture/todo-006-framework-entrypoint-object-graph`.

Do not start TODO-007 until TODO-006 is closed or hard-blocked.

Do not rewrite `TODO.md` or `fix-this.md` for Slice A because this slice is partial and does not close TODO-006.
