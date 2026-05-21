# Practical Test Pyramid Governance — Evidence Summary

## Mission

Add AvaX Practical Test Pyramid governance.

Source: https://martinfowler.com/articles/practical-test-pyramid.html

Scope: governance/docs/skills only. No production PHP. No Identity refactor.

## Extracted Principles (15 Rules)

1. **Test Portfolio** — many fast focused, fewer broad, minimal E2E
2. **Fast Feedback Pipeline** — ordered by speed/scope, not test label
3. **Behavior Over Implementation** — verify observable behavior, not internals
4. **Private Method Smell** — extract, don't use reflection hacks
5. **Sociable vs Solitary** — real collaborators where fast, doubles where slow
6. **Test Double Precision** — fake/stub/mock/spy used intentionally
7. **Contract Test** — required for public surfaces and boundaries
8. **Integration Test** — separate, fewer, deterministic
9. **E2E Minimalism** — critical journeys only, no edge case duplication
10. **Acceptance Test** — user/system perspective, ubiquitous language
11. **Exploratory Testing** — automation doesn't replace exploration
12. **Clean Test Code** — production-grade, one behavior, Arrange/Act/Assert
13. **Avoid Test Duplication** — details low, contracts at boundaries, journeys high
14. **Refactor Safety Net** — characterization tests before large changes
15. **Test Naming Consistency** — consistent names across docs, folders, evidence

## AvaX Translation

All concepts translated into AvaX terms:

- Unit/Behavior tests — base of pyramid, fast, focused, one behavior per test
- Component tests — component with real internal collaborators
- Integration tests — database, filesystem, network, runtime, external
- Contract tests — PublicSurface APIs, component boundaries, events, metadata
- Architecture/Governance tests — canonical shape, naming, dependency direction
- Acceptance tests — feature/business behavior, user/system perspective
- E2E/Canonical journeys — critical journeys only, minimal

## Canonical Rule Location

- `how-to-unit-test.md` — Section 91: Practical Test Pyramid Rule
  - 16 sub-sections (91.1 through 91.16)
  - Covers all 15 extracted rules + GREEN/YELLOW/RED criteria

## Cross-References

### How-To Files

- `how-to-code-review.md` — Section 26: Practical Test Pyramid Review Rule
  - 11-item review checklist for test portfolio quality
  - Portfolio, fast feedback, behavior vs implementation, private method smell, sociable vs solitary, test doubles, contracts, E2E minimalism, clean test code, duplication, refactor safety

- `how-to-production-readiness.md` — Section 27.1: Practical Test Pyramid Cross-Reference
  - Production readiness requires healthy test portfolio, not just passing count
  - "A system with 8000 passing tests but all at one pyramid layer is not production-ready"

- `how-to-dogfooding.md` — Section 28.2: Practical Test Pyramid Cross-Reference
  - Component dogfooding requires contract tests at boundaries
  - "A component that is dogfooded without contract tests is a ticking integration bomb"

### Skills

- `avax-test-evidence-quality/SKILL.md` — Practical Test Pyramid Rule
  - Agents must distribute tests across layers, verify behavior, use real collaborators, write contract tests, keep test code production-grade, avoid duplication, run fast first, require characterization tests

- `avax-enterprise-codecraft/SKILL.md` — Practical Test Pyramid Gate
  - Production code must be testable at the right pyramid layer
  - Evaluation: behavior testable, right layer, no over-mocking, contract coverage

- `avax-api-compatibility-contract/SKILL.md` — Practical Test Pyramid Rule
  - Public API changes require contract tests
  - Consumer expectations executable, breaking contracts fail fast

- `avax-autonomous-backlog-loop/SKILL.md` — Practical Test Pyramid in Autonomous Loops
  - Focused tests first, then component, contract, integration, broad, E2E last
  - Never run E2E before focused tests

## Dictionary Terms (15 new)

- Test Pyramid — granularity principle, not layer dogma
- Unit Test — fast, focused, one observable behavior
- Component Test — component with real internal collaborators
- Integration Test — external systems, separate from unit
- Contract Test — consumer expectations, executable
- Acceptance Test — user/system perspective
- End-to-End Test — critical journeys only, expensive/flaky
- Test Double — umbrella term for fake/stub/mock/spy
- Fake — working implementation for tests, needs contract test
- Stub — pre-programmed responses, no call assertion
- Mock — pre-programmed expectations, verifies calls
- Spy — records calls for later verification
- Characterization Test — safety net before refactor
- Fast Feedback — pipeline ordered by speed/scope
- Flaky Test — unreliable test, must fix/delete/quarantine

## Files Updated (9)

1. `how-to-unit-test.md` — Section 91 (16 sub-sections, canonical)
2. `how-to-code-review.md` — Section 26 (11-item review checklist)
3. `how-to-production-readiness.md` — Section 27.1
4. `how-to-dogfooding.md` — Section 28.2
5. `avax-test-evidence-quality/SKILL.md` — Test Pyramid Rule
6. `avax-enterprise-codecraft/SKILL.md` — Test Pyramid Gate
7. `avax-api-compatibility-contract/SKILL.md` — Test Pyramid Rule
8. `avax-autonomous-backlog-loop/SKILL.md` — Test Pyramid in Loops
9. `dictionary/framework-terms.md` — CREATED (15 terms)

## Validation

```bash
composer validate --no-check-publish  # PASS
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-root-evidence-hygiene.php
git diff --check
```

## Final Decision

Scope: governance/docs/skills only. Zero production PHP changed.

All 15 principles from the Practical Test Pyramid article extracted and translated into AvaX governance.

Canonical rule placed in how-to-unit-test.md Section 91.
Cross-references in code-review, production-readiness, dogfooding.
4 skills updated.
Dictionary created with 15 terms.
Evidence summary created.

Status: GREEN — ready for commit.
