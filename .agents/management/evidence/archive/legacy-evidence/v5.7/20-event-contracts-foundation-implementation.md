# V5.7-02 — Event Contracts and Foundation Implementation

**Date:** 2026-05-12
**Branch:** main
**Commit:** 1fee87631af292b3f16c3e97f420684a428c553d (parent)
**Scope:** Implementation of foundation types, enums, and minimal registry behavior for V5.7-02

## Files Created

| File | Purpose |
|---|---|
| `components/Operations/Events/System/Foundation/ListenerSource.php` | `enum ListenerSource: string` — Dsl, Attribute, Configuration |
| `components/Operations/Events/System/Foundation/ListenerExecutionMode.php` | `enum ListenerExecutionMode: string` — Sync only |
| `components/Operations/Events/System/Foundation/ListenerRegistration.php` | Registration value object with eventClass, listener (mixed + @param callable), priority, source, mode, order |
| `components/Operations/Events/System/Foundation/CompiledListener.php` | Compiled listener value object with same fields |
| `components/Operations/Events/System/Capabilities/ListenerProvider/ListenerProvider.php` | Provider wrapping ListenerRegistry, exposes `listenersFor(object): array` |
| `tests/Unit/Components/Operations/Events/EventFoundationTest.php` | 12 tests covering all foundation types |

## Files Modified

| File | Change |
|---|---|
| `components/Operations/Events/System/Capabilities/Registry/ListenerRegistry.php` | Added `register(ListenerRegistration)`, `listenersFor(string)` alias, `$registrationOrder` counter, `$registrations` array |

## Design Decisions

### callable cannot be typed in readonly classes

PHP 8.5 does not allow `callable` as a typed property in `readonly` classes. Both `ListenerRegistration::$listener` and `CompiledListener::$listener` use `public mixed $listener` with `@param callable $listener` docblock. This preserves runtime callable behavior while satisfying PHP's type system.

### No EventInterface or ListenerInterface

User events are plain readonly objects. User listeners are plain callables. No forced interfaces at this stage. The contracts are the foundation types that define how registrations look.

### ListenerProvider returns array, not iterable

PHPStan flagged `list<callable>` as incompatible with `array` return. Changed to `array<int, callable>` return type annotation and explicit `array` return type to satisfy static analysis.

### What this stage does NOT do

- No `onEvent()` DSL (V5.7-03)
- No `emit()` helper (V5.7-04)
- No `#[ListensTo]` attribute (V5.7-05)
- No compilation pipeline (V5.7-06)
- No PSR-14 adapter (V5.7-08)
- No EventInterface or ListenerInterface for userland
- No GlobalEventRegistry
- No disk persistence

## Validation Results

| Command | Result |
|---|---|
| `composer validate --no-check-publish` | GREEN |
| `composer dump-autoload -o` | GREEN — 9200 classes |
| `vendor/bin/phpunit --no-coverage` | GREEN — 7923 tests, 22895 assertions (23 Events tests) |
| `vendor/bin/phpstan analyse components/Operations/Events tests/Unit/Components/Operations/Events --memory-limit=512M` | GREEN — 0 errors |
| `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | GREEN — 0 errors |
| `php tooling/events/check-canonical-event-owner.php` | PASS — 7/7 |

## Evidence

Tests prove:
- ListenerRegistration stores event class, listener, priority, source, mode, order
- CompiledListener stores dispatch metadata
- ListenerRegistry returns empty list when no listeners exist
- ListenerRegistry returns registered listeners for event
- ListenerRegistry sorts by priority descending
- ListenerRegistry preserves registration order for same priority
- ListenerProvider delegates to ListenerRegistry
- User events need not implement any interface
- User listeners need not implement any interface
- Sync is the only active execution mode
- ListenerSource has Dsl, Attribute, Configuration cases
- Closure invocation through ListenerRegistry works end-to-end
