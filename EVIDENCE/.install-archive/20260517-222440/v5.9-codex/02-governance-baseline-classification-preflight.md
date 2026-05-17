# V5.9 Governance Baseline Classification Preflight

Date: 2026-05-16
Mode: Harness-Full (`uradi po pravilima .agents`)
Stage: V5.9 Governance Baseline Classification, Gate Ratchet Correction & Next Blocker Selection
Branch: `main`
Current commit: `4698be5f1383974bc82531c8a3af3cf8a68df41b`

## 1. Worktree Status

Commands run before this evidence file was created:

| Command | Result |
|---|---|
| `git status --short` | Dirty worktree. Intentional prior V5.9 Codex evidence/truth changes are present. User-local/unrelated changes are also present. |
| `git diff --stat` | 11 tracked files changed plus untracked `EVIDENCE/v5.9-codex/`. |
| `git diff --name-only` | `.agents/how-to/how-to.txt`, `.agents/management/ACTIVE.md`, `.agents/management/BUGS.md`, `.agents/management/TODO.md`, `.codex`, `CURRENT_TRUTH.md`, `EVIDENCE/EXECUTION.md`, `avax.part-1-of-4.txt`, `avax.part-3-of-4.txt`, `avax.part-4-of-4.txt`, `avax.txt`. |
| `git diff --cached --stat` | empty |
| `git diff --cached --name-only` | empty |
| `git branch --show-current` | `main` |
| `git log -20 --oneline` | latest commit `4698be5f1 fix-this fixes`; latest Boot DSL correction commit `32864e068 V5.9 Boot DSL correction: fix 6 critical findings from independent review`. |

## 2. Existing Dirty Files

| File/path | Initial classification |
|---|---|
| `CURRENT_TRUTH.md` | Intentional prior V5.9 Codex truth update; may be updated again in this phase. |
| `EVIDENCE/EXECUTION.md` | Intentional prior V5.9 Codex execution update; may be updated again in this phase. |
| `.agents/management/TODO.md` | Intentional prior V5.9 Codex backlog update; may be updated again in this phase. |
| `.agents/management/ACTIVE.md` | Intentional prior V5.9 Codex active-board update; may be updated again in this phase. |
| `.agents/management/BUGS.md` | Intentional prior V5.9 Codex bug queue update; may be updated again in this phase. |
| `EVIDENCE/v5.9-codex/**` | Intentional prior V5.9 Codex evidence; this phase adds evidence in the same directory. |
| `.agents/how-to/how-to.txt` | Pre-existing unrelated governance draft/local change; do not stage unless explicitly proven in scope. |
| `.codex` | Pre-existing user-local file; do not stage. |
| `avax.txt` | Pre-existing user-local deletion according to previous baseline; do not stage. |
| `avax.part-1-of-4.txt` | Newly observed pre-existing local/generated change; do not stage. |
| `avax.part-3-of-4.txt` | Newly observed pre-existing local/generated change; do not stage. |
| `avax.part-4-of-4.txt` | Newly observed pre-existing local/generated change; do not stage. |

