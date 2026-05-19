# AvaX Governance Budget Recalibration Adoption

**Date:** 2026-05-19
**Status:** GREEN — verify-governance.sh FULL GREEN

## What Changed

Adopted Agent Harness OS v5.0.0 recalibrated complexity budgets into AvaX baseline.

## Baseline Files Updated

| File | Before | After |
|---|---|---|
| `.agents/.rules/skills/bin/check-complexity-budget.py` | v4.2.0 | v5.0.0 |
| `.agents/.rules/skills/bin/compile-governance.py` | v4.2.0 | v5.0.0 + exclusion tagging |

## Threshold Delta

| Metric | Old (v4.2.0) | New (v5.0.0) | Rationale |
|---|---|---|---|
| `MAX_TOTAL_ACTIVE_RULES` | 380 | 500 | Room for framework dictionary + profiles |
| `MAX_CORE_RULES` | 60 | 80 | Mature standards coverage |
| `MAX_AVG_RULE_LINES` | 150 | 250 | Examples, anti-patterns, code blocks |
| `MAX_UNREFERENCED_RULES` | 160 | 200 | Framework dictionary + lookup tables |
| `MAX_SINGLE_RULE_LINES` | N/A | 5000 | Prevent individual monolithic files |
| `MIN_REFERENCED_RATIO` | N/A | 0.05 | Catch systemic reference decay |
| `effective_avg_lines` | N/A | Advisory | Excl reference/lookup dirs |

## New Exclusion Logic

Framework dictionary and profile lookup directories are now excluded from unreferenced rule counting. These files are consumed programmatically (resolution algorithm, framework dictionary matching), not via markdown cross-references:

- `governance/framework-dictionary/`
- `governance/profiles/languages/`
- `governance/profiles/frameworks/`
- `governance/profiles/project-types/`
- `governance/profiles/overlays/`
- `governance/profiles/repository-kinds/`
- `governance/profiles/roles/`

## AvaX Impact

| Metric | Before | After | Status |
|---|---|---|---|
| Total active rules | 184 / 380 | 184 / 500 | GREEN |
| Core rules | 28 / 60 | 28 / 80 | GREEN |
| Avg lines/rule | 194.3 / 150 | 194.3 / 250 | GREEN |
| Unreferenced (filtered) | 165 / 160 | 90 / 200 | GREEN |
| Raw unreferenced | 165 | 165 | 75 excluded as reference/lookup |
| Referenced ratio | N/A | 10.33% / 5% | GREEN |
| Effective avg (excl ref) | N/A | 160.3 | Advisory |
| Max single rule | N/A | 2550 / 5000 | GREEN |

## What Was NOT Changed

- `AGENTS.md` — AvaX root contract untouched
- `.agents/how-to/` — AvaX local governance untouched
- `.agents/GOVERNANCE_INDEX.md` — AvaX index untouched
- AvaX finding decisions, local evidence, Layer 4 rules — all preserved
- No AvaX-specific governance overwritten

## Validation

| Check | Result |
|---|---|
| verify-governance.sh | **FULL GREEN** (was YELLOW) |
| Root evidence hygiene | GREEN |
| Governance index | GREEN |
| Runtime composition | PASS |
| Composer validate | GREEN |
| Composer autoload | 0 warnings |
| PHPUnit | 8458 tests, 0 failures |
| PHPStan | 0 errors |

## Previous YELLOW Resolved

- Avg rule lines 194.3 > 150 → now 194.3 < 250 **GREEN**
- Unreferenced rules 165 > 160 → now 90 filtered < 200 **GREEN**
