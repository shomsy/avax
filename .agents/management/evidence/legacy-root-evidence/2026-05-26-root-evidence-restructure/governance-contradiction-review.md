# Governance Contradiction Review

**Date:** 2025-01-XX
**Scope:** 16 governance documents after governance lock pass
**Status:** MERGE_BLOCKED

---

## Findings

### BLOCKER: Unresolved Git Merge Conflict in Governance Document

**Documents:** `.agents/how-to/components/how-to-design-components.md`
**Location:** Line 2660
**Severity:** BLOCKER

**Issue:**
`how-to-design-components.md` contains an unresolved git merge conflict marker (`<<<<<<< HEAD` at line 2660). The conflict separates two different section numbering schemes:
- HEAD version: `## 31. Hard Enterprise OOP Boundary Rules`
- Merged version: `## 30. Universal Enterprise Codecraft Rule` (implied by the closing `>>>>>>> 86677e5b8e9e4a41502c82272c1ead440c422dbb` marker)

**Impact:**
A governance document with an unresolved merge conflict has no defined authoritative content. Any rule that references this file cannot determine which version of the rules apply. Automated tools parsing this file may fail or produce incorrect results.

**Fix:**
Resolve the merge conflict by choosing one version or manually merging both sections. Run `git diff` to see both sides and create a single coherent version.

---

### BLOCKER: Missing Ownership Boundary Statement in Identity README

**Documents:** `components/Identity/docs/README.md`
**Location:** Entire file
**Severity:** BLOCKER

**Issue:**
`components/Identity/docs/README.md` does not contain the mandatory ownership boundary statement required by the self-explaining architecture standard. Per `how-to-write-self-explaining-architecture.md` §3.1, every important boundary MUST have a README.md that answers:
1. What this boundary owns (positive space)
2. What does NOT belong here (negative space — mandatory for AI grounding)
3. Which platform plane it belongs to
4. What public API it exposes
5. What flows it owns or participates in
6. What capabilities it provides
7. How it is configured
8. How it fails
9. How it is observed
10. How it is tested
11. What is not owned here

The Identity README contains only a directory structure listing and links to sub-documents. It lacks ALL of the required ownership statements.

**Impact:**
The Identity component is a complex boundary (10+ source subdirectories, security-critical). Without an ownership statement, AI agents and developers cannot determine what belongs in the Identity component versus other components. This creates silent boundary violations and architectural drift.

**Fix:**
Add a proper README.md with all 11 required sections per §3.1 of the self-explaining architecture standard.

---

### HIGH: Conflict Resolution Priority Differs Between AGENTS.md and GOVERNANCE_INDEX.md

**Documents:** `AGENTS.md` §3, `.agents/GOVERNANCE_INDEX.md` §138-§154
**Location:** AGENTS.md lines 646-658, GOVERNANCE_INDEX.md lines 165-177
**Severity:** HIGH

**Issue:**
Two different conflict resolution priority orders exist:

