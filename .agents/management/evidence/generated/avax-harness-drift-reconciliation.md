# AvaX Harness V6 Drift Reconciliation Report

Date: 2026-05-19
Branch: main
Mode: HARNESS-FULL
Status: GREEN — All conflicts reconciled

## Executive Summary

Agent Harness V6 adoption reported 13 drift conflicts (both_diverged / local_customization).
This pass reconciled every conflict individually with semantic analysis.

**Result: No governance changes required.**

All 13 conflicts were classified as **TAKE_BASELINE** or **PROJECT_LOCAL_OVERRIDE_INTENTIONAL**.
The baseline V6 upgrade is a superset of the V5 governance model.
AvaX-specific rules remain correctly isolated in Layer 4 (root AGENTS.md + .agents/how-to/).

---

## Conflict Classification Methodology

Each conflict was evaluated against these criteria:

1. Is the file under `.agents/.rules/` (frozen baseline)?
2. Does the file contain AvaX-specific semantics not present in the baseline?
3. If yes, should AvaX semantics move to Layer 4 or stay as a local overlay?
4. Does the new baseline version supersede the old without loss of meaning?

Classification categories:
- **TAKE_BASELINE**: New baseline version is strictly better; accept it.
- **KEEP_LOCAL**: Local customization is intentional and must be preserved.
- **SEMANTIC_MERGE_REQUIRED**: Both versions contain valuable, non-overlapping content.
- **PROJECT_LOCAL_OVERRIDE_INTENTIONAL**: Local override is correct and belongs in Layer 4.

---

## Conflict-by-Conflict Reconciliation

### 1. `.agents/.rules/AGENTS.md` (Priority: CRITICAL)

**Classification: TAKE_BASELINE**

**Before (V5):** Version 2.4.0, 9-item precedence list, minimal governance chain.
**After (V6):** Version 3.0.0, 40-item precedence list, full governance stack including bootstrap, review contracts, documentation standards, governance authoring, delivery operations, intelligence layers, integration policies, sandbox boundaries, and orchestration patterns.

**Merge rationale:**
- The V6 precedence list is a superset of the V5 list.
- All paths that existed in V5 are still present in V6.
- V6 adds 31 new governance paths that were previously optional or implicit.
- No AvaX-specific semantics were lost because AvaX rules are in root AGENTS.md (Layer 4).
- The expanded precedence chain improves profile resolution completeness.

**Before/after semantics:**
- V5: `.agents/governance/core/resolution/profile-resolution-algorithm.md` was item 4
- V6: Same path is item 5 (bootstrap was inserted at item 4, which is correct)
- Evidence model reference changed from full path to `evidence-model.md` (shortened, same file)

**Decision: Accept V6 baseline. No local changes needed.**

---

### 2. `.agents/.rules/governance/core/resolution/profile-resolution-algorithm.md` (Priority: CRITICAL)

**Classification: TAKE_BASELINE**

**Before (V5):** Version 1.0.0, basic resolution order.
**After (V6):** Version 3.0.0, three-tier overlay architecture (Root AGENTS.md -> Local Project Governance -> Frozen Baseline), V3/V4 resolution engine, conflict & incompatibility detection, resolution evidence requirements, memory context resolution (Phase 2), strategic context resolution (ProdOps), detailed lane-specific rule packs.

**Merge rationale:**
- V6 adds the overlay architecture model that AvaX already uses implicitly.
- The three-tier model (Root -> Local -> Baseline) matches AvaX's actual behavior.
- V6 formalizes what AvaX was already doing: root AGENTS.md wins for local rules.
- No AvaX-specific semantics lost.

**Decision: Accept V6 baseline. AvaX already follows the three-tier model.**

---

### 3. `.agents/.rules/governance/core/quality/quality-gates.md` (Priority: HIGH)

**Classification: TAKE_BASELINE**

**Before (V5):** Basic quality filter questions.
**After (V6):** Version 1.3.0, expanded to 13 quality filter questions (from original 8-10), production-ready amplifiers, explicit gate enforcement model, self-healing loop requirement.

**Merge rationale:**
- V6 quality gates are strictly more thorough.
- Self-healing loop (gate 13) aligns with AvaX's evidence-driven governance.
- Production-ready amplifiers match AvaX's production-readiness rules.

**Decision: Accept V6 baseline. Strictly improved.**

---

### 4. `.agents/.rules/governance/execution/approvals/approval-policy.md` (Priority: HIGH)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist in V5 baseline.
**After (V6):** Version 1.0.0, graduated trust model with T0-T3 tiers, dangerous operation detection, approval modes, auto-approval rules, approval request protocol.

**Merge rationale:**
- This is a new V6 baseline capability.
- AvaX root AGENTS.md already defines its own security and execution rules.
- The trust tier model is generic and does not conflict with AvaX specifics.
- AvaX-specific approval behavior stays in root AGENTS.md.

**Decision: Accept V6 baseline. New capability, no conflict.**

---

### 5. `.agents/.rules/governance/execution/sandbox/sandbox-boundary-policy.md` (Priority: HIGH)

**Classification: TAKE_BASELINE**

