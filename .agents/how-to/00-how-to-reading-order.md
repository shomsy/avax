# Governance Reading Order

## Canonical Truth

This reading order is CANONICAL.

Every governance document in `.agents/how-to/` appears exactly once.
No duplicates exist. No shadow governance exists outside categorized subfolders.
Every document in this order is loaded when an agent runs recursive governance load.

## Governance Layering

Governance is layered. See `.agents/management/evidence/generated/governance-generalization/governance-layering-model.md` for the full model.

```text
Layer 1: Generic engineering governance (reusable rules)
Layer 2: Project-specific governance overlays
Layer 3: Local component documentation
Layer 4: Evidence and generated artifacts
```

The reading order below loads Layer 1 and Layer 2 documents in priority order. Layer 2 (project/) extends Layer 1 with project-specific rules. Layer 2 wins for project-specific matters; Layer 1 wins for universal principles.

### Why Duplicate Governance Is Forbidden

Duplicate governance documents create:

- **Conflicting truths:** Two copies of the same rule inevitably drift. AI agents load both and cannot determine which is canonical.
- **Split context:** When an update changes one copy but not the other, the system has both old and new rules active.
- **Validation bypass:** A checker that validates one path misses the duplicate.
- **AI confusion:** AI agents lose deterministic behavior when two sources claim authority over the same rule.

### Why AI Agents Require Deterministic Loading

AI agents lose context between sessions. Each task starts fresh. Without a deterministic loading order, an AI agent may:

- Load governance in different order and interpret precedence differently
- Miss critical rules because they live outside the expected path
- Load duplicate rules and spend context budget resolving contradictions
- Violate rules it never saw because loading order was ambiguous

### Conflict Resolution

If two governance documents disagree:

1. The document appearing EARLIER in this reading order wins
2. AGENTS.md always wins over any how-to document
3. If the same rule appears in two documents, the categorized canonical copy wins over any root-level shadow
4. If resolution is impossible, the agent must report a HARD_BLOCKER

---

## Mandatory Preflight

These are loaded before any task execution:

1. `AGENTS.md` — root project contract
2. `.agents/how-to/README.md` — governance map and structure
3. `.agents/how-to/00-how-to-reading-order.md` — this file, canonical loading order

Generated evidence files are NOT mandatory preflight. They are loaded on demand when the task requires them.

## Canonical vs Support Material

```text
Canonical governance (mandatory, must exist):
  AGENTS.md
  .agents/GOVERNANCE_INDEX.md
  ARCHITECTURE.md
  .agents/how-to/README.md
  .agents/how-to/00-how-to-reading-order.md
  .agents/how-to/**/*.md (generic + project overlay)

Project overlay:
  .agents/how-to/project/*.md — extends generic governance for this project

Local component documentation:
  components/<Name>/docs/ — loaded by task scope, NOT globally

Generated evidence:
  .agents/management/evidence/generated/** — proves status, NOT governance
  Evidence must not be treated as canonical governance unless explicitly promoted

Review packs:
  _pack/** — focused context for AI review upload, NOT governance

Examples:
  docs/examples/** — illustrative, NOT authoritative
```

## Generic Governance

## Canonical Support Material

`.agents/knowledge/**` explains source principles and traceability. It is CANONICAL_SUPPORT, not binding governance by itself.

<!-- ENGINEERING_CANON_CONVERGENCE_START -->
- `.agents/knowledge/README.md`
- `.agents/knowledge/engineering-canon.md`
- `.agents/knowledge/book-to-rule-traceability.md`
- `.agents/knowledge/source-principles/README.md`
- `.agents/knowledge/source-principles/software-architecture-hard-parts.md`
- `.agents/knowledge/source-principles/designing-data-intensive-applications.md`
- `.agents/knowledge/source-principles/clean-code-code-complete.md`
- `.agents/knowledge/source-principles/pragmatic-programmer.md`
- `.agents/knowledge/source-principles/domain-driven-design.md`
- `.agents/knowledge/source-principles/use-cases-domain-storytelling.md`
- `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md`
<!-- ENGINEERING_CANON_CONVERGENCE_END -->

### Source Principle Loading By Task

- Architecture decisions: `.agents/knowledge/source-principles/software-architecture-hard-parts.md`
- Data/state: `.agents/knowledge/source-principles/designing-data-intensive-applications.md`
- Implementation/refactor/tests: `.agents/knowledge/source-principles/clean-code-code-complete.md`
- Automation/tracer/evidence: `.agents/knowledge/source-principles/pragmatic-programmer.md`
- Domain modeling: `.agents/knowledge/source-principles/domain-driven-design.md`
- Scenario/use case: `.agents/knowledge/source-principles/use-cases-domain-storytelling.md`
- Antipattern/pattern/refactor: `.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md`

### Core Architecture

4. `.agents/how-to/architecture/how-to-architecture.md`
5. `.agents/how-to/architecture/how-to-architecture-decisions.md`
<!-- ENGINEERING_CANON_OPERATIONAL_HOWTO_START -->
6. `.agents/how-to/architecture/how-to-coupling-governance.md`
7. `.agents/how-to/architecture/how-to-architecture-fitness-functions.md`
8. `.agents/how-to/architecture/how-to-enterprise-application-patterns.md`
9. `.agents/how-to/architecture/how-to-data-correctness.md`
10. `.agents/how-to/architecture/how-to-adr-tradeoff-governance.md`
<!-- ENGINEERING_CANON_OPERATIONAL_HOWTO_END -->
11. `.agents/how-to/architecture/how-to-architecture-extension-with-ddd.md`
12. `.agents/how-to/architecture/how-to-runtime-composition.md`
13. `.agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md`
14. `.agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md`
15. `.agents/how-to/architecture/how-to-use-ai-assisted-execution.md`
16. `.agents/how-to/architecture/how-to-engineering-laws.md`

