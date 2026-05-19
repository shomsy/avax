# ApiVersioningServiceProvider Wiring Proof

**Date:** 2026-05-15
**Test file:** `tests/Unit/Components/HTTP/ApiVersioning/ApiVersioningServiceProviderTest.php`

## 1. Proof Scenarios

| Scenario | Test method | Expected | Result |
|---|---|---|---|
| Unconfigured facade fails clearly | test_unconfigured_facade_fails_clearly | RuntimeException "not configured" | PASS |
| Provider registers VersionRegistry | test_provider_registers_version_registry | VersionRegistry resolves, current=1 | PASS |
| Provider boots facade | test_provider_boots_facade_with_provider_created_registry | ApiVersion::current() works after boot | PASS |
| No second source of truth | test_no_second_registry_source_of_truth | Container mutation visible through facade | PASS |
| Reset clears state | test_reset_clears_facade_state_and_unconfigured_usage_fails | After reset, usage fails | PASS |
| Facade uses provider singleton | test_facade_uses_provider_singleton_not_new_instance | Pre-boot mutation visible after boot | PASS |
| Custom config respected | test_provider_respects_custom_config | current=3, supported=[1,2,3] | PASS |
| Double boot fails | test_double_boot_fails | RuntimeException "already configured" | PASS |

## 2. Key Proofs

- **Provider wiring:** ApiVersioningServiceProvider.register() binds VersionRegistry as singleton. boot() calls ApiVersion::setInstance() with container-resolved registry.
- **Single source of truth:** Container registry mutation is visible through facade, proving same instance.
- **No lazy fallback:** ApiVersion has no `new VersionRegistry()`, no `?? new`, no `??= new`.
- **Reset lifecycle:** reset() clears static state; subsequent usage fails with clear message.
- **Config-driven:** Provider reads `api.versioning.current` and `api.versioning.supported` from container config.

## 3. Decision

ApiVersioningServiceProvider wiring is **PROVEN**. The facade uses the provider-created VersionRegistry. No independent lazy allocation exists. Single source of truth is confirmed through behavioral proof.
