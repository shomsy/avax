# Separation of Concern Governance — Evidence Summary

**Branch:** `governance/separation-of-concern`
**Worktree:** `../avax-governance-separation-of-concern`
**Base:** `main`
**Source:** [emlandre.com — On Separation of Concern](https://emlandre.com/2024/01/05/on-separation-of-concern-soc/)

---

## Rules Added

### Canonical — how-to-architecture.md Section 57

16 sub-sections:
- 57.1 Universal Concern Definition
- 57.2 Separation at Every Level
- 57.3 Room Rule
- 57.4 Valid Separation Test
- 57.5 Concern Ownership Rule
- 57.6 Cross-Cutting Concern Rule
- 57.7 Composition After Separation Rule
- 57.8 Change-Axis Test
- 57.9 SoC and Coupling
- 57.10 SoC and Cognitive Load
- 57.11-57.13 GREEN/YELLOW/RED Criteria
- 57.14 SoC as Review Lens
- 57.15 SoC in Refactoring
- 57.16 AvaX SoC Summary

### Canonical — how-to-design-components.md Section 32

13 sub-sections:
- 32.1 Universal Concern Definition
- 32.2 Separation at Every Component Level
- 32.3 Room Rule for Components
- 32.4 Valid Component Separation
- 32.5 Component Concern Ownership
- 32.6 Cross-Cutting Concerns in Components
- 32.7 Composition After Component Separation
- 32.8 Change-Axis Test for Components
- 32.9 SoC and Component Coupling
- 32.10-32.12 GREEN/YELLOW/RED Criteria
- 32.13 AvaX Component SoC Summary

### Cross-References Added

- `how-to-clean-code.md` — Section 24.5: Separation of Concern Cross-Reference
- `how-to-code-review.md` — Section 26: Separation of Concern Review Rule (7-item checklist)
- `how-to-dependency-injection.md` — Section 16.5: Separation of Concern in DI
- `how-to-runtime-composition.md` — Section 13.5: Separation of Concern in Runtime Composition

### Skills Updated

- `avax-enterprise-codecraft` — Separation of Concern Gate (6 evaluation points)
- `avax-component-dogfooding` — Separation of Concern in Dogfooding
- `avax-security-threat-model` — Separation of Concern in Security
- `avax-test-evidence-quality` — Separation of Concern in Testing

### Dictionary Terms Created (7)

- Separation of Concern (SoC)
- Concern
- Cross-Cutting Concern
- Change Axis
- Coupling
- Cohesion
- Composition Boundary

---

## Files Changed

1. `.agents/how-to/how-to-architecture.md`
2. `.agents/how-to/how-to-design-components.md`
3. `.agents/how-to/how-to-clean-code.md`
4. `.agents/how-to/how-to-code-review.md`
5. `.agents/how-to/how-to-dependency-injection.md`
6. `.agents/how-to/how-to-runtime-composition.md`
7. `.agents/skills/avax-enterprise-codecraft/SKILL.md`
8. `.agents/skills/avax-component-dogfooding/SKILL.md`
9. `.agents/skills/avax-security-threat-model/SKILL.md`
10. `.agents/skills/avax-test-evidence-quality/SKILL.md`
11. `.agents/dictionary/framework-terms.md`
12. `.agents/management/evidence/generated/separation-of-concern-governance/summary.md`

---

## Key Concepts Translated

| Source Concept | AvaX Translation |
|---|---|
| SoC as universal principle | Behind modularization, encapsulation, functions, objects, layering, flows, capabilities |
| Rooms metaphor | Room Rule: one purpose per unit, storage room anti-pattern |
| Concern = reason to change | Universal Concern Definition: any reason to understand/change/test/secure/observe |
| Separation ≠ file splitting | Valid Separation Test: must reduce coupling, not just file count |
| Cross-cutting concerns | Must be explicit capabilities, not scattered helpers/globals/inline logic |
| Composition after separation | Clean parent/gateway/configuration boundaries, not god builders |
| Change-axis test | 5-question test: what causes change, related, testable, understandable, dependent |

---

## Validation Status

- `composer validate --no-check-publish`: pending
- `git diff --check`: pending

---

## GREEN Criteria

SoC governance is GREEN when:
- every class has one clear reason to change
- cross-cutting concerns are explicit capabilities with clear boundaries
- composition happens through clean configuration or gateway boundaries
- no god objects or storage-room units exist
- change-axis test passes for all production units
- splitting a unit demonstrably reduces coupling or cognitive load

## RED Criteria

SoC governance is RED when:
- units have multiple unrelated reasons to change
- cross-cutting logic is scattered as helpers and inline code
- file splitting increased count but not clarity
- composition rebuilds the mess through god builders
- change-axis test fails with unrelated change drivers
- understanding one unit requires knowing about unrelated subsystems
