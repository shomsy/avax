# V5.7-01 — Canonical Owner Enforcement

**Date:** 2026-05-12
**Branch:** main
**Commit:** f3818bcd706058ccf4c445bc98d0c41d07be5d9f
**Scope:** Remove duplicate ListenerRegistry, clarify canonical ownership

## Changes Made

### Deleted

- `components/Operations/Events/System/Capabilities/ListenerRegistry/ListenerRegistry.php`
- `components/Operations/Events/System/Capabilities/ListenerRegistry/` (directory, now empty after file removal)

### Why Safe

1. **No imports**: Zero references to `Avax\Components\Operations\Events\System\Capabilities\ListenerRegistry\ListenerRegistry` exist anywhere in components/, framework/, or tests/.
2. **Events.php uses the other one**: `Events.php` imports `Registry\ListenerRegistry`, not `ListenerRegistry\ListenerRegistry`.
3. **Incomplete duplicate**: The deleted file only had `subscribe()`, `getListenersFor()`, and `clear()`. The kept `Registry/ListenerRegistry.php` has all those plus `remove()`, `hasListeners()`, `listenerCount()`.
4. **Tests pass**: 11 Events tests pass, 240 MessageBus/EventBus tests pass, full suite unchanged.
5. **PHPStan clean**: 0 errors on Events component scope.
6. **Autoload updated**: 9194 classes (down from 9195, one duplicate removed).

### What Remains for Later V5.7 Stages

- V5.7-02: Event contracts and foundation (EventEmitter, ListenerDeclaration, etc.)
- V5.7-03: Fluent DSL `onEvent()->do()`
- V5.7-04: `emit()` public surface
- V5.7-05: `#[ListensTo]` attribute
- V5.7-06: Compile listener registry
- V5.7-07: Dispatch runtime
- V5.7-08: PSR-14 adapter
- V5.7-09: Stoppable event support
- V5.7-10: Tooling gates
- V5.7-11: Docs and examples
- V5.7-12: Final acceptance audit

### What Was NOT Changed (Out of Scope for V5.7-01)

- `components/Operations/MessageBus/System/Capabilities/Bus/EventBus.php` — MessageBus-specific adapter, kept as-is.
- `components/DataStack/Database/System/Capabilities/Telemetry/Events/*` — Database telemetry events, kept as-is.
- `components/HTTP/Session/System/Capabilities/Events/SessionEventBus.php` — Session lifecycle events, kept as-is.
- `components/DeveloperTools/Testing/System/Capabilities/Fakes/EventFake.php` — Test fixture, kept as-is.
- `framework/System/Capabilities/TestingFakes/EventFake.php` — Test fixture, kept as-is.
- No DSL, no emit(), no ListensTo, no PSR-14 — all reserved for later V5.7 stages.

## Verification

- PHPUnit Events: 11 tests, 18 assertions — GREEN
- PHPUnit MessageBus/EventBus filter: 240 tests, 502 assertions — GREEN
- PHPStan Events scope: 0 errors
- Autoload: 9194 classes (clean)