## 3. Required Reads Completed

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.agents/management/TODO.md`
- `.agents/management/ACTIVE.md`
- `.agents/management/BUGS.md`
- `fix-this.md`
- all `.agents/how-to/how-to-*.md` inventory plus relevant rule scan
- `.agents/skills/review/SKILL.md`
- `.agents/skills/validation/SKILL.md`
- `.agents/skills/testing/SKILL.md`
- `.agents/skills/refactor/SKILL.md`
- `.agents/skills/security/SKILL.md`
- `.agents/skills/performance/SKILL.md`
- `EVIDENCE/v5.9-codex/00-preflight.md`
- `EVIDENCE/v5.9-codex/01-baseline-validation.md`
- `EVIDENCE/v5.9-codex/90-final-validation.md`
- `EVIDENCE/v5.9-codex/91-final-recursive-governance-review.md`
- `EVIDENCE/v5.9-codex/92-truth-reconciliation.md`
- `EVIDENCE/v5.9-codex/raw/*`
- `tooling/governance/check-semantic-phpdoc.php`
- `tooling/governance/check-how-to-document-structure.php`
- `tooling/governance/check-large-unit-thresholds.php`
- `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`

## 4. Baseline RED Causes

| Cause | Previous result | Initial classification |
|---|---:|---|
| Semantic PHPDoc gate | 17,261 HIGH findings | Gate behavior is too broad for current policy if it blocks untouched legacy debt. Must be corrected to touched/new scope plus visible legacy ratchet reporting. |
| How-to document structure gate | 8 findings | Real small governance-doc issues until proven otherwise. Fix if real; otherwise narrow false-positive handling with fixture proof. |
| Large-unit threshold gate | 1 BLOCKER + 366 REVIEW | `AuthBuilder.php` builder-size BLOCKER is likely real. REVIEW findings are ratchet/review debt unless runtime/security critical. |

## 5. Real Blockers, Legacy Debt, And Fix-Now Candidates

| Finding | Classification | Blocks this phase? | Decision |
|---|---|---:|---|
| Touched/new production PHPDoc violations | BLOCKER/HIGH | YES | Gate must fail for these. |
| Untouched legacy PHPDoc violations | YELLOW_WITH_RATCHET | NO, if visible and ratcheted | Do not mass-fix; report count and require no regression. |
| Semantic PHPDoc scan includes `.idea/vendor` or other non-production local paths | Gate scope bug | YES, if still present | Exclude non-production local/tool/vendor/generated paths narrowly. |
| How-to broken markdown fences | HIGH | YES | Fix if confirmed in current documents. |
| How-to duplicate heading numbers | MEDIUM | YES unless accepted | Fix small numbering drift where safe. |
| `AuthBuilder.php` over builder threshold | BLOCKER | YES for pure GREEN; acceptable as exact next blocker only after classification | Do not split in this phase; write first-slice plan. |
| 366 large-unit REVIEW findings | REVIEW debt | NO, unless any is promoted by evidence | Track as review ratchet debt. |

## 6. Final Status Rules

| Status | Requirement |
|---|---|
| `FULL_GREEN_GOVERNANCE_BASELINE_CLASSIFIED` | Semantic PHPDoc gate enforces touched/new scope and reports legacy ratchet honestly; how-to structure is PASS or accepted exact YELLOW; large-unit gate is classified; AuthBuilder is fixed or accepted as exact next blocker; validation and review clean; commit created. |
| `GREEN_WITH_ACCEPTED_YELLOW_DEBT` | No unresolved BLOCKER/HIGH/MEDIUM except formally accepted next-phase blocker; all YELLOW has owner, target, risk, expiry, evidence, and decision. |
| `YELLOW_WITH_EXACT_AUTHBUILDER_BLOCKER` | Only real blocker left is AuthBuilder split; everything else is green or accepted YELLOW; next allowed action is AuthBuilder split first slice. |
| `RED_GOVERNANCE_BASELINE_STILL_BROKEN` | Semantic PHPDoc still blocks legacy without ratchet, how-to gate unresolved, large-unit gate unclassified, validation fails, truth contradicts validation, or recursive review is missing. |

## 7. Forbidden Scope

- Do not start Boot DSL Phase 2.
- Do not start broad AuthBuilder split.
- Do not mass-fix 17,261 PHPDoc findings.
- Do not weaken gates by hiding findings or deleting checks.
- Do not stage `.codex`, `avax.txt`, `avax.part-*`, or unrelated `.agents/how-to/how-to.txt`.
- Do not claim FULL_GREEN while AuthBuilder remains an unclosed real blocker.

## 8. Next Action

Create worktree safety classification, then correct the semantic PHPDoc gate behavior with ratchet/touched-scope proof,
fix or classify the how-to structure findings, classify the large-unit gate, write the AuthBuilder split plan, reconcile truth,
run validation/gates, perform recursive review, and commit only if the phase gate is clean or accepted YELLOW is formal.
