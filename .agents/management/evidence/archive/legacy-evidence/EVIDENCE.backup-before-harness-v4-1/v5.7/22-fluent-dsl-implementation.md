# V5.7-03 — Fluent Event DSL Implementation

**Date:** 2026-05-12
**Branch:** main
**Scope:** Implementation of `onEvent()->do()` registration DSL

## Files Created

| File                                                                                    | Purpose                                                 |
|-----------------------------------------------------------------------------------------|---------------------------------------------------------|
| `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php` | Fluent DSL class with chainable `do()` method           |
| `components/Operations/Events/System/Foundation/GlobalEventListenerState.php`           | Boot-time singleton holding shared ListenerRegistry     |
| `components/Operations/Events/System/PublicSurface/functions.php`                       | Global `onEvent()` and `onEventSetRegistry()` functions |
| `tests/Unit/Components/Operations/Events/EventDslTest.php`                              | 12 DSL tests                                            |

## Files Modified

| File            | Change                                       |
|-----------------|----------------------------------------------|
| `composer.json` | Added events functions.php to autoload files |

## Design Decisions

### Class-string resolution

The DSL accepts `class-string|callable` for the listener parameter. When a class-string is passed, the DSL instantiates
the class and creates a `[new $listener(), '__invoke']` callable. This allows both closure-based and
invokable-class-based listener registration through the same API.

### GlobalEventListenerState

A boot-time singleton (not GlobalEventRegistry from the design doc — simplified naming) holds the shared
`ListenerRegistry` instance. The framework wires this during bootstrap via `onEventSetRegistry()`. In tests, each test
creates its own registry and resets the state in `tearDown()`.

### No emit() in this stage

`emit()` is reserved for V5.7-04. This stage only implements the registration side (`onEvent()->do()`).

### No GlobalEventRegistry with EventEmitter

The design doc described a `GlobalEventRegistry` holding both registry and emitter. V5.7-03 simplifies this to
`GlobalEventListenerState` holding only the `ListenerRegistry`. The emitter will be wired in V5.7-04.

## Validation Results

| Command                                                                   | Result                                                 |
|---------------------------------------------------------------------------|--------------------------------------------------------|
| `composer validate --no-check-publish`                                    | GREEN                                                  |
| `composer dump-autoload -o`                                               | GREEN — 9203 classes                                   |
| `vendor/bin/phpunit --no-coverage`                                        | GREEN — 7947 tests, 22931 assertions (35 Events tests) |
| `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | GREEN — 0 errors                                       |
| `php tooling/events/check-canonical-event-owner.php`                      | PASS — 7/7                                             |

## Evidence

Tests prove:

- EventListenerDsl returns itself for chaining
- DSL registers listeners via subscribe
- DSL accepts class-string listeners (resolves to invokable)
- DSL supports priority ordering
- DSL chains multiple listeners correctly
- `onEvent()` returns EventListenerDsl instance
- `onEvent()` registers listeners through global registry
- `onEvent()` chains multiple `do()` calls
- `onEvent()` respects priority ordering
- `onEvent()` preserves registration order for same priority
- `onEvent()` uses shared registry across calls
- `onEvent()` default priority is zero