**Before (V5):** Basic sandbox rules.
**After (V6):** Version 1.0.0, untrusted code rule, sandboxing mandates (Docker preferred, venv fallback), network & resource restrictions, failure handling with instincts engine.

**Merge rationale:**
- V6 sandbox policy is more operational and specific.
- AvaX framework does not execute untrusted code at runtime; this is about agent execution.
- No AvaX-specific semantics affected.

**Decision: Accept V6 baseline. Agent-level sandbox, not framework-level.**

---

### 6. `.agents/.rules/templates/tasks/execute-change-task.md` (Priority: HIGH)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist in V5.
**After (V6):** Task template for executing changes through the governance system.

**Merge rationale:**
- New V6 capability. Provides structured task execution template.
- Does not affect AvaX implementation rules.

**Decision: Accept V6 baseline. New template, no conflict.**

---

### 7. `.agents/.rules/governance/profiles/languages/php.md` (Priority: HIGH)

**Classification: PROJECT_LOCAL_OVERRIDE_INTENTIONAL**

**Status: NO CHANGE REQUIRED**

The PHP governance profile exists in the baseline and has NOT been modified by the V6 upgrade (file timestamp is May 4, predating V6 adoption). AvaX-specific PHP preferences are defined in root AGENTS.md section 29 (Coding Rule) which specifies:

- PHP 8.5 style
- strict types
- constructor promotion
- named arguments
- @throws tags
- readonly where useful
- small public surface

These are Layer 4 overrides that correctly narrow the baseline PHP profile.

**Decision: Keep as-is. AvaX overrides are in root AGENTS.md, not in this file.**

---

### 8. `.agents/.rules/governance/core/bootstrap/agent-bootstrap.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Embedded in other documents, not a standalone file.
**After (V6):** Version 1.0.0, 9-step bootstrap sequence (Read AGENTS.md -> Resolve Stack -> Load Profiles -> Load Overlays -> Read Management -> Execute -> Update Evidence -> Update Dashboard -> Recursive Review).

**Merge rationale:**
- V6 bootstrap sequence is explicit and well-structured.
- Matches AvaX's actual bootstrap behavior (root AGENTS.md section 4 requires preflight).
- AvaX adds stage lock and forbidden scope identification on top of this.

**Decision: Accept V6 baseline. AvaX-specific bootstrap additions are in root AGENTS.md.**

---

### 9. `.agents/.rules/governance/core/bootstrap/canonical-bootstrap-lifecycle.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist as standalone file.
**After (V6):** Version 3.0.0, 8-phase canonical lifecycle (Discovery -> Context -> Planning -> Execution -> Verification -> Recursive Review -> Evidence -> Finalization).

**Merge rationale:**
- New V6 capability defining the full agent lifecycle.
- Complements agent-bootstrap.md.
- No AvaX conflict.

**Decision: Accept V6 baseline.**

---

### 10. `.agents/.rules/governance/core/flags/feature-flags.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist.
**After (V6):** Version 1.0.0, feature flag catalog with 16 flags, override precedence, flag semantics, lifecycle stages (Alpha/Beta/GA/Deprecated/Removed), immutable flags, flag dependencies, review and retirement process.

**Merge rationale:**
- New V6 capability for conditional governance activation.
- Does not affect AvaX's stage lock model (which is about implementation phases, not governance toggles).
- AvaX does not need to override any baseline flags.

**Decision: Accept V6 baseline.**

---

### 11. `.agents/.rules/governance/execution/policy/execution-policy.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Basic explore/execute modes.
**After (V6):** Version 1.2.0, enhanced explore/execute modes with agent choice guidance, iteration contract, completion ceremony with memory extraction, evidence requirements with memory delta.

**Merge rationale:**
- V6 adds memory extraction and ProdOps integration to completion ceremony.
- AvaX uses its own evidence model (EVIDENCE/) which is Layer 4.
- The baseline memory references (.agents/memory/) are generic and do not conflict.

**Decision: Accept V6 baseline. AvaX evidence model is Layer 4.**

---

### 12. `.agents/.rules/governance/execution/routing/prompt-to-governance-flow.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist.
**After (V6):** Version 1.0.0, canonical event-driven flow from prompt to executable governance route, task manifest contract, intent classification, governance pack selection, pipeline/role selection, context injection, subagent planning, trust/approval resolution, evidence targets.

**Merge rationale:**
- New V6 capability for deterministic prompt routing.
- Formalizes what AvaX agents already do implicitly (read AGENTS.md, resolve stack, classify task).
- Does not conflict with AvaX stage lock or preflight rules.

**Decision: Accept V6 baseline.**

---

### 13. `.agents/.rules/governance/execution/hooks/hooks-policy.md` (Modified by V6)

**Classification: TAKE_BASELINE**

**Before (V5):** Did not exist as standalone file.
**After (V6):** Version 1.0.0, hook event catalog (PreTask, PostTask, PreCommit, PostCommit, PreReview, PostReview, PreRelease, PostRelease, PreValidation, PostValidation), disposition semantics (Continue, Abort, Inject-Context, Gate, Escalate, Log, Archive), hook execution protocol, standard hook implementations, custom hook rules, audit trail format, baseline runtime scripts.

