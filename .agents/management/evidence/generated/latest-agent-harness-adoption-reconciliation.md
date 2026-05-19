# Latest Agent Harness Adoption Reconciliation

**Date:** 2026-05-19
**Installer:** Agent Harness OS v6.0.0 (ADOPT mode)
**Status:** GREEN — all conflicts resolved, no data loss

## Summary

Agent Harness v6.0.0 installer ran in ADOPT mode, detecting 17 `both_diverged` conflicts between the reusable baseline and AvaX's local copy. The installer correctly kept all LOCAL versions. No AvaX Layer 4 rules were overwritten. No reusable rules were corrupted.

## Conflict Classification

All 17 conflicts classified as **KEEP_LOCAL** — the installer's ADOPT mode correctly preserved local versions:

| # | File | Drift Type | Decision | Reason |
|---|---|---|---|---|
| 1 | `profiles/frameworks/v-web-components.md` | both_diverged | KEEP_LOCAL | Generic framework profile, 0 AvaX refs |
| 2 | `profiles/languages/php.md` | both_diverged | KEEP_LOCAL | Language profile, 3 valid override refs |
| 3 | `profiles/languages/typescript.md` | both_diverged | KEEP_LOCAL | Language profile, 1 override ref |
| 4 | `profiles/languages/javascript.md` | both_diverged | KEEP_LOCAL | Language profile, clean |
| 5 | `standards/documentation/evidence-model.md` | both_diverged | KEEP_LOCAL | Universal evidence model, proper frontmatter |
| 6 | `intelligence/learning/instincts-policy.md` | both_diverged | KEEP_LOCAL | Learning policy, proper frontmatter |
| 7 | `core/quality/quality-gates.md` | both_diverged | KEEP_LOCAL | Core quality gates, proper frontmatter |
| 8 | `core/architecture-law.md` | both_diverged | KEEP_LOCAL | Core architecture law (2104 lines), proper frontmatter |
| 9 | `core/flags/feature-flags.md` | both_diverged | KEEP_LOCAL | Feature flags, 6 override refs, proper frontmatter |
| 10 | `core/README.md` | both_diverged | KEEP_LOCAL | Core index, proper frontmatter |
| 11 | `core/bootstrap/canonical-bootstrap-lifecycle.md` | both_diverged | KEEP_LOCAL | Bootstrap lifecycle, proper frontmatter |
| 12 | `core/bootstrap/agent-bootstrap.md` | both_diverged | KEEP_LOCAL | Agent bootstrap, proper frontmatter |
| 13 | `core/resolution/profile-resolution-algorithm.md` | both_diverged | KEEP_LOCAL | Resolution algorithm (456 lines), proper frontmatter |
| 14 | `execution/approvals/approval-policy.md` | both_diverged | KEEP_LOCAL | Approval policy, proper frontmatter |
| 15 | `execution/sandbox/sandbox-boundary-policy.md` | both_diverged | KEEP_LOCAL | Sandbox policy, proper frontmatter |
| 16 | `management/TODO.md` | both_diverged | KEEP_LOCAL | Management TODO, clean |
| 17 | `templates/tasks/execute-change-task.md` | both_diverged | KEEP_LOCAL | Task template, proper frontmatter |

## Root EVIDENCE Dashboard

Installer archived 4 root EVIDENCE files as safety backups:
- `EVIDENCE/EXECUTION.md` (834 lines) — AvaX canonical execution control (AGENTS.md Section 3/4)
- `EVIDENCE/accepted-exceptions-ledger.md` (36 lines) — governance exceptions (AGENTS.md Section 16)
- `EVIDENCE/cleanup_execution_report.md` (45 lines) — governance hygiene
- `EVIDENCE/route-cache-plan.md` (48 lines) — V4-04 DX planning guard

All 4 files remain intact in root EVIDENCE/. The archived copies are identical — no data loss. The evidence-model's 5-file canonical limit is a reusable baseline; AvaX Layer 4 governance explicitly extends it. This is a deliberate, documented override.

## Installer Artifacts Cleaned

- `EVIDENCE/.install-archive/` — safety backups (verified identical, removed)
- `.agents/management/evidence/install-journal/` — install log (removed)
- `.agents/skills/bin/` — installer Python tools (removed)

## Installer-Generated Files (Not Committed)

- `governance-event-stream.json` (44MB) — muscle recovery data dump, too large for repo
- `governance-provenance-graph.json` (32B) — empty
- `strategic-context-summary.json` (79B) — empty

These remain as untracked files. Not committed due to size/emptiness.

## AvaX Layer 4 Integrity

| Check | Result |
|---|---|
| AvaX rules in Layer 4 | PRESERVED — no leakage into baseline |
| Reusable rules generic | CONFIRMED — 0 AvaX refs in all 17 conflict files |
| Frontmatter additions | PRESERVED — all files have proper frontmatter |
| Budget thresholds | UNCHANGED — pre-existing complexity budget issues persist |
| Root EVIDENCE files | INTACT — all 9 files present and correct |
| AGENTS.md | UNCHANGED — local root contract intact |

## Validation

| Check | Result |
|---|---|
| verify-governance.sh | YELLOW (pre-existing complexity budget) |
| root evidence hygiene | GREEN |
| governance index | GREEN (current) |
| runtime composition | PASS |
| composer validate | GREEN |
| composer autoload | 0 ambiguous warnings |

## Pre-existing YELLOW (Not Caused by This Adoption)

- Average rule lines: 194.3 vs 150 threshold
- Unreferenced rules: 165 vs 160 limit

These are governance complexity budget issues that require separate budget recalibration.