**AGENTS.md §3 (Rule Precedence — which document wins):**
1. AGENTS.md
2. .agents/GOVERNANCE_INDEX.md
3. .agents/how-to/**
4. .agents/skills/**
5. .agents/.rules/**
6. evidence
7. docs/**
8. README.md

**GOVERNANCE_INDEX.md §163-§177 (Conflict Resolution — which topic wins):**
1. correctness and safety
2. security
3. production readiness
4. architecture ownership
5. component filesystem law
6. DDD / patterns
7. clean code
8. coding standards
9. code style
10. documentation rules
11. local preference

These are technically addressing different questions (document hierarchy vs. topic hierarchy), but the GOVERNANCE_INDEX.md section is labeled "Conflict Resolution" without clarifying that it addresses topic-level conflicts rather than document-level conflicts. When a how-to document disagrees with AGENTS.md on a security topic, the reader cannot determine which resolution order applies.

**Impact:**
Ambiguous resolution path when documents disagree on security or correctness topics. An agent may resolve incorrectly by applying the wrong priority order.

**Fix:**
In GOVERNANCE_INDEX.md, rename the "Conflict Resolution" section to "Topic-Level Conflict Resolution" and add a cross-reference to AGENTS.md §3 for document-level precedence. Alternatively, unify both into a single two-axis resolution matrix.

---

### HIGH: Canonical Documentation Location Contradiction

**Documents:** `.agents/how-to/documentation/how-to-document.md`, `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`
**Location:** how-to-document.md §50-§68, how-to-write-self-explaining-architecture.md §4.5
**Severity:** HIGH

**Issue:**
Three documents define different documentation locations:

1. **how-to-document.md §50** (HARD RULE): "`docs/` is the **single canonical location** for all documentation... No documentation is allowed outside `docs/`"

2. **how-to-document.md §430** (Documentation Location Resolution Rule): "`docs/` is the canonical home for long-form documentation... A component README may summarize."

3. **how-to-write-self-explaining-architecture.md §4.5**: "Dictionaries live at: `components/<Area>/<Component>/dictionary/` OR `components/<Area>/<Component>/docs/dictionary/`"

The self-explaining architecture standard explicitly allows dictionaries (which are documentation) to live OUTSIDE the `docs/` folder at `components/<Area>/<Component>/dictionary/`. This directly contradicts the HARD RULE in how-to-document.md §68: "No documentation is allowed outside `docs/`".

The Identity component currently uses `components/Identity/docs/dictionary/` which satisfies both rules, but the governance itself is contradictory.

**Impact:**
Agents following the HARD RULE from how-to-document.md would reject valid dictionary placements at `components/<Area>/<Component>/dictionary/`. Agents following the self-explaining architecture standard might place documentation outside `docs/` and be flagged by the how-to-document.md checker.

**Fix:**
Update how-to-document.md §50-§68 to acknowledge the self-explaining architecture exception: component-level dictionaries and READMEs are allowed outside the top-level `docs/` as defined by the self-explaining architecture standard. Or update the self-explaining architecture standard to require `docs/dictionary/` exclusively.

---

### HIGH: Security Testing Gate Duplicated Across Three Documents

**Documents:** `.agents/how-to/verification/how-to-unit-test.md` §94, `.agents/how-to/verification/how-to-system-security.md` §40A, `AGENTS.md` §24A (partial)
**Location:** how-to-unit-test.md §94.1-§94.11, how-to-system-security.md §40A.1-§40A.5
**Severity:** HIGH

**Issue:**
The security testing gate (four mandatory test types: positive, negative, invalid-input, fail-closed) appears in three places with slightly different wording and enforcement details:

- **how-to-unit-test.md §94**: Most detailed, includes 11 sub-sections, enforcement checklist, relationship to other rules
- **how-to-system-security.md §40A**: Slightly different formatting, includes replay/invalidity behavior table, different enforcement checklist
- **AGENTS.md §24A**: References "security-path testing" with fail-closed behavior but less specific

The two detailed versions (§94 and §40A) have overlapping but not identical content. §94 includes a "Relationship to Other Rules" section (§94.11) that references §40A, acknowledging the overlap. §40A does not reference §94.

**Impact:**
When security testing requirements change, maintainers must update three documents. If they update only one, the documents contradict each other. The enforcement checklists differ slightly between §94.10 and §40A.5.

**Fix:**
Consolidate into a single authoritative source. The recommended approach: keep the detailed gate in how-to-system-security.md §40A (its natural home), have how-to-unit-test.md §94 reference it with a thin forwarding section, and have AGENTS.md §24A reference it. Add a "single source of truth" annotation to all three.

---

### HIGH: Self-Explaining Documentation Gate Duplicated Across Two Documents

**Documents:** `.agents/how-to/components/how-to-design-components.md` §24A, `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`
**Location:** how-to-design-components.md §24A.1-§24A.11, entire how-to-write-self-explaining-architecture.md
**Severity:** HIGH

**Issue:**
The self-explaining documentation gate (§24A with sub-sections §24A.1 through §24A.11) appears in full within `how-to-design-components.md` AND as the entire content of `how-to-write-self-explaining-architecture.md`. The content is substantially the same but with minor formatting differences.

**Impact:**
Same as the security testing duplication — maintenance burden, risk of drift, contradictory enforcement if one version is updated and the other is not.

**Fix:**
Keep the detailed standard in `how-to-write-self-explaining-architecture.md` (its natural home). Replace `how-to-design-components.md` §24A with a reference to `how-to-write-self-explaining-architecture.md` plus any component-design-specific additions.

---

### HIGH: Risk-Based Behavioral Testing Policy Duplicated

**Documents:** `AGENTS.md` §24A, `.agents/how-to/verification/how-to-unit-test.md` §93
**Location:** AGENTS.md §24A, how-to-unit-test.md §93
**Severity:** HIGH

**Issue:**
The risk-based behavioral testing policy appears in both AGENTS.md §24A and how-to-unit-test.md §93. how-to-unit-test.md §93 explicitly states: "Source: AGENTS.md §24A — Root Risk-Based Behavioral Testing Rule" and "This section is the detailed companion to AGENTS.md §24A." However, the two sections have different content structure and some different details:

- AGENTS.md §24A includes specific test type categories (happy path, failed-when, validation-path, security-path, runtime/lifecycle)
- how-to-unit-test.md §93 includes V1/V2/V3 phase definitions, forbidden shallow test patterns, and coverage policy details not in AGENTS.md

The relationship is described as "companion" but the content overlap is significant enough that changes to one may not be reflected in the other.

**Impact:**
Agents checking V1 coverage policy may look in the wrong document. Changes to forbidden shallow test patterns must be made in both places.

**Fix:**
Clarify the relationship explicitly: AGENTS.md §24A should contain the root rule with a reference to how-to-unit-test.md §93 for detailed phase policies and forbidden patterns. Or consolidate entirely into how-to-unit-test.md and have AGENTS.md reference it.

---

### MEDIUM: "Security Must Scream" Rule Duplicated

**Documents:** `.agents/how-to/verification/how-to-system-security.md` §51, `.agents/how-to/verification/how-to-code-review.md` §16
**Location:** how-to-system-security.md §51, how-to-code-review.md §16
**Severity:** MEDIUM

**Issue:**
The "Security Must Scream" rule (security-relevant code must be unmistakably visible in architecture, naming, and structure) appears in both documents with substantially similar content.

**Impact:**
Low-risk duplication since the rule is declarative rather than procedural, but still creates maintenance burden.

**Fix:**
Keep in how-to-system-security.md §51. Reference from how-to-code-review.md §16.

---

### MEDIUM: Identity ADR Status Mismatch with Governance Expectations

**Documents:** `components/Identity/docs/adr/0001-authentication-strategy.md`
**Location:** ADR-0001, Status field
**Severity:** MEDIUM

**Issue:**
ADR-0001 has status "proposed" but the Identity component is actively being developed and its authentication strategy is being implemented throughout the codebase. Per the self-explaining architecture standard, ADRs should reflect the actual decision state. A "proposed" status implies the decision is not yet made, but the codebase clearly treats the capability-based authentication strategy as decided.

**Impact:**
Developers and AI agents reading the ADR may question whether the authentication strategy is still open for debate, causing unnecessary second-guessing of established architectural decisions.

**Fix:**
Update ADR-0001 status to "accepted" (or the appropriate status per your ADR lifecycle).

---

### MEDIUM: Dictionary Location Ambiguity in Practice

**Documents:** `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md` §4.5, `components/Identity/docs/dictionary/authentication.md`
**Location:** how-to-write-self-explaining-architecture.md §4.5
**Severity:** MEDIUM

**Issue:**
The self-explaining architecture standard allows dictionaries at two locations:
- `components/<Area>/<Component>/dictionary/`
- `components/<Area>/<Component>/docs/dictionary/`

The Identity component uses `components/Identity/docs/dictionary/` which is valid. However, the standard does not specify which of the two locations is preferred or under what circumstances each should be used. This creates ambiguity for new components.

**Impact:**
New components may choose inconsistently, creating a mixed pattern across the codebase.

**Fix:**
Pick one as the canonical location and deprecate the other. Recommended: `components/<Area>/<Component>/docs/dictionary/` for consistency with the `docs/` canonical location rule.

---

### MEDIUM: Flow Documentation Path Not Specified in Governance

**Documents:** `components/Identity/docs/flows/login-flow.md`, `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`
**Location:** how-to-write-self-explaining-architecture.md (entire document)
**Severity:** MEDIUM

**Issue:**
The self-explaining architecture standard specifies required documentation artifacts (README.md, dictionary/, ADR/, Mermaid diagrams, mistakes.md) but does not mention runtime flow documentation (the `flows/` directory). The Identity component includes a `flows/` directory with detailed flow documentation (login-flow.md, etc.), but the governance standard neither requires nor describes this artifact type.

**Impact:**
Components may or may not include flow documentation. There is no governance standard for what flow documentation should contain, its format, or when it is required.

**Fix:**
Add flow documentation as an optional or required artifact in the self-explaining architecture standard, with format and complexity threshold guidance.

---

### LOW: Component README vs docs/ Canonical Resolution Wording

**Documents:** `.agents/how-to/documentation/how-to-document.md` §430
**Location:** how-to-document.md §430-§451
**Severity:** LOW

**Issue:**
The "Documentation Location Resolution Rule" states: "if component README and docs disagree, docs are canonical unless README is explicitly newer and linked." The phrase "explicitly newer and linked" is not precisely defined — there is no mechanism for determining which document is newer or what "linked" means (linked from where?).

**Impact:**
Minor ambiguity in edge cases where both documents exist and differ.

**Fix:**
Define "explicitly newer" (e.g., git commit date, file modification date) and "linked" (e.g., linked from the component README's table of contents, linked from docs/ index).

---

### LOW: Shallow Test Detection Patterns Not Fully Aligned

**Documents:** `tooling/testing/check-shallow-tests.php`, `AGENTS.md` §24A, `.agents/how-to/verification/how-to-unit-test.md` §93
**Location:** check-shallow-tests.php patterns list
**Severity:** LOW

**Issue:**
`check-shallow-tests.php` detects 12 patterns: `meaningless_assertion`, `fake_coverage_farming`, `constructor_only_test`, `implementation_coupled_test`, `no_negative_assertions`, `happy_path_only_auth`, `missing_failure_assertions`, `duplicated_test_logic`, `trivial_smoke_test`, `no_assertions`, `missing_fail_closed_test`, `getter_setter_only_test`.

AGENTS.md §24A lists forbidden shallow tests as: `assertTrue(true)`, `meaningless not-null assertions`, `constructor-only tests without behavior`, `getter/setter-only tests`, `coverage-padding tests`, `implementation-detail obsession`, `mocking the entire subject under test`, `tests that only prove execution`, `tests written only to increase percentages`.

The two lists overlap but are not identical. Some patterns in the checker are not in AGENTS.md (e.g., `happy_path_only_auth`, `duplicated_test_logic`, `trivial_smoke_test`), and some patterns in AGENTS.md are not in the checker (e.g., `mocking the entire subject under test`).

**Impact:**
Agents reading AGENTS.md may expect certain patterns to be caught by the checker that are not. The checker may flag patterns not explicitly forbidden in the governance documents.

**Fix:**
Align the two lists. Either update AGENTS.md §24A to reference the checker as the authoritative list, or update the checker to match the governance document.

---

### INFO: Identity Component Uses `docs/` Subdirectory Pattern

**Documents:** `components/Identity/docs/README.md`
**Severity:** INFO

**Observation:**
The Identity component places all documentation under `components/Identity/docs/` rather than at a top-level `docs/Identity/`. This follows the self-explaining architecture pattern (`components/<Area>/<Component>/docs/`) but differs from the `docs/` mirror-the-source-structure pattern described in how-to-document.md §62-§76.

This is consistent with the component-level documentation exception but worth noting for future component authors.

---

### INFO: Dictionary Entry Format Compliance

**Documents:** `components/Identity/docs/dictionary/authentication.md`
**Severity:** INFO

**Observation:**
The authentication dictionary entry correctly follows the required format from how-to-write-self-explaining-architecture.md §4:
- "What It Is" section: present
- "What It Is NOT" section: present (mandatory per §4.3)
- "Common Confusion" section: present (mandatory per §4.3)
- "In AvaX" section: present (provides context-specific grounding)

This is a well-formed dictionary entry.

---

## Summary

| Severity | Count |
|----------|-------|
| BLOCKER | 2 |
| HIGH | 4 |
| MEDIUM | 4 |
| LOW | 2 |
| INFO | 2 |

## Verdict

**MERGE_BLOCKED**

Two BLOCKER issues must be resolved before merge:
1. **Unresolved merge conflict** in `how-to-design-components.md` — this is a git state issue that makes the file's authoritative content undefined.
2. **Missing ownership boundary statement** in `components/Identity/docs/README.md` — the Identity component is a complex, security-critical boundary without the mandatory README content per the self-explaining architecture standard.

Additionally, 4 HIGH-severity contradictions create significant maintenance risk and should be resolved before or immediately after merge:
- Conflict resolution priority ambiguity between AGENTS.md and GOVERNANCE_INDEX.md
- Canonical documentation location contradiction
- Security testing gate duplicated across three documents
- Self-explaining documentation gate duplicated across two documents
- Risk-based behavioral testing policy duplicated
