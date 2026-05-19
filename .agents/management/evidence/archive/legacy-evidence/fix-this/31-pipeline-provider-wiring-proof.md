# PipelineServiceProvider Wiring Proof

**Date:** 2026-05-15
**Test file:** `tests/Unit/Components/Application/Pipeline/PipelineServiceProviderTest.php`

## 1. Proof Scenarios

| Scenario | Test method | Expected | Result |
|---|---|---|---|
| Unconfigured facade fails clearly | test_unconfigured_facade_fails_clearly | RuntimeException "not configured" | PASS |
| Provider registers HookRegistry | test_provider_registers_hook_registry | HookRegistry resolves from container | PASS |
| Provider boots facade | test_provider_boots_facade_with_provider_created_registry | Pipeline::hooks() works after boot | PASS |
| No second source of truth | test_no_second_registry_source_of_truth | Container mutation visible through facade | PASS |
| Reset clears state | test_reset_clears_facade_state_and_unconfigured_usage_fails | After reset, usage fails | PASS |
| Facade uses provider singleton | test_facade_uses_provider_singleton_not_new_instance | Pre-boot mutation visible after boot | PASS |
| Hook execution through provider | test_hook_execution_through_provider_wired_facade | Hooks execute in registration order | PASS |
| Double boot fails | test_double_boot_fails | RuntimeException "already configured" | PASS |

## 2. Key Proofs

- **Provider wiring:** PipelineServiceProvider.register() binds HookRegistry as singleton. boot() calls Pipeline::setInstance() with container-resolved registry.
- **Single source of truth:** Container registry mutation is visible through facade, proving same instance.
- **No lazy fallback:** Pipeline has no `new HookRegistry()`, no `?? new`, no `??= new`.
- **Reset lifecycle:** reset() clears static state; subsequent usage fails with clear message.
- **HookRegistry placement:** HookRegistry lives in `Capabilities/PipelineHooks/` (internal capability), NOT in PublicSurface.

## 3. Decision

PipelineServiceProvider wiring is **PROVEN**. The facade uses the provider-created HookRegistry. No independent lazy allocation exists. Single source of truth is confirmed through behavioral proof.
