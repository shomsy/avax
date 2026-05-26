# Governance Hardening Pass — Deviation Audit + Correction Loop

## Date
2026-05-24

## Branch
main (worktree: avax-auth-rewrite-v2)

## Base Commit
48f9f3557

## Mode
Harness-Full

## Skills Discovered
- avax-enterprise-remediation (bootloader)
- avax-source-of-truth-resolver
- validation (skill)
- review (skill)

## Skills Used
- validation (output format update)
- review (output format update)

## How-To Files Read
- `.agents/how-to/verification/how-to-code-review.md`
- `.agents/how-to/verification/how-to-production-readiness.md`
- `.agents/how-to/architecture/how-to-architecture.md`
- `.agents/how-to/components/how-to-design-components.md`
- `.agents/how-to/verification/how-to-system-security.md`
- `.agents/how-to/verification/how-to-unit-test.md`
- `.agents/how-to/documentation/how-to-document.md`
- `.agents/how-to/implementation/how-to-dependency-injection.md`
- `.agents/how-to/architecture/how-to-runtime-composition.md`

## Source-of-Truth Decision
- AGENTS.md is the root contract (version 3.0.0)
- Existing governance documents define pieces of severity, status, and review but lack unified deviation audit lifecycle
- No contradiction detected — this pass strengthens, does not override

---

## Changes Made

### 1. AGENTS.md — New Laws 16-18
Added three new non-negotiable laws:
- **Law 16**: Deviation audit is an execution gate, not informational. Correction mandatory before GREEN.
- **Law 17**: No finding may be fixed by suppression. Suppression is itself a governance violation.
- **Law 18**: GREEN status must be semantically justified. Every deviation must be classified.

### 2. AGENTS.md — §1A: Mandatory Deviation Audit and Correction Lifecycle
Five-stage lifecycle:
1. VALIDATION — run all gates, record outputs
2. DEVIATION AUDIT — classify findings by canonical severity
3. CORRECTION PASS — fix BLOCKER/HIGH, no suppression allowed
4. RE-VALIDATION — rerun all validation, verify no new findings
5. COMMIT GATE VERIFICATION — verify no uncorrected findings, no suppression, justified GREEN

Loop enforcement: correction can introduce new deviations, re-audit is mandatory.

### 3. AGENTS.md — §1B: Canonical Severity Classification System
Unified severity levels across all governance documents:
- **BLOCKER**: prevents safe operation, blocks everything
- **HIGH**: significant gap, blocks GREEN/commit by default
- **MEDIUM**: maintainability issue, must be tracked
- **LOW**: cleanup, does not block
- **INFO**: observation only

Includes:
- Blocking matrix (GREEN/commit/merge/release)
- Detailed definitions per severity level with concrete examples
- Severity escalation rules
- Canonical finding format

### 4. AGENTS.md — §1C: No Fixed By Suppression Rule
Explicitly forbids:
- Disabling tests to achieve GREEN
- Broadening ignore patterns
- Weakening assertions
- Hiding failures behind fallbacks
- Adding nullable escape hatches
- Bypassing gates
- Silent catch-all handling
- "Temporary" fake fixes
- Changing tests to match broken behavior

Suppression without exception register entry is a BLOCKER governance violation.

### 5. AGENTS.md — §1D: GREEN Status Justification Requirement
Mandatory justification section for every GREEN claim:
- validation results
- gate results
- deviation audit findings by severity
- corrections made
- remaining deviations
- suppression check
- exception register entries
- risk assessment
- severity decision (why not YELLOW/RED)
- evidence files

Status truth table maps conditions to required status.

### 6. AGENTS.md — §1E: Remaining Drift Classification Rule
Every unresolved issue must contain:
- severity (canonical)
- impact
- owner
- phase allowance
- mitigation
- future plan
- evidence

Four drift categories: Active Blocker, Accepted Debt, Phase-Locked, Cleanup Queue.
Unclassified drift is BLOCKER.

### 7. how-to-code-review.md — Integration
- Added deviation audit lifecycle as step 4 in completion checklist
- Added suppression check, GREEN justification, drift classification as steps 10-12
- Added all five new governance rules to required review checks
- Added all five new rules as commit block conditions

### 8. how-to-production-readiness.md — Integration
- Updated completion short version to include deviation audit, suppression, GREEN justification, drift classification
- Updated severity definitions to reference canonical severity system (AGENTS.md §1B)

### 9. validation/SKILL.md — Integration
- Added deviation audit, suppression check, GREEN justification, drift classification to output format
- Added governance cross-reference section

### 10. review/SKILL.md — Integration
- Added deviation audit, suppression check, GREEN justification, drift classification to output format
- Added governance cross-reference section

---

## Design Decisions

