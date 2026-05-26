# Governance Classification Audit

## GENERIC vs PROJECT_SPECIFIC vs MIXED Classification

### Method

Each governance file was classified by:
- Counting direct "AvaX" references
- Counting AvaX-specific architectural folder references (PublicSurface, Flows/, Capabilities/, System/, Configuration/)
- Evaluating whether the rules are reusable or project-locked

### How-To Files

| # | File | Lines | AvaX | Classification | AI Confusion Risk |
|---|------|-------|------|----------------|-------------------|
| 1 | `project/how-to-write-avax.md` | 339 | 14 | **PROJECT_SPECIFIC** — correct, this is the overlay | None (correctly placed) |
| 2 | `project/how-to-git.md` | 260 | 5 | **PROJECT_SPECIFIC** — AvaX git conventions | Low |
| 3 | `architecture/how-to-engineering-laws.md` | 463 | 1 | **GENERIC** — engineering heuristics, 1 stray AvaX ref | Low |
| 4 | `architecture/how-to-architecture-decisions.md` | 111 | 4 | **MIXED** — ADR format is generic, 4 AvaX references | Medium |
| 5 | `architecture/how-to-runtime-composition.md` | 1183 | 6 | **MIXED** — runtime composition concepts are generic, hardcodes PublicSurface/Flows | Medium |
| 6 | `architecture/how-to-use-ai-assisted-execution.md` | 1315 | 13 | **MIXED** — prompt engineering is generic, hardcodes AvaX | Medium |
| 7 | `verification/how-to-code-review.md` | 2100 | 3 | **GENERIC** — review process is universal, 3 AvaX refs | Low |
| 8 | `verification/how-to-test-risk-based-behavioral-testing.md` | 319 | 2 | **GENERIC** — testing strategy is universal | Low |
| 9 | `verification/how-to-unit-test.md` | 3317 | 13 | **MIXED** — test philosophy generic, PublicSurface/Flows path hardcoding | Medium |
| 10 | `verification/how-to-create-ai-code-review-packs.md` | 559 | 0 | **GENERIC** — review pack creation, no AvaX refs | None |
| 11 | `verification/how-to-production-readiness.md` | 1813 | 13 | **MIXED** — production readiness concepts generic, PublicSurface/Capabilities path hardcoding | Medium |
| 12 | `verification/how-to-system-security.md` | 2713 | 9 | **MIXED** — security rules mostly generic, PublicSurface/Flows path hardcoding | Medium |
| 13 | `verification/how-to-system-performance.md` | 2243 | 10 | **MIXED** — performance rules mostly generic, Capabilities path hardcoding | Medium |
| 14 | `verification/how-to-data-systems.md` | 86 | 1 | **GENERIC** — data system rules, 1 stray AvaX ref | Low |
| 15 | `implementation/how-to-clean-code.md` | 1782 | 4 | **GENERIC** — clean code philosophy is universal | Low |
| 16 | `implementation/how-to-code-style.md` | 443 | 1 | **GENERIC** — code style is universal, 1 stray AvaX ref | Low |
| 17 | `implementation/how-to-coding-standards.md` | 1556 | 2 | **GENERIC** — coding standards are universal | Low |
| 18 | `implementation/how-to-dependency-injection.md` | 1859 | 10 | **MIXED** — DI rules mostly generic, hardcodes AvaX DI specifics | Low |
| 19 | `implementation/how-to-modern-php-attributes-di.md` | 790 | 8 | **MIXED** — PHP DI concepts generic, AvaX-specific DI patterns | Low |
| 20 | `components/how-to-design-components.md` | 3501 | 38 | **MIXED (heavy)** — component design universal, hardcodes Flows/Capabilities/PublicSurface as literal paths | High |
| 21 | `components/how-to-dogfooding.md` | 973 | 20 | **MIXED (heavy)** — dogfooding concept universal, deeply AvaX-branded | High |
| 22 | `modeling/how-to-model-flows.md` | 1276 | 17 | **MIXED (heavy)** — flow modeling universal, hardcodes Flows/PublicSurface | High |
| 23 | `architecture/how-to-architecture.md` | 3223 | 21 | **MIXED (heavy)** — architecture rules universal, deeply AvaX-integrated | High |
| 24 | `architecture/how-to-architecture-extension-with-ddd.md` | 2709 | 9 | **MIXED** — DDD extension concepts generic, 107 PublicSurface/Flows references | High |
| 25 | `architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md` | 1375 | 18 | **MIXED** — events/CQRS concepts universal, 18 AvaX refs | High |
| 26 | `architecture/how-to-use-advanced-architecture-patterns.md` | 2067 | 6 | **MIXED** — advanced patterns universal, 54 PublicSurface/Flows refs | Medium |
| 27 | `documentation/how-to-document.md` | 1098 | 8 | **MIXED** — documentation rules generic, 8 AvaX refs (partially fixed — two-layer model) | Low |
| 28 | `documentation/how-to-write-self-explaining-architecture.md` | 685 | 3 | **GENERIC** — self-explaining concept is universal | Low |
| 29 | `README.md` | 104 | 2 | **GENERIC** — folder map | None |
| 30 | `00-reading-order.md` | 104 | 2 | **GENERIC** — reading order | None |

