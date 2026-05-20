# API Compatibility

## Public API Inventory

Public methods touched indirectly:

- `Avax::create()` behavior
- `BootDsl::create()` behavior
- `App::__construct()` internal construction path
- `App::handle()`
- `App::run()`
- route registration methods on `App`

## Compatibility Answers

- public API changed: NO for user-facing factory/method behavior
- backward compatible: YES
- migration needed: NO
- contract tests updated: YES
- old behavior preserved: YES
- deprecation path needed: NO
- semantic version impact: PATCH_SAFE internal architecture cleanup
- user-facing examples still work: proven by focused App/Avax/BootDsl tests
- documentation updated: NO, not needed for internal assembly relocation
- changelog entry needed: NO for Slice A evidence-only branch

## Constructor Note

`App::__construct()` now requires `RunApplication`. Current production creation points are updated. This constructor is not the documented zero-configuration public entrypoint; public creation remains `Avax::create()` and `BootDsl::create()`.

## Classification

PATCH_SAFE.
