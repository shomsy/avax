# Identity Target Architecture Source Of Truth Decision

Date: 2026-05-21

## Sources Classified Current

- Current git state on `architecture/identity-target-architecture`.
- User request: implement the root `refactor-identity.md` plan and record open discussion notes.
- `refactor-identity.md`: active task contract for Identity redesign.
- Root `CURRENT_TRUTH.md`: current remediation baseline through 2026-05-20.
- Root `TODO.md` and `fix-this.md`: completed TODO-007 remains completed; Identity PublicSurface pressure remains relevant as architecture debt.

## Sources Classified Stale Or Advisory

- `.agents/management/CURRENT_TRUTH.md`: dated 2026-05-01 and contradicted by newer root truth.
- `EVIDENCE/EXECUTION.md`: contains older stage lock language and AuthBuilder blocker references contradicted by later root truth and current branch state.
- `.agents/management/ACTIVE.md` / `.agents/management/TODO.md`: still name V5.9 AuthBuilder first slice as ready, but root TODO/fix-this classify TODO-007 as DONE. Advisory only for historical context.
- Older identity-world-class evidence: useful as previous implementation notes, not proof that current branch is already complete.

## Contradictions

- `refactor-identity.md` target tree includes `System/Runtime/`; AGENTS.md canonical component shape does not allow that folder by default.
- Prior evidence claims unified DSL was completed, but current `components/Identity/System/PublicSurface/Identity.php` still directly constructs collaborators.
- `IdentityConfig.php` is deleted while `IdentityServiceProvider.php` still registers `IdentityConfig`.

## Resolution Applied

- AGENTS.md and current git state win over older evidence and management files.
- Implement the plan as a bounded slice without creating forbidden top-level folders.
- Preserve the public static root DSL for compatibility while moving object graph assembly out of PublicSurface into Configuration/Builders.

## Final Truth Baseline

This slice owns:

- root Identity DSL delegation
- default root Identity assembly in `System/Configuration/Builders`
- provider registration of `IdentityConfiguration`
- focused DSL characterization tests
- evidence and validation notes

This slice does not claim full Identity redesign completion.
