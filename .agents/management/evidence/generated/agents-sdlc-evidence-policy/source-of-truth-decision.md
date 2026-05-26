# Source Of Truth Decision

Date: 2026-05-26
Branch: architecture/identity-runtime-convergence
Task: Make complete `.agents` SDLC loading and evidence location policy explicit.

## Decision

- Every task must load the complete `.agents` SDLC before routing:
  - `AGENTS.md`
  - `.agents/GOVERNANCE_INDEX.md`
  - `.agents/how-to/README.md`
  - `.agents/how-to/00-how-to-reading-order.md`
  - all `.agents/how-to/**/*.md`
  - all `.agents/skills/**/SKILL.md`
  - `.agents/management/**`
  - `ARCHITECTURE.md`
- New agent evidence belongs under `.agents/management/evidence/**`.
- Root `EVIDENCE/` is legacy/transitional and must not receive new task evidence by default.
- If legacy tooling or explicit human instruction requires a root `EVIDENCE/` file, its filename must use `YYYY-MM-DD-HH-MM-SS-descriptive-name.md`.

## Scope

This pass updated routing and evidence-location rules only.
No production code or Identity implementation was changed.