### Skills Files

| # | File | AvaX | Classification | Notes |
|---|------|------|----------------|-------|
| 1 | `avax-enterprise-remediation/SKILL.md` | 16 | **PROJECT_SPECIFIC** — named avax-*, intended as project skill | Correct |
| 2 | `avax-enterprise-codecraft/SKILL.md` | 10 | **PROJECT_SPECIFIC** — named avax-*, intended as project skill | Correct |
| 3 | `avax-component-dogfooding/SKILL.md` | 23 | **PROJECT_SPECIFIC** — named avax-*, intended as project skill | Correct |
| 4 | `avax-runtime-performance-cache/SKILL.md` | 11 | **PROJECT_SPECIFIC** | Correct |
| 5 | `avax-autonomous-backlog-loop/SKILL.md` | 8 | **PROJECT_SPECIFIC** | Correct |
| 6 | `avax-api-compatibility-contract/SKILL.md` | 8 | **PROJECT_SPECIFIC** | Correct |
| 7 | `avax-observability-failure-semantics/SKILL.md` | 7 | **PROJECT_SPECIFIC** | Correct |
| 8 | `avax-test-evidence-quality/SKILL.md` | 6 | **PROJECT_SPECIFIC** | Correct |
| 9 | `avax-security-threat-model/SKILL.md` | 6 | **PROJECT_SPECIFIC** | Correct |
| 10 | `avax-source-of-truth-resolver/SKILL.md` | 2 | **PROJECT_SPECIFIC** | Correct |
| 11 | `self-explaining-architecture/SKILL.md` | 4 | **GENERIC** — reusable outside AvaX | Low risk |
| 12 | `review/SKILL.md` | 0 | **GENERIC** | None |
| 13 | `testing/SKILL.md` | 0 | **GENERIC** | None |
| 14 | `security/SKILL.md` | 0 | **GENERIC** | None |
| 15 | `performance/SKILL.md` | 0 | **GENERIC** | None |
| 16 | `recovery/SKILL.md` | 1 | **GENERIC** | Low |
| 17 | `refactor/SKILL.md` | 0 | **GENERIC** | None |
| 18 | `validation/SKILL.md` | 1 | **GENERIC** | Low |

### Summary

| Classification | Count |
|----------------|-------|
| **GENERIC** | 12 how-to + 8 skills = 20 |
| **MIXED** (after refactor, path hardcoding remains) | 16 how-to |
| **PROJECT_SPECIFIC** | 2 how-to + 10 skills = 12 |

### Refactoring Priority (as classified)

**P1 — Quick fixes (low AvaX count, mostly generic):**
| File | Before | After | Status |
|------|--------|-------|--------|
| how-to-engineering-laws.md | 1 AvaX | 0 | COMPLETE |
| how-to-code-style.md | 1 AvaX | 0 | COMPLETE |
| how-to-code-review.md | 3 AvaX | 1 (`avax.txt` file ref) | COMPLETE |
| how-to-clean-code.md | 4 AvaX | 0 | COMPLETE |
| how-to-architecture-decisions.md | 4 AvaX | 0 | COMPLETE |
| how-to-test-risk-based-behavioral-testing.md | 2 AvaX | 0 | COMPLETE |
| how-to-data-systems.md | 1 AvaX | 0 | COMPLETE |
| how-to-write-self-explaining-architecture.md | 3 AvaX | 0 | COMPLETE |
| how-to-document.md | 8 AvaX | 0 | COMPLETE |

**P2 — Moderate refactor (mixed, medium AvaX count):**
| File | Before | After | Status |
|------|--------|-------|--------|
| how-to-dependency-injection.md | 10 AvaX | 3 (DI-specific, minimal) | COMPLETE |
| how-to-modern-php-attributes-di.md | 8 AvaX | 4 (path refs remain) | COMPLETE |
| how-to-unit-test.md | 13 AvaX | 2 (test-layer names remain) | COMPLETE |
| how-to-production-readiness.md | 13 AvaX | 5 (path refs remain) | COMPLETE |
| how-to-runtime-composition.md | 6 AvaX | 2 (path refs remain) | COMPLETE |

**P3 — Heavy refactor (deeply mixed, high AvaX count):**
| File | Before | After | Status |
|------|--------|-------|--------|
| how-to-architecture.md | 21 AvaX | ~3 (path refs remain) | COMPLETE |
| how-to-design-components.md | 38 AvaX | ~5 (path refs remain) | COMPLETE |
| how-to-dogfooding.md | 20 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-model-flows.md | 17 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-system-security.md | 9 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-system-performance.md | 10 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-architecture-extension-with-ddd.md | 9 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-events-listeners-event-sourcing-cqrs-realtime.md | 18 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-use-advanced-architecture-patterns.md | 6 AvaX | ~2 (path refs remain) | COMPLETE |
| how-to-use-ai-assisted-execution.md | 13 AvaX | ~2 (path refs remain) | COMPLETE |

**Total: ~260 AvaX refs reduced to ~35 path-only references across all how-to files.**
