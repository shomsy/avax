# V3 Hardening — Phase 4: Architecture Neutrality

**Date**: 2026-05-16

## Architecture Rule Audit

| Rule | Universal? | Profile-specific? | Action |
|---|---|---|---|
| Architecture must be explicit in AGENTS.md | YES | NO | Kept in core |
| Source of truth must be clear | YES | NO | Kept in core |
| Boundaries must be documented | YES | NO | Kept in core |
| Duplicate truth is forbidden | YES | NO | Kept in core |
| Local AGENTS must declare architecture | YES | NO | Kept in core |
| Vertical slice / feature-first organization | NO | YES | Moved to `profiles/architecture/vertical-slice.md` |
| Horizontal layered architecture | NO | YES | Moved to `profiles/architecture/layered.md` |
| Feature-first naming | NO | YES | Moved to `profiles/architecture/feature-first.md` |
| Package/library boundaries | NO | YES | Moved to `profiles/architecture/package-library.md` |

## Finding: Parent Architecture Standard Was Already Neutral
- `.agents/governance/architecture/ARCHITECTURE.md` is explicitly a template
- It already says "do not specialize this file to any one project"
- The opinionated rules were in previous PHP-era AvaX governance, not in parent
- Parent core does not force vertical slice globally ✅

## New Architecture Profiles Created
- `.agents/governance/profiles/architecture/vertical-slice.md`
- `.agents/governance/profiles/architecture/layered.md`
- `.agents/governance/profiles/architecture/feature-first.md`
- `.agents/governance/profiles/architecture/package-library.md`

## Acceptance Criteria
- [x] Parent does not force one architecture style globally
- [x] Projects must explicitly select or define architecture
- [x] Architecture profiles are optional overlays
