# TODO-006 Slice B Implementation Summary

## Slice

TODO-006 Slice B - move `BootDsl::create()` boot engine assembly into Configuration.

## Files Changed

Production:

- `framework/System/Configuration/BootDsl/BuildBootDslEngine.php`
- `framework/System/PublicSurface/BootDsl.php`
- `framework/System/Configuration/BootDsl/BootDslBuilder.php`

Contract test:

- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`

Evidence:

- `slice-b-plan.md`
- `slice-b-high-level-design.md`
- `slice-b-low-level-design.md`
- `slice-b-implementation-summary.md`
- `slice-b-test-proof.md`
- `slice-b-validation-output.md`
- `slice-b-governance-review.md`
- `slice-b-final-decision.md`

## Change Summary

- Added `BuildBootDslEngine` under `framework/System/Configuration/BootDsl`.
- Moved clock fallback, project/environment value construction, default HTTP handler construction, provider registry creation, and `BootDslEngine` construction out of `BootDsl::create()`.
- Updated public `BootDsl::create()` to validate required `projectPath` and delegate engine assembly to Configuration.
- Updated internal `BootDslBuilder::create()` to use the same Configuration builder and avoid duplicating the graph.
- Updated the V4 architecture contract to prove public `BootDsl` does not instantiate `BootDslEngine` and Configuration does.

## Behavior Preserved

- `BootDsl::make()`, fluent DSL methods, and `BootDsl::create()` remain public and stable.
- Missing project path still throws the same `LogicException` message.
- Provider ordering and provider lifecycle remain owned by `ProviderRegistry` and `BootDslEngine`.
- `Avax::dsl()` still returns public `BootDsl`, not the internal builder.

## Remaining TODO-006 Work

Slice B removes `BootDsl.php` from the direct-instantiation residual findings.

TODO-006 remains PARTIAL because direct-instantiation still reports:

- `framework/System/PublicSurface/App.php:199`
- `framework/System/PublicSurface/App.php:203`
- `framework/System/PublicSurface/App.php:282`
- `framework/System/PublicSurface/App.php:316`

Broader non-TODO-006 direct-instantiation backlog also remains.
