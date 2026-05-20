# TODO-006 Slice C Implementation Summary

## Slice

TODO-006 Slice C - remove remaining `App.php` direct runtime/route object construction findings.

## Files Changed

Production:

- `framework/System/PublicSurface/App.php`
- `framework/System/Flows/HandleIncomingHttp/CreateRuntimeRequestFromHttpRequest.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`

Contract test:

- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`

Evidence:

- `slice-c-plan.md`
- `slice-c-high-level-design.md`
- `slice-c-low-level-design.md`
- `slice-c-implementation-summary.md`
- `slice-c-test-proof.md`
- `slice-c-validation-output.md`
- `slice-c-governance-review.md`
- `slice-c-final-decision.md`

## Change Summary

- Added `CreateRuntimeRequestFromHttpRequest` to convert canonical HTTP requests into `RuntimeRequest`.
- Changed `App` to receive `FrameworkRouteRegistrar`, request-scope open/close flows, and runtime-request converter through its constructor.
- Changed `App` route registration to delegate to `FrameworkRouteRegistrar`.
- Changed `App::handle()` to use injected scope flows.
- Changed `App::handleRequest()` to use the injected runtime-request converter.
- Updated `CreateApplication` and `BootDslEngine` assembly paths to provide the new App collaborators.
- Updated the V4 architecture contract to prove `App` does not instantiate those runtime/route objects directly.

## Behavior Preserved

- public `App` method signatures are unchanged
- route registration behavior remains covered by `AppTest`
- request handling behavior remains covered by `AppTest`
- request scope close remains in `finally`
- custom exception handling remains unchanged

## Remaining TODO-006 Work

`framework/System/PublicSurface/App.php` no longer appears in direct-instantiation output.

Remaining TODO-006 candidate after Slice C:

- `framework/System/PublicSurface/Avax.php` still directly assembles `Avax::create()` and `bootInternal()` object graphs, but the current direct-instantiation gate treats it as an explicit composition root.

This must be addressed or explicitly accepted before closing TODO-006.
