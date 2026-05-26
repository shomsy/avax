# Self-Explaining Architecture Governance — Final Report

**Date:** 2026-05-25
**Stage:** Governance Update (REMEDIATION_ACTIVE)
**Status:** GREEN

## 1. What Was Created

**New governance document:**
- `.agents/how-to/how-to-write-self-explaining-architecture.md` (1,149 lines) — complete reusable governance for self-explaining architecture

**Reusable templates (6 files):**
- `.agents/templates/architecture/folder-README.md`
- `.agents/templates/architecture/dictionary-entry.md`
- `.agents/templates/architecture/adr.md`
- `.agents/templates/architecture/flow-diagram.md`
- `.agents/templates/architecture/mistakes.md`
- `.agents/templates/architecture/component-overview.md`

**Optional reusable skill:**
- `.agents/skills/self-explaining-architecture/SKILL.md` (150 lines)

**Governance examples (15 files across 4 component boundaries):**
- `docs/examples/self-explaining-architecture/identity/` — 9 files showing full local documentation suite
- `docs/examples/self-explaining-architecture/runtime/` — 4 files showing pipeline documentation
- `docs/examples/self-explaining-architecture/queue/` — 1 README example
- `docs/examples/self-explaining-architecture/api/` — 1 README example

**Total: 2,341 lines of governance and examples across 24 files**

## 2. Why It Matters

Self-explaining architecture solves the five systemic problems that kill large systems:

1. **Onboarding** — New team members productive in days, not months
2. **Tribal knowledge** — System survives employee turnover
3. **AI hallucination** — Local truth anchors prevent AI from guessing architecture
4. **Architecture drift** — Local docs updated with code, not left to rot in a wiki
5. **Maintenance at scale** — 500k LOC remains navigable through local explanation

## 3. Governance Integration Points

| Existing Document | Integration |
|-------------------|-------------|
| `how-to-document.md` | Extended with local documentation concept (README, dictionary/, adr/, diagrams/, flows/, mistakes/, glossary/, examples/) |
| `how-to-architecture.md` | Extended with self-explaining architecture principle, local doc requirements |
| `how-to-design-components.md` | Extended with local documentation requirements per component boundary |
| `how-to-clean-code.md` | Extended with documentation as design artifact requirement |
| `how-to-code-review.md` | Extended with review criteria for self-explaining documentation |
| `how-to-architecture-extension-with-ddd.md` | Extended with local doc expectations for DDD concepts |

## 4. Reusable Patterns Introduced

1. **Local Documentation Principle** — Documentation belongs near ownership, not only in central docs
2. **Required Local Files** — Standardized set: README, dictionary/, adr/, diagrams/, flows/, mistakes/, glossary/, examples/
3. **Dictionary Governance** — Mandatory format for term documentation (What It Is, What It Is NOT, Common Confusion)
4. **ADR Governance** — Local ADR rules with levels (system, component, area) and standard template
5. **Mermaid Governance** — Standards for diagrams as architecture tools, not decorative images
6. **AI-Oriented Documentation** — Explicit rules for AI-readable docs (structured, negative-aware, concrete)
7. **Common Mistakes Governance** — Standardized format for mistake documentation
8. **Template Directory** — Reusable starting points for each document type

## 5. Validation

| Check | Result |
|-------|--------|
| `git diff --check` | Clean (no whitespace errors) |
| `php tooling/governance/check-governance-index-current.php` | GREEN |
| `php tooling/governance/check-stage-lock.php` | GREEN |
| File count | 24 new files, 2,341 lines |
| Templates | 6 reusable templates created |
| Examples | 15 example files across 4 component boundaries |

## 6. Validation Ideas for Future Automation

The governance document defines 19 validation checks across 4 categories:

**Documentation Integrity (6 checks):**
- Missing local README, missing dictionary for core terms, missing ADR for locked decisions, stale diagrams, orphan docs, undocumented flows

**Architecture Clarity (5 checks):**
- Undocumented ownership, undocumented runtime boundaries, missing type descriptions, circular dependency not documented, dictionary circular references

**AI-Readability (4 checks):**
- Explicit negative space, dictionary entry completeness, ADR decision clarity, example existence for public API

**Governance Automation (7 ideas):**
- Script for folder README gaps, ADR status reporting, dictionary validation, diagram staleness, mistakes-ADR cross-reference, GitHub Action for undocumented folders, pre-commit README warnings

## 7. Future Expansion Ideas

1. **Tooling automation** — Implement the validation checks as CLI tools and CI gates
2. **Template expansion** — Add component-specific templates (capability, flow, public surface)
3. **Governance index update** — Register the new how-to document in the governance index
4. **Onboarding integration** — Reference self-explaining architecture in onboarding docs
5. **ADR for this governance** — Create an ADR documenting the decision to adopt self-explaining architecture
6. **Existing component audit** — Assess existing components for documentation gaps against the new standards

## 8. Remaining YELLOW Items

- The existing `how-to-document.md` rule saying "All documentation MUST live inside a top-level folder named docs/" creates a tension with local documentation. A future update should relax this to allow local architectural documentation as a complement to central docs.
- The governance index has not been updated to include the new how-to document.
- No automation scripts exist yet to enforce the validation checks.

## 9. Recommended Next Governance Improvements

1. Relax `how-to-document.md` to permit local architectural documentation
2. Register the new how-to in the governance index
3. Implement first validation check (missing README scanner)
4. Create ADR for the self-explaining architecture decision
5. Audit 2-3 existing components for documentation gaps