### Why Sections 1A-1E Instead of New Numbered Sections
Inserted as 1A-1E between §1 (Non-Negotiable Laws) and §2 (Operating Modes) to:
- Keep proximity to the laws they operationalize
- Maintain clear reference chain: laws → lifecycle → severity → suppression → justification → drift
- Avoid renumbering all 36 existing sections

### Why Canonical Severity Instead of Per-Document Severity
Existing documents use ad hoc severity (security uses BLOCKER/HIGH, review uses Low/Medium/High/Rewrite Risk, production readiness uses BLOCKER/HIGH/MEDIUM/LOW). A single canonical system ensures:
- Cross-document finding comparison is possible
- Agents cannot downgrade severity by switching documents
- Commit gates have consistent blocking behavior

### Why Suppression Is BLOCKER, Not HIGH
Suppression is concealment, not merely a gap. Concealing a finding is worse than having a finding. It prevents the governance system from functioning. Therefore BLOCKER.

### Why GREEN Justification Is Mandatory
GREEN without justification is decorative. Decorative GREEN is fake compliance. The justification section forces agents to explicitly defend the status rather than merely declaring it.

---

## Governance Consistency Check

| Rule | Conflicts With Existing Governance? | Resolution |
|------|-----------------------------------|------------|
| Deviation audit lifecycle | No — extends existing recursive review loop | Integrated into how-to-code-review.md completion checklist |
| Canonical severity | No — unifies existing ad hoc usage | Existing definitions map cleanly to canonical levels |
| Suppression ban | No — aligns with "no fake GREEN" and "gate self-test" rules | Makes implicit explicit with detection guidance |
| GREEN justification | No — extends existing status state machine | Status truth table formalizes existing GREEN/YELLOW/RED rules |
| Drift classification | No — extends existing exception register | Formalizes "pre-existing is not a status" rule from existing governance |

### Terminology Consistency
- Uses existing AvaX terminology: GREEN, YELLOW, RED, BLOCKER, HIGH, MEDIUM, LOW
- Uses existing document references: AGENTS.md, EVIDENCE/accepted-exceptions-ledger.md, EXECUTION.md
- Uses existing concepts: phase allowance, exception register, recursive governance review, stage lock
- Introduces new terms with clear definitions: deviation audit, correction pass, unclassified drift

### No Duplicate Authority
- Does not duplicate existing severity definitions — references them and unifies
- Does not duplicate existing status state machine — extends with justification requirement
- Does not duplicate existing review process — integrates as additional gates
- All new rules reference AGENTS.md as source of authority

---

## Files Changed

| File | Change Type | Lines Added | Lines Removed |
|------|-------------|------------:|--------------:|
| `AGENTS.md` | New sections 1A-1E, laws 16-18 | ~480 | 0 |
| `.agents/how-to/verification/how-to-code-review.md` | Integration | ~16 | 4 |
| `.agents/how-to/verification/how-to-production-readiness.md` | Integration | ~10 | 3 |
| `.agents/skills/validation/SKILL.md` | Output format update | ~12 | 0 |
| `.agents/skills/review/SKILL.md` | Output format update | ~14 | 0 |
| `EVIDENCE/governance-hardening-deviation-audit.md` | New evidence | — | — |

---

## Validation

Governance validation for this change:
- No PHP code changed — PHPStan/phpunit not applicable
- Structural validation: all cross-references resolve to existing sections
- Terminology validation: all terms consistent with AGENTS.md and how-to documents
- No contradictions introduced — all new rules extend, never override existing rules
- Section references use AGENTS.md §X format consistently

---

## Remaining Risks

| Risk | Severity | Mitigation |
|------|----------|------------|
| Existing documents with ad hoc severity not yet updated to reference canonical system | LOW | Future agents will reference AGENTS.md §1B; can be cleaned up incrementally |
| Deviation audit lifecycle adds overhead to small changes | MEDIUM | Phase allowance and focused validation allow narrow scope; lifecycle scales to change size |
| Section numbering (1A-1E) may look unconventional | LOW | Clear and unambiguous; avoids mass renumbering of 36 sections |

---

## Status

**GREEN**

### Why This Is GREEN

- **validation**: Governance document structural validation complete — all cross-references resolve, no contradictions detected
- **gates**: No code gates applicable (governance-only change)
- **deviation_audit**: 0 BLOCKER, 0 HIGH, 3 LOW (existing documents with ad hoc severity not yet updated)
- **corrections**: N/A (new governance, not repair)
- **remaining_deviations**: 3 LOW items — existing how-to files with local severity definitions that should reference canonical system (incremental cleanup)
- **suppression_check**: No suppression detected
- **exception_register**: No entries added
- **risk_assessment**: Low risk — governance-only change, no runtime behavior affected, all new rules extend existing governance
- **severity_decision**: GREEN (not YELLOW) because remaining deviations are LOW severity documentation cleanup, not governance gaps
- **evidence**: This file
