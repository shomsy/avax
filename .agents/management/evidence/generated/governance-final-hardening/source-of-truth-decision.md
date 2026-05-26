# Source of Truth Decision

**Date:** 2026-05-26
**Task:** Governance final hardening before Identity rewrite
**Branch:** `architecture/identity-runtime-convergence`
**Worktree:** `/home/shomsy/projects/avax-auth-rewrite-v2`
**Status:** PROCEED_WITH_REPAIR

## Sources Loaded

| Source | Status | Decision |
|---|---|---|
| `AGENTS.md` | Loaded | Root execution contract wins. |
| `.agents/GOVERNANCE_INDEX.md` | Loaded | Canonical governance index path. No root `GOVERNANCE_INDEX.md` will be created because that would duplicate the current index. |
| `ARCHITECTURE.md` | Loaded | Root architecture north star exists and must be included in review pack 01. |
| `.agents/how-to/README.md` | Loaded | Root `.agents/how-to/` may contain only `README.md`, `00-reading-order.md`, and an ignored generated `how-to.txt`. |
| `.agents/how-to/00-reading-order.md` | Loaded | Canonical reading order; planned docs must not be treated as required. |
| `.agents/how-to/**/*.md` | Discovered | 30 markdown files found, including 2 project overlay files. |
| `tooling/governance/*` | Discovered | Canonical truth, leakage, index, self-explaining, stage-lock, and review-pack tooling exist. |
| `tooling/testing/*` | Discovered | Shallow-test checker exists. |
| Existing governance-final-hardening evidence | Loaded | Contains stale GREEN/YELLOW claims and raw template fragments; must be rewritten. |

## Preflight Output

```text
pwd
/home/shomsy/projects/avax-auth-rewrite-v2

git branch --show-current
architecture/identity-runtime-convergence

git status --short
 M .agents/GOVERNANCE_ENFORCEMENT_MAP.md
 M .agents/GOVERNANCE_INDEX.md
 M .agents/how-to/00-reading-order.md
 M .agents/how-to/README.md
 M .agents/how-to/architecture/how-to-architecture-decisions.md
 M .agents/how-to/architecture/how-to-architecture-extension-with-ddd.md
 M .agents/how-to/architecture/how-to-architecture.md
 M .agents/how-to/architecture/how-to-engineering-laws.md
 M .agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md
 M .agents/how-to/architecture/how-to-runtime-composition.md
 M .agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md
 M .agents/how-to/architecture/how-to-use-ai-assisted-execution.md
 M .agents/how-to/components/how-to-design-components.md
 M .agents/how-to/components/how-to-dogfooding.md
 M .agents/how-to/documentation/how-to-document.md
 M .agents/how-to/implementation/how-to-clean-code.md
 M .agents/how-to/implementation/how-to-code-style.md
 M .agents/how-to/implementation/how-to-coding-standards.md
 M .agents/how-to/implementation/how-to-dependency-injection.md
 M .agents/how-to/implementation/how-to-modern-php-attributes-di.md
 M .agents/how-to/modeling/how-to-model-flows.md
 M .agents/how-to/project/how-to-git.md
 M .agents/how-to/project/how-to-write-avax.md
 M .agents/how-to/verification/how-to-code-review.md
 M .agents/how-to/verification/how-to-data-systems.md
 M .agents/how-to/verification/how-to-production-readiness.md
 M .agents/how-to/verification/how-to-system-performance.md
 M .agents/how-to/verification/how-to-system-security.md
 M .agents/how-to/verification/how-to-unit-test.md
 M .agents/management/evidence/TEST_STRATEGY.md
 M .gitignore
 M AGENTS.md
 M components/Identity/Identity.txt
 D how-to-write-avax.md
```

Additional untracked governance, evidence, architecture, and tooling files already existed before this repair pass.

## Baseline Validation

```text
php tooling/governance/check-governance-canonical-truth.php
GREEN: Governance canonical truth is coherent.

php tooling/governance/check-governance-leakage.php
Files checked: 26
Files with leaks: 7
Total findings: 11
Exit code: 1 (0=clean, 1=warnings, 2=critical)

php tooling/governance/check-governance-index-current.php
GREEN: Governance index is current.
```

The PHP commands require access to the local PHP/Docker environment. Initial sandboxed runs failed with:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```

They were rerun with approved escalation.

## Contradictions Resolved

| Contradiction | Resolution |
|---|---|
| User prompt names `GOVERNANCE_INDEX.md` at root, but repo has `.agents/GOVERNANCE_INDEX.md`. | `.agents/GOVERNANCE_INDEX.md` is canonical per AGENTS.md and existing tooling. Do not create a duplicate root file. |
| Existing evidence claims leakage checker is clean while current command exits 1. | Current validation wins; evidence must be rewritten. |
| `.agents/how-to/how-to.txt` is a tracked generated aggregate while README says it must not be staged. | Remove tracked generated aggregate and ignore it so stale generated content cannot enter review packs. |
| Existing evidence contains raw template fragments. | Rewrite evidence after actual generator repair. |

## Final Truth Baseline

Proceed with governance/tooling repair only.

Forbidden scope:

- no Identity implementation
- no production feature work
- no new governance philosophy
- no suppression or weakened validation

Required repair surface:

- `.agents/how-to/**`
- `.agents/GOVERNANCE_INDEX.md`
- `.gitignore`
- `tooling/governance/check-governance-canonical-truth.php`
- `tooling/governance/check-governance-leakage.php`
- `tooling/governance/generate-review-packs.php`
- `.agents/management/evidence/generated/governance-final-hardening/**`

