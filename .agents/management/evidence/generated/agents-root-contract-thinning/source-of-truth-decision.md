# Source Of Truth Decision

Date: 2026-05-26
Branch: architecture/identity-runtime-convergence
Task: Thin `AGENTS.md` into a root constitution and router.

## Sources Loaded

| Source | Status | Decision |
|---|---|---|
| `AGENTS.md` | Loaded and rewritten | Root contract should define stop conditions, routing, evidence, validation, and reporting. |
| `.agents/how-to/README.md` | Loaded | Defines categorized how-to governance layout. |
| `.agents/how-to/00-how-to-reading-order.md` | Loaded | Canonical deterministic loading order. |
| `.agents/how-to/project/how-to-write-avax.md` | Loaded and updated | Owns AvaX-specific architecture and execution overlay. |
| `.agents/how-to/components/how-to-design-components.md` | Loaded and updated | Owns component shape and local docs clarification. |
| `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md` | Loaded and updated | Owns risk-based behavioral testing details previously duplicated in AGENTS.md. |
| `.agents/how-to/verification/how-to-unit-test.md` | Loaded and updated | Cross-references risk-based testing owner. |

## Final Source Of Truth

- `AGENTS.md` is the root execution contract and router.
- Detailed SDLC rules are owned by categorized `.agents/how-to/**` files and task-relevant `.agents/skills/**`.
- AvaX-specific architecture detail is owned by `.agents/how-to/project/how-to-write-avax.md` and `ARCHITECTURE.md`.
- Risk-based behavioral testing details are owned by `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md`.
- Local component docs are allowed under `components/<Area>/<Component>/docs/`.
- `System/Docs/` remains forbidden as a generic technical bucket.

## Conflict Resolution

The old AGENTS.md sections that duplicated project and SDLC details were removed from the root file. Active references were updated to point at canonical how-to files.
