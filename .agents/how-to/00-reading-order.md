# Governance Reading Order

## Mandatory Preflight

1. AGENTS.md
2. .agents/how-to/README.md
3. .agents/how-to/00-reading-order.md

## Core Architecture

4. .agents/how-to/architecture/how-to-architecture.md
5. .agents/how-to/architecture/how-to-architecture-decisions.md
6. .agents/how-to/architecture/how-to-architecture-extension-with-ddd.md
7. .agents/how-to/architecture/how-to-runtime-composition.md
8. .agents/how-to/architecture/how-to-events-listeners-event-sourcing-cqrs-realtime.md
9. .agents/how-to/architecture/how-to-use-advanced-architecture-patterns.md
10. .agents/how-to/architecture/how-to-use-ai-assisted-execution.md

## Modeling Before Code

11. .agents/how-to/modeling/how-to-model-flows.md — MISSING
12. .agents/how-to/modeling/how-to-domain-discovery.md — MISSING
13. .agents/how-to/modeling/how-to-scenario-input.md — MISSING

## Component Design

14. .agents/how-to/components/how-to-design-components.md
15. .agents/how-to/components/how-to-dogfooding.md

## Implementation

16. .agents/how-to/implementation/how-to-clean-code.md
17. .agents/how-to/implementation/how-to-code-style.md
18. .agents/how-to/implementation/how-to-coding-standards.md
19. .agents/how-to/implementation/how-to-modern-php-attributes-di.md
20. .agents/how-to/implementation/how-to-dependency-injection.md

## Verification

21. .agents/how-to/verification/how-to-code-review.md
22. .agents/how-to/verification/how-to-unit-test.md
23. .agents/how-to/verification/how-to-production-readiness.md
24. .agents/how-to/verification/how-to-system-security.md
25. .agents/how-to/verification/how-to-system-performance.md
26. .agents/how-to/verification/how-to-data-systems.md

## Documentation

27. .agents/how-to/documentation/how-to-document.md

## Project Local

28. .agents/how-to/project/how-to-write-avax.md
29. .agents/how-to/project/how-to-git.md

## Rules

- If a listed file does not exist, the agent must report it as MISSING instead of silently skipping it.
- If the task touches a specific domain, the agent must read the relevant specific document even if it appears late in the order.
- If a local AGENTS.md or project contract is stricter, the stricter rule wins.
- For AvaX work, project-local governance must be read before implementation.
- The modeling/ folder is currently empty; these documents are planned but not yet created.