### Modeling Before Code

17. `.agents/how-to/modeling/how-to-model-flows.md`
18. `.agents/how-to/modeling/how-to-scenario-input.md`
19. `.agents/how-to/modeling/how-to-domain-discovery.md`

### Component Design

20. `.agents/how-to/components/how-to-design-components.md`
21. `.agents/how-to/components/how-to-dogfooding.md`

### Implementation

22. `.agents/how-to/implementation/how-to-clean-code.md`
23. `.agents/how-to/implementation/how-to-code-style.md`
24. `.agents/how-to/implementation/how-to-coding-standards.md`
25. `.agents/how-to/implementation/how-to-modern-php-attributes-di.md`
26. `.agents/how-to/implementation/how-to-dependency-injection.md`
<!-- ENGINEERING_CANON_CONSTRUCTION_HOWTO_START -->
27. `.agents/how-to/implementation/how-to-software-construction.md`
28. `.agents/how-to/implementation/how-to-refactoring.md`
29. `.agents/how-to/implementation/how-to-design-patterns.md`
30. `.agents/how-to/runtime/how-to-concurrency-runtime-safety.md`
<!-- ENGINEERING_CANON_CONSTRUCTION_HOWTO_END -->

### Verification

31. `.agents/how-to/verification/how-to-code-review.md`
32. `.agents/how-to/verification/how-to-create-ai-code-review-packs.md`
33. `.agents/how-to/verification/how-to-unit-test.md`
34. `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md`
35. `.agents/how-to/verification/how-to-production-readiness.md`
36. `.agents/how-to/verification/how-to-system-security.md`
37. `.agents/how-to/verification/how-to-system-performance.md`
38. `.agents/how-to/verification/how-to-data-systems.md`
39. `.agents/how-to/verification/how-to-antipattern-detection.md`
<!-- ENGINEERING_CANON_SDLC_AUTOMATION_START -->
40. `.agents/how-to/verification/how-to-sdlc-automation.md`
<!-- ENGINEERING_CANON_SDLC_AUTOMATION_END -->
<!-- ENGINEERING_CANON_SDLC_RUNNERS_START -->
41. `.agents/how-to/verification/how-to-sdlc-runners.md`
<!-- ENGINEERING_CANON_SDLC_RUNNERS_END -->

### Documentation

42. `.agents/how-to/documentation/how-to-document.md`
43. `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`

## Project Overlay Governance

44. `.agents/how-to/project/how-to-write-avax.md`
45. `.agents/how-to/project/how-to-git.md`

## Executable Engineering Canon Checkers

<!-- ENGINEERING_CANON_CHECKERS_START -->
- `tooling/governance/check-engineering-canon-traceability.php`
- `tooling/governance/check-scenario-input.php`
- `tooling/governance/check-coupling-decisions.php`
- `tooling/governance/check-architecture-fitness-functions.php`
- `tooling/governance/check-antipatterns.php`
- `tooling/governance/check-data-correctness-evidence.php`
- `tooling/governance/check-enterprise-application-boundaries.php`
- `tooling/governance/check-adr-tradeoff-evidence.php`
- `tooling/governance/check-runtime-concurrency-safety.php`
- `tooling/governance/check-refactoring-safety.php`
- `tooling/governance/check-construction-checklist.php`
- `tooling/governance/create-actual-changes-review-pack.php`
<!-- ENGINEERING_CANON_CHECKERS_END -->

## AntiPattern Dictionary Support

- `.agents/dictionary/antipatterns/analysis-paralysis.md`
- `.agents/dictionary/antipatterns/architecture-theater.md`
- `.agents/dictionary/antipatterns/blob-god-object.md`
- `.agents/dictionary/antipatterns/cut-and-paste-programming.md`
- `.agents/dictionary/antipatterns/fake-abstraction.md`
- `.agents/dictionary/antipatterns/generic-bucket.md`
- `.agents/dictionary/antipatterns/golden-hammer.md`
- `.agents/dictionary/antipatterns/service-locator.md`
- `.agents/dictionary/antipatterns/shallow-tests.md`
- `.agents/dictionary/antipatterns/spaghetti-code.md`
- `.agents/dictionary/antipatterns/stovepipe-system.md`

## Task-Scoped Local Docs

Local component or bounded-context docs are discovered by task scope, not mandatory global preflight.

```text
components/<Area>/<Component>/docs/
<bounded-context>/docs/
```

Load these only when the task touches the matching component, flow, capability, or bounded context.

## Generated Evidence

Generated evidence proves what happened. It is not canonical governance unless explicitly promoted by a current governance decision.

```text
.agents/management/evidence/generated/**
_pack/**
```

Root `EVIDENCE/**` is legacy/transitional. Load it only when current evidence, legacy tooling, or explicit human instruction activates it. New root `EVIDENCE/` files must use `YYYY-MM-DD-HH-MM-SS-descriptive-name.md`.

Generated evidence must not be used as mandatory canonical preflight.

## Planned, Not Required

These documents may be created in a future pass. Their absence is not a governance failure.

```text
No planned mandatory governance documents are active in this section.
```


## Rules

- If a listed file does not exist, the agent must report it as MISSING instead of silently skipping it.
- If the task touches a specific domain, the agent must read the relevant specific document even if it appears late in the order.
- If a local AGENTS.md or project contract is stricter, the stricter rule wins.
- Project overlay governance extends generic governance. Load both for project-specific work.
- For project-specific work, project-local governance must be read before implementation.
- The modeling/ folder contains `how-to-model-flows.md`; planned modeling docs are listed only under "Planned, Not Required".
- `how-to.txt` is retired as a repository file. If tooling recreates it locally, it is ignored and must not be loaded as governance.
