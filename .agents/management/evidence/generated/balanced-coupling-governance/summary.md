# Balanced Coupling Governance — Evidence Summary

**Date:** 2026-05-22
**Branch:** main (governance-only change)
**Status:** GREEN

## Scope

Added Balanced Coupling and Local/Global Complexity rules to AvaX governance based on GOTO 2025 transcript by Vlad Khononov & Sheen Brisals, "Balancing Coupling in Software Design".

This is governance/docs/skills only — NO production PHP code changed.

## 8 Rules Added

### 1. Local vs Global Complexity Rule
Modularity must not reduce local complexity by increasing global complexity.
A refactoring that makes one unit simpler but the system harder to understand has failed.

### 2. Modularity as Predictable Change Rule
The purpose of modularity is not separation — it is predictable change.
A module boundary is correct only when change location is obvious, impact is predictable, integration knowledge is explicit, and cognitive load is reduced.

### 3. Balanced Coupling Rule (Enhanced)
Coupling is not automatically evil. Coupling must be explicit, intentional, and balanced.
Assessed through: knowledge exchanged, integration strength, physical/logical distance, change frequency, co-change pressure.

### 4. Change-Together Smell
When multiple modules require simultaneous edits for the same change, the decomposition is suspect.
Frequent co-change across boundaries is a decomposition problem, not a coordination problem.

### 5. Physical Distance Is Not Decoupling
Putting modules in different folders, packages, namespaces, processes, or repositories does not remove coupling if knowledge leaks between them.
Physical separation is organizational, not architectural.

### 6. Integration Knowledge Rule
Every module boundary that exchanges knowledge MUST document: what crosses, who owns it, who depends on it, change-together risk, whether intentional.
Undocumented integration knowledge is accidental coupling.

### 7. Intrusive Coupling Blocker
A unit MUST NOT depend on another unit's implementation details: storage layout, private state shape, private workflow order, lifecycle internals, unpublished invariants, private naming.
If renaming an internal class in Module B breaks Module A, the coupling is intrusive.

### 8. Decomposition Principle Review
Before splitting a module, the decomposition principle MUST be recorded.
Good reasons: change locality, information ownership, cohesive behavior, predictable impact.
Bad reasons: file is long, technical category, constructor size, framework layer.

## Files Updated

| File | Changes |
|------|---------|
| `.agents/how-to/architecture/how-to-architecture.md` | Section 58 expanded with 8 subsections (58.2-58.10): Local vs Global Complexity, Modularity as Predictable Change, Balanced Coupling Enhanced, Change-Together Smell, Physical Distance Is Not Decoupling, Integration Knowledge Rule, Intrusive Coupling Blocker, Decomposition Principle Review |
| `.agents/how-to/components/how-to-design-components.md` | Section 32 expanded with 32.3 Component Decomposition Principle Rule and 32.4 Component Coupling Assessment Rule |
| `.agents/how-to/implementation/how-to-clean-code.md` | Section 5.16 added: Balanced Coupling and Complexity Rules with 3 subsections: Local vs Global Complexity, Modularity as Predictable Change, Decomposition Principle Review |
| `.agents/how-to/verification/how-to-code-review.md` | Section 27 expanded with 10 review checks (27.1.1-27.1.10): Afferent/Efferent, Temporal, Semantic, Local vs Global Complexity, Modularity as Predictable Change, Change-Together Smell, Physical Distance Is Not Decoupling, Integration Knowledge, Intrusive Coupling, Decomposition Principle |
| `.agents/how-to/implementation/how-to-dependency-injection.md` | Section 7.7 added: Intrusive Coupling Blocker for DI — forbidden DI patterns, allowed DI patterns, DI intrusive coupling detection, ServiceProvider responsibility |
| `.agents/skills/avax-enterprise-codecraft/SKILL.md` | Added Balanced Coupling and Complexity Assessment section after Coupling Direction: Local vs Global Complexity, Change-Together Smell, Intrusive Coupling, Integration Knowledge, Decomposition Principle, Physical Distance Check |
| `.agents/skills/avax-component-dogfooding/SKILL.md` | Added Balanced Coupling Check for Component Dependencies to Component Dependency Review: Intrusive Coupling, Integration Knowledge, Change-Together Smell, Physical Distance Check |
| `.agents/skills/avax-api-compatibility-contract/SKILL.md` | Resolved merge conflict. Added Integration Knowledge Documentation for API Boundaries section. |
| `.agents/skills/avax-test-evidence-quality/SKILL.md` | Added Coupling Test Evidence Rule section: Intrusive Coupling Detection Tests, Change-Together Proof, Integration Knowledge Contract Tests, Local vs Global Complexity Tests |
| `.agents/dictionary/framework-terms.md` | Updated Coupling and Cohesion definitions. Added 8 new terms: Local Complexity, Global Complexity, Intrusive Coupling, Integration Strength, Change-Together Smell, Physical Distance, Decomposition Principle, Modularity |

## Governance Cross-References

All new rules cross-reference each other consistently:
- Architecture Section 58 is the canonical source
- Design Components Section 32 adapts for component boundaries
- Clean Code Section 5.16 adapts for clean-code judgment
- Code Review Section 27 provides review checks
- Dependency Injection Section 7.7 covers DI-specific intrusive coupling
- All 4 skills updated with task-relevant checks
- Dictionary updated with 10 terms (2 enhanced, 8 new)

## Severity Classification

| Severity | Rules |
|----------|-------|
| BLOCKER | Balanced Coupling Rule (Enhanced), Physical Distance Is Not Decoupling, Intrusive Coupling Blocker |
| HIGH | Local vs Global Complexity, Modularity as Predictable Change, Change-Together Smell, Integration Knowledge Rule, Decomposition Principle Review |

## Validation

- Governance documents are internally consistent
- No production PHP code changed
- All cross-references point to correct sections
- Dictionary terms link to correct governance sections
- Merge conflict resolved in avax-api-compatibility-contract/SKILL.md

## Remaining Work

None — all 8 rules added to all target files.
All 10 dictionary terms added.
All 4 skills updated.
Evidence summary created.
