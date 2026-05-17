# V5.7 Events Runtime Closure — Compiled Listener Registry, Dispatch Runtime & PSR-14 Adapter

**Date:** 2026-05-12
**Branch:** main
**Scope:** V5.7-04 through V5.7-08

## Summary

This pass closes the core event runtime so AvaX can safely dogfood events in the next stage.

## Stages Completed

### V5.7-04 — emit() Public Surface — GREEN

**Public API:** `emit(object $event): object`

- Only accepts event objects — no class-string emission
- Returns the same event object after listener invocation
- No-listener behavior returns the event unchanged
- Does not register listeners, mutate state, or create hidden runtime state
- Delegates to canonical EventEmitter

**Files created:**
- `components/Operations/Events/System/Flows/EmitEvent/EmitEvent.php`

**Files modified:**
- `components/Operations/Events/System/PublicSurface/functions.php` — added `emit()` global function
- `components/Operations/Events/System/Foundation/GlobalEventListenerState.php` — added emitter support

### V5.7-05 — ListensTo Attribute Declaration — GREEN

**Attribute:** `#[ListensTo(EventClass::class, priority: N)]`

- Declaration-only attribute, targets classes
- Attribute reflection happens only at compile-time, not at runtime dispatch
- Compiled into the same registry as DSL registrations

**Files created:**
- `components/Operations/Events/System/Foundation/ListensTo.php`

### V5.7-06 — Compiled Listener Registry — GREEN

**Core:** `CompiledListenerRegistry` + `CompileEventListeners`

- DSL registrations compile into CompiledListenerRegistry
- #[ListensTo] attribute declarations compile into CompiledListenerRegistry
- Both sources share one registry
- Higher priority runs first; same priority preserves registration order
- Source tracking (DSL / Attribute / Configuration)
- Registry freezes after compilation
- Unknown event returns empty list
- No runtime reflection — only compile-time attribute scanning

**Files created:**
- `components/Operations/Events/System/Foundation/CompiledListenerRegistry.php`
- `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php`

**Files modified:**
- `components/Operations/Events/System/Capabilities/Registry/ListenerRegistry.php` — added `getAllEvents()`
- `components/Operations/Events/System/Foundation/CompiledListener.php` — updated docblock for callable|class-string

### V5.7-07 — Dispatch Runtime — GREEN

**Core:** `EventEmitter` + `ResolveEventListeners` + `InvokeEventListener`

**Runtime flow:**
```
emit(event) → EventEmitter → CompiledListenerRegistry → ResolveEventListeners → InvokeEventListener → return event
```

- emit() returns the event
- No-listener case returns the same event
- Listener return values are ignored
- Listener exceptions bubble by default (no catch-and-ignore)
- Stoppable events work (duck-typed: `isPropagationStopped()`)
- Plain event objects dispatch without EventInterface
- Invokable listeners dispatch without ListenerInterface
- No runtime attribute reflection
- Dispatcher uses canonical compiled registry

**Files created:**
- `components/Operations/Events/System/Foundation/EventEmitter.php`
- `components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php`
- `components/Operations/Events/System/Capabilities/InvokeEventListener/InvokeEventListener.php`

**Files modified:**
- `components/Operations/Events/System/Configuration/RegisterEventDependencies.php` — fixed bug + compile-and-wire

### V5.7-08 — PSR-14 Adapter — GREEN

**Adapters:**
- `Psr14EventDispatcherAdapter` — wraps AvaX EventEmitter
- `Psr14ListenerProviderAdapter` — wraps AvaX CompiledListenerRegistry

- PSR `dispatch($event)` delegates to AvaX dispatcher
- PSR `getListenersForEvent($event)` delegates to AvaX listener provider
- PSR StoppableEventInterface respected via duck-typing
- Userland still uses `onEvent()` and `emit()`, not PSR plumbing
- `psr/event-dispatcher` added as direct dependency

**Files created:**
- `components/Operations/Events/System/Capabilities/Psr14/Psr14EventDispatcherAdapter.php`
- `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php`

**composer.json:** `psr/event-dispatcher ^1.0` added to require

## Tests

**New test file:** `tests/Unit/Components/Operations/Events/EventsRuntimeClosureTest.php`
- 49 new tests covering all V5.7-04 through V5.7-08 acceptance criteria
- All pass, 74 assertions

**Total suite:** 8045 tests, 23091 assertions — GREEN (up from 7947)

## Validation

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | GREEN |
| `composer dump-autoload -o` | GREEN, 9215 classes |
| `vendor/bin/phpunit --no-coverage` | GREEN, 8045 tests, 23091 assertions |
| `vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit` | GREEN, 0 errors |
| `php tooling/security/check-security-blockers.php` | PASS |
| `php tooling/governance/check-component-adoption.php` | PASS |
| `php tooling/refactor/check-component-canonical-shape.php` | GREEN |
| `php tooling/refactor/check-namespace-drift.php` | PASS |
| `php tooling/refactor/check-public-surface.php` | PASS |
| `php tooling/refactor/check-runtime-leaks.php` | PASS |
| `php tooling/refactor/check-advanced-pattern-folder-violations.php` | GREEN |
| `php tooling/refactor/check-component-suite-structure.php` | PASS |
| `php tooling/refactor/check-duplicate-owners.php` | PASS |
| `php tooling/failure-boundary/check-attributes-compiled.php` | GREEN |
| `php tooling/failure-boundary/check-local-try-catch.php` | GREEN |
| `php tooling/failure-boundary/check-dogfooding.php` | GREEN |

## Remaining Risks

- In-memory compiled registry only (disk persistence is ROADMAP)
- Simple listener instantiation (container integration is ROADMAP)
- Eager listener instantiation from DSL (documented as temporary, V5.7-06/07 scope)
- No dogfooding yet — planned for next stage

## Next Allowed Action

V5.7-09 through V5.7-12 (Stoppable Events, Tooling Gates, Docs, Final Audit) — or dogfooding pass.
