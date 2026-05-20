# TODO-006 Implementation Summary

## Task

- TODO: `TODO-006`
- Title: Move framework public entrypoint object-graph assembly out of runtime/PublicSurface
- Slice: A - move default `RunApplication` dispatch pipeline assembly into Configuration
- Status: PARTIAL_WITH_YELLOW
- Current branch HEAD before Slice A commit: `683e6fb250ac`

## Source Of Truth

Authoritative for this slice:

- current git state
- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- task-relevant `.agents/skills/**/SKILL.md`
- task-relevant `.agents/how-to/*.md`
- `fix-this.md` TODO-006 section
- `TODO.md` TODO-006 section
- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/current-truth.md`
- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/source-of-truth-decision.md`
- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/object-graph-map.md`

Advisory/stale for this slice:

- root `CURRENT_TRUTH.md`, because it declares itself stale and defers to `fix-this.md` / `TODO.md`
- `.agents/management/CURRENT_TRUTH.md`, because current evidence supersedes it where it disagrees
- older `EVIDENCE/EXECUTION.md`
- older V5.9/AuthBuilder notes, unless they map directly to active TODO-006
- previous Slice A evidence referencing old HEAD `f04cb8a1c582`

## Files Changed

Production:

- `framework/System/Configuration/Builders/BuildRunApplication.php`
- `framework/System/PublicSurface/App.php`
- `framework/System/Flows/RunApplication/RunApplication.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`

Focused test contract:

- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`

Evidence:

- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/current-truth.md`
- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/source-of-truth-decision.md`
- `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/*.md`

## Change Summary

- Added `BuildRunApplication` under `framework/System/Configuration/Builders`.
- Moved the default `RunApplication` object graph from `RunApplication::withDefaultResolutionPipeline()` into `BuildRunApplication::fromDefaultResolutionPipeline()`.
- Made `BuildRunApplication::fromDefaultResolutionPipeline()` static and stateless to match warm-runtime builder guidance.
- Removed `RunApplication::withDefaultResolutionPipeline()`.
- Changed `App` to receive a ready `RunApplication` dispatcher through its constructor.
- Removed lazy dispatcher assembly from `App::ensureInitialized()`.
- Updated `CreateApplication` and `BootDslEngine` to build and pass the dispatcher from the Configuration owner.
- Updated the V4 architecture contract so `RouteFacadeContainer` is expected in the Configuration builder, not in the runtime flow.

## Source Conflicts Found

- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php` expected `RouteFacadeContainer` to appear in `RunApplication.php`.
- TODO-006 and current architecture governance say the default dispatch object graph belongs in Configuration and `RunApplication` should execute with ready collaborators.
- Resolution: the contract test was updated to assert that `RunApplication` does not instantiate `RouteFacadeContainer`, while `BuildRunApplication` owns the default assembly.

## Scope Decision

TODO-006 is not closed.

This slice removes the highest-confidence lazy dispatch pipeline assembly path only. Known residual TODO-006 work remains:

- `Avax::create()` still assembles a `CreateApplication` object graph.
- `Avax::bootInternal()` still assembles full kernel/runtime objects.
- `BootDsl::create()` still assembles BootDslEngine inputs.
- `App::handle()` still constructs request-scope open/close helpers per request.
- `App::asHttpKernel()`, `App::asConsoleKernel()`, and `App::asRuntimeKernel()` still construct compatibility adapters.
- `CreateApplication` still assembles the runtime/App object graph and may need a later Configuration extraction.

## Validation Decision

This evidence supports `TODO_PARTIAL_WITH_YELLOW` for TODO-006 Slice A:

- focused PHPUnit passed: `OK (122 tests, 234 assertions)`
- changed-file PHPStan passed with no errors
- `check-public-surface.php` passed
- `check-namespace-drift.php` passed
- governance index and root evidence hygiene passed
- `git diff --check` passed
- broad PHPStan, direct-instantiation, and runtime-composition gates still have accepted/pre-existing YELLOW findings outside the completed Slice A change

Implementation may proceed no further in Slice A without broadening TODO-006; the next remediation should be a new smallest safe slice after review/merge.
