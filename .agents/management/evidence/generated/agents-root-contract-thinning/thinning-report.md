# AGENTS.md Thinning Report

Date: 2026-05-26

## What Changed

- Replaced the 2429-line AGENTS.md with a 651-line thin execution contract.
- Preserved root-level authority for:
  - AvaX goal
  - hard stop laws
  - deviation audit
  - canonical severity names
  - no suppression rule
  - GREEN justification
  - drift classification
  - precedence
  - source-of-truth order
  - execution modes
  - skill routing
  - preflight
  - branch/worktree policy
  - validation/evidence/output contracts
- Removed detailed encyclopedia content from root:
  - architecture manifesto details
  - component shape detail
  - PublicSurface detail
  - DI detail
  - dogfooding detail
  - risk-based testing detail
  - security detail
  - performance/cache detail
  - detailed SDLC rules
- Replaced removed details with links to canonical `.agents/how-to/**` documents.

## Supporting Updates

- Updated `.agents/how-to/project/how-to-write-avax.md` so it no longer points to removed AGENTS.md sections for component shape or runtime hot path details.
- Updated `.agents/how-to/components/how-to-design-components.md` to clarify:
  - `System/Docs/` is forbidden as a generic bucket.
  - `components/<Area>/<Component>/docs/` is allowed for local self-explaining architecture.
- Updated risk-based testing cross-references to point at `.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md`.
- Updated review-pack generation text that referenced removed `AGENTS.md §24A`.

## Scope Not Changed

- No production code was changed.
- No Identity implementation was started.
- Legacy full-mode gate debt was not remediated in this pass.
