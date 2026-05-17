# Source of Truth Order — V5-01

Date: 2026-05-10
Stage: V5-01 Whole-Repo Governance Resolution

## Purpose

Resolve which documents are source of truth for what, and what happens when they disagree.

## Rule Precedence (When Rules Conflict)

From AGENTS.md §1:

1. `AGENTS.md` — root contract
2. `.agents/GOVERNANCE_INDEX.md` — navigation map
3. `.agents/how-to/**` — local governance rules
4. `EVIDENCE/EXECUTION.md` — active stage and execution order (for state, not rules)
5. `CURRENT_TRUTH.md` — current project status (for state, not rules)
6. `.agents/.rules/AGENTS.md` — reusable root contract
   7-46. Remaining reusable rules, management files, docs (see AGENTS.md §1 for full list)

## Project State Precedence (When Status Disagrees)

From AGENTS.md §3:

1. `CURRENT_TRUTH.md` — wins for current project status
2. `EVIDENCE/EXECUTION.md` — wins for active stage
3. `.agents/management/ACTIVE.md`
4. `.agents/management/TODO.md`
5. Latest validation output

## Known Staleness Issues

| Document          | Stale Area                         | Source of Truth                                     | Resolution                                      |
|-------------------|------------------------------------|-----------------------------------------------------|-------------------------------------------------|
| TODO.md           | V4 stages shown as unchecked `[ ]` | CURRENT_TRUTH.md shows all GREEN                    | TODO.md needs update; V4 is complete            |
| EXECUTION.md §13  | V3 marked LOCKED                   | CURRENT_TRUTH.md says V3 CLOSED/GREEN               | EXECUTION.md is stale; SystemDesignKit promoted |
| EXECUTION.md §165 | Active stage shows "None"          | CURRENT_TRUTH.md says V5 next                       | EXECUTION.md needs update to V5-00              |
| TODO.md §4        | V4 stages unchecked                | CURRENT_TRUTH.md §33 says V4 production-ready GREEN | TODO.md stale                                   |

## File Type Source of Truth

| Question                             | Source of Truth                                                                                               |
|--------------------------------------|---------------------------------------------------------------------------------------------------------------|
| What are the rules?                  | AGENTS.md → GOVERNANCE_INDEX.md → .agents/how-to/**                                                           |
| What is the current project status?  | CURRENT_TRUTH.md                                                                                              |
| What stage is active?                | EVIDENCE/EXECUTION.md, confirmed by CURRENT_TRUTH.md                                                          |
| What work should be done next?       | EVIDENCE/EXECUTION.md → TODO.md                                                                               |
| What does a component look like?     | how-to-design-components.md + AGENTS.md §7                                                                    |
| How should PHP code be written?      | how-to-coding-standards.md + how-to-code-style.md + how-to-clean-code.md + how-to-modern-php-attributes-di.md |
| How should tests be written?         | how-to-unit-test.md                                                                                           |
| How should documentation be written? | how-to-document.md                                                                                            |
| How should security be handled?      | how-to-system-security.md + .agents/.rules/governance/security/**                                             |
| How should performance be handled?   | how-to-system-performance.md                                                                                  |
| How should reviews be done?          | how-to-code-review.md + all applicable how-to docs                                                            |
| What is the V5 plan?                 | EVIDENCE/.PLANS/v5-internal-convergence-modern-php-performance-plan.md                                        |
| What is the V4 plan?                 | EVIDENCE/.PLANS/V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md                                                   |
| What does old code say?              | Evidence only — current governance is the target                                                              |
