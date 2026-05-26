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

### Core Architecture

4. `.agents/how-to/architecture/how-to-architecture.md`
5. `.agents/how-to/architecture/how-to-architecture-decisions.md`
6. `.agents/how-to/architecture/how-to-architecture-extension-with-ddd.md`
7. `.agents/how-to/architecture/how-to-runtime-composition.md`
8. `.agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md`
9. `.agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md`
10. `.agents/how-to/architecture/how-to-use-ai-assisted-execution.md`
11. `.agents/how-to/architecture/how-to-engineering-laws.md`

### Modeling Before Code

12. `.agents/how-to/modeling/how-to-model-flows.md`

### Component Design

13. `.agents/how-to/components/how-to-design-components.md`
14. `.agents/how-to/components/how-to-dogfooding.md`

### Implementation

15. `.agents/how-to/implementation/how-to-clean-code.md`
16. `.agents/how-to/implementation/how-to-code-style.md`
17. `.agents/how-to/implementation/how-to-coding-standards.md`
18. `.agents/how-to/implementation/how-to-modern-php-attributes-di.md`
19. `.agents/how-to/implementation/how-to-dependency-injection.md`

### Verification

20. `.agents/how-to/verification/how-to-code-review.md`
21. `.agents/how-to/verification/how-to-create-ai-code-review-packs.md`
22. `.agents/how-to/verification/how-to-unit-test.md`
23. `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md`
24. `.agents/how-to/verification/how-to-production-readiness.md`
25. `.agents/how-to/verification/how-to-system-security.md`
26. `.agents/how-to/verification/how-to-system-performance.md`
27. `.agents/how-to/verification/how-to-data-systems.md`

### Documentation

28. `.agents/how-to/documentation/how-to-document.md`
29. `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`

## Project Overlay Governance

30. `.agents/how-to/project/how-to-write-avax.md`
31. `.agents/how-to/project/how-to-git.md`

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
.agents/how-to/modeling/how-to-domain-discovery.md
.agents/how-to/modeling/how-to-scenario-input.md
```


## Rules

- If a listed file does not exist, the agent must report it as MISSING instead of silently skipping it.
- If the task touches a specific domain, the agent must read the relevant specific document even if it appears late in the order.
- If a local AGENTS.md or project contract is stricter, the stricter rule wins.
- Project overlay governance extends generic governance. Load both for project-specific work.
- For project-specific work, project-local governance must be read before implementation.
- The modeling/ folder contains `how-to-model-flows.md`; planned modeling docs are listed only under "Planned, Not Required".
- `how-to.txt` is retired as a repository file. If tooling recreates it locally, it is ignored and must not be loaded as governance.
