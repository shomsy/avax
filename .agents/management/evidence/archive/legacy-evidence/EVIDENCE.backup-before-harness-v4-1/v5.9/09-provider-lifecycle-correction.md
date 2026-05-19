# 09 — Provider Lifecycle Correction Proof

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## Finding (from 07-correction-preflight)

Both `register()` and `boot()` created new provider instances via `new $providerClass()`, meaning different instances
received each lifecycle call. Provider state set during `register()` was invisible during `boot()`.

## Correction

Added `$providerInstances` array to `BootDslEngine`:

```php
private array $providerInstances = [];
```

- `registerProviders()`: instantiates once, stores in `$providerInstances`, calls `register()`
- `bootProviders()`: retrieves same instance from `$providerInstances`, calls `boot()`

## Proof

- Test: `same_provider_instance_receives_register_and_boot` — proves `$registerContainer === $bootContainer`
- Test: `provider_state_set_during_register_is_visible_during_boot` — proves state survives between register and boot
- Test: `framework_provider_boots_before_user_provider` — proves ordering: framework register before user boot

## Files Changed

- `framework/System/Configuration/BootDsl/BootDslEngine.php` (UPDATED — provider instance storage and reuse)
- `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php` (UPDATED — `StateTrackingServiceProvider` helper)

## Verdict

Provider lifecycle is correct. Same instance receives both `register()` and `boot()`. State survives.