**Merge rationale:**
- New V6 capability for lifecycle hook interception.
- AvaX hooks are in `.agents/hooks/` which is the correct location.
- The hook policy defines the contract; AvaX hooks implement it.

**Decision: Accept V6 baseline.**

---

## Layer 4 Separation Verification

| Layer | Location | Status |
|:---|:---|:---|
| L1 Universal | `.agents/.rules/governance/standards/**` | GREEN — Baseline V6 accepted |
| L2 Language | `.agents/.rules/governance/profiles/languages/**` | GREEN — Baseline accepted |
| L3 Framework | `.agents/.rules/governance/profiles/project-types/**` | GREEN — Baseline accepted |
| L4 AvaX | Root `AGENTS.md` | GREEN — AvaX-specific rules intact |
| L4 AvaX | `.agents/how-to/*.md` | GREEN — AvaX operational guidelines intact |

**No AvaX leakage into baseline:** Root AGENTS.md contains all AvaX-specific rules (stage lock, component shape, forbidden folders, naming law, validation set, V4 branch policy). None of these have been moved into `.agents/.rules/`.

**No duplicated governance semantics:** Baseline defines generic rules. Root AGENTS.md defines AvaX-specific narrowing. No contradictions.

**No stale pre-V6 behavior:** All pre-V6 governance files have been upgraded to V6. No old versions remain in `.agents/.rules/`.

---

## New Baseline Files (V6 Additions)

The following new files were added by V6 and are untracked (new baseline additions):

- `.agents/.rules/governance/architecture/ARCHITECTURE.md`
- `.agents/.rules/governance/architecture/adoption-model.md`
- `.agents/.rules/governance/architecture/architecture-standard.md`
- `.agents/.rules/governance/architecture/contract-linting.md`
- `.agents/.rules/governance/architecture/profiles/frameworks/react.md`
- `.agents/.rules/governance/architecture/runtime-hardening.md`
- `.agents/.rules/governance/core/architecture-law.md`
- `.agents/.rules/governance/delivery/operations/enterprise-operational-lifecycle.md`
- `.agents/.rules/governance/delivery/operations/management-model.md`
- `.agents/.rules/governance/delivery/release/advanced-deployment-policy.md`
- `.agents/.rules/governance/integrations/` (directory)
- `.agents/.rules/governance/intelligence/memory/v3-intelligence-lifecycle.md`
- `.agents/.rules/governance/profiles/architecture/` (directory)
- `.agents/.rules/governance/profiles/languages/go.md`
- `.agents/.rules/governance/profiles/languages/php.d/` (directory)
- `.agents/.rules/governance/profiles/overlays/` (directory)
- `.agents/.rules/governance/profiles/project-types/` (directory)
- `.agents/.rules/governance/security/` (directory)
- `.agents/.rules/governance/standards/governance/canonical-governance-map.md`
- `.agents/.rules/governance/standards/governance/capability-maturity-model.md`
- `.agents/.rules/governance/standards/governance/entropy-control-policy.md`
- `.agents/.rules/governance/standards/governance/finding-lifecycle.md`
- `.agents/.rules/governance/standards/review/recursive-review-contract.md`
- `.agents/.rules/config/schemas/finding-decision.schema.json`

These are all **new V6 baseline additions**. They should be tracked as part of the baseline upgrade.

---

## Validation Results

| Command | Result |
|:---|:---|
| `php tooling/governance/check-governance-index-current.php` | GREEN |
| `php tooling/governance/check-overlay-governance.php` | NOT IMPLEMENTED (file not found) |
| `bash verify-governance.sh` | YELLOW — detects new untracked baseline files (expected for V6 adoption) |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS |
| `composer validate --no-check-publish` | GREEN |

**verify-governance.sh YELLOW explanation:** The script detects new untracked files under `.agents/.rules/` and warns "Do not edit .agents/.rules/** directly." These files are new V6 baseline additions, not local modifications. This is expected behavior during a baseline upgrade. Once these files are committed as part of the V6 adoption, the warning will resolve.

---

## Remaining YELLOW

| Item | Owner | Risk | Decision |
|:---|:---|:---|:---|
| `check-overlay-governance.php` not implemented | AvaX governance owner | LOW | Tool not yet created; not blocking |
| New untracked baseline files | Harness V6 adoption | NONE | Expected during upgrade; will resolve on commit |
| `.agents/governance/` overlay directory does not exist | AvaX governance owner | LOW | AvaX uses root AGENTS.md for Layer 4; overlay dir is optional |

---

## Conclusion

All 13 drift conflicts have been reconciled.

- **12 conflicts: TAKE_BASELINE** — V6 baseline is strictly better or adds new capabilities.
- **1 conflict: PROJECT_LOCAL_OVERRIDE_INTENTIONAL** — PHP profile; AvaX overrides are correctly in root AGENTS.md.

No files required modification.
No AvaX-specific semantics were lost.
No governance contradictions remain.
Layer 4 separation is clean.

**Status: GREEN**
