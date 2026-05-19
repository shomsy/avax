# 13 — Correction Test Proof

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## Test Suite

File: `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`

### Test Count

- 25 tests
- 59 assertions
- 0 failures
- 0 errors

### Coverage Matrix

| Finding                | Tests                                                                                                                                                                                                                                                                       | Status |
|------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------|
| PublicSurface boundary | `avax_dsl_returns_public_boot_dsl_not_internal_builder`, `boot_dsl_public_api_has_expected_methods`                                                                                                                                                                         | GREEN  |
| Provider lifecycle     | `same_provider_instance_receives_register_and_boot`, `provider_state_set_during_register_is_visible_during_boot`, `framework_provider_boots_before_user_provider`, `missing_provider_class_fails_clearly`                                                                   | GREEN  |
| Container freeze       | `container_is_frozen_after_boot`, `mutation_after_freeze_throws`, `singleton_after_freeze_throws`, `instance_after_freeze_throws`, `alias_after_freeze_throws`, `flush_after_freeze_throws`, `read_methods_work_after_freeze`, `resolve_after_freeze_creates_new_instances` | GREEN  |
| End-to-end             | `boot_dsl_builder_requires_project_path`, `boot_dsl_creates_booted_app`, `boot_dsl_engine_advances_through_all_phases`                                                                                                                                                      | GREEN  |
| Compatibility          | `avax_boot_with_application_builder_still_works`, `avax_create_still_works`                                                                                                                                                                                                 | GREEN  |
| Phase enum             | `boot_phase_enum_advances_sequentially`, `boot_phase_enum_throws_on_final_phase`                                                                                                                                                                                            | GREEN  |
| Registry               | `provider_registry_orders_framework_first`, `provider_registry_is_empty_initially`                                                                                                                                                                                          | GREEN  |
| State isolation        | `repeated_boot_does_not_leak_state`                                                                                                                                                                                                                                         | GREEN  |
| Missing dependency     | `missing_required_dependency_fails_during_compile_not_runtime`                                                                                                                                                                                                              | GREEN  |

### Test Helpers

- `StateTrackingServiceProvider` — tracks register/boot calls, container references, values, and ordering
- `createEngine()` — factory method with sensible defaults for test scenarios

### PHPStan

All new files pass PHPStan level 8 + type_perfect:

- `framework/System/Configuration/BootDsl/*`
- `framework/System/PublicSurface/BootDsl.php`
- `components/Application/Container/System/Foundation/FrozenContainer.php`
- `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`

### Composition Gate

`check-runtime-composition-leaks.php` passes with `BootDsl.php` added to composition roots.

## Verdict

Test suite is comprehensive. All 6 findings from correction preflight have dedicated tests. No unproven claims.
