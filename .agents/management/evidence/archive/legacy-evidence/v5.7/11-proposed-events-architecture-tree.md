# V5.7 — 11 Proposed Events Architecture Tree

**Date:** 2026-05-12
**Owner:** `components/Operations/Events/`

## Proposed Tree

```
components/Operations/Events/
  System/
    PublicSurface/
      Events.php                    — Existing facade, enhanced with DSL methods
      functions.php                 — Global helpers: onEvent(), emit(), events()

    Flows/
      RegisterEventListeners/
        RegisterEventListeners.php  — Registers DSL-style listener declarations
        EventListenerDsl.php        — Fluent DSL: onEvent()->do()

      EmitEvent/
        EmitEvent.php               — Flow: dispatch event through emitter

      CompileEventListeners/
        CompileEventListeners.php   — Scans attributes + DSL, builds compiled registry

    Capabilities/
      DispatchEvent/
        DispatchEvent.php           — Existing, updated to use new EventEmitter

      ResolveEventListeners/
        ResolveEventListeners.php   — Resolves listener classes via container

      InvokeEventListener/
        InvokeEventListener.php     — Invokes a single listener against event

      ProvidePsr14Listeners/
        ProvidePsr14Listeners.php   — PSR-14 ListenerProvider adapter

      ProvidePsr14Dispatch/
        ProvidePsr14Dispatch.php    — PSR-14 EventDispatcher adapter

    Configuration/
      RegisterEventDependencies.php — Existing, updated to wire new components
      BuildEvents.php               — Boot-time assembly: registry + emitter + compiler

    Foundation/
      EventEmitter.php              — New: Entry point for event dispatch (renamed from EventDispatcher concept)
      EventDispatcher.php           — Existing, coordinates listener execution
      ListenerRegistry.php          — Existing (Registry/ListenerRegistry.php) — canonical
      ListenerDeclaration.php       — New: Immutable listener registration record
      ListenerRegistration.php      — New: Registered listener with resolved instance
      CompiledListener.php          — New: Pre-compiled listener metadata
      CompiledListenerRegistry.php  — New: Frozen compiled listener map
      ListenerPriority.php          — New: Priority enum
      ListenerExecutionMode.php     — New: Execution mode enum (sync only for V5.7)
      ListensTo.php                 — New: #[ListensTo] attribute
      GlobalEventRegistry.php       — New: Boot-time singleton for DSL/emit coordination
```

## Migration Plan

### What Stays

- `Operations/Events/System/Capabilities/Registry/ListenerRegistry.php` — becomes the canonical ListenerRegistry
- `Operations/Events/System/Capabilities/Dispatcher/EventDispatcher.php` — becomes listener coordination layer under
  EventEmitter
- `Operations/Events/System/Flows/DispatchEvent/DispatchEvent.php` — kept, updated
- `Operations/Events/System/Flows/SubscribeToEvent/SubscribeToEvent.php` — kept as internal mechanism
- `Operations/Events/System/PublicSurface/Events.php` — enhanced with DSL
- `Operations/Events/System/PublicSurface/EventsInterface.php` — updated with new methods
- `Operations/Events/System/Configuration/RegisterEventDependencies.php` — updated

### What Goes (Superseded)

- `Operations/Events/System/Capabilities/ListenerRegistry/ListenerRegistry.php` — incomplete duplicate, remove after
  migration

### What Is New

- `EventEmitter` — new entry point (wraps EventDispatcher + CompiledListenerRegistry)
- `EventListenerDsl` — fluent DSL class
- `functions.php` — global helpers
- `ListensTo` attribute
- `ListenerDeclaration`, `CompiledListener`, `CompiledListenerRegistry`
- `ListenerPriority`, `ListenerExecutionMode` enums
- `GlobalEventRegistry` — boot-time coordination
- `Psr14EventDispatcherAdapter`, `Psr14ListenerProviderAdapter`
- `CompileEventListeners` flow
- `ResolveEventListeners` capability
- `InvokeEventListener` capability

## Rules Applied

| Rule                        | Application                                                        |
|-----------------------------|--------------------------------------------------------------------|
| Folder says flow/capability | Flows/, Capabilities/, Foundation/, Configuration/, PublicSurface/ |
| Unit says responsibility    | Each class has one clear responsibility                            |
| Function says exact action  | `subscribe()`, `dispatch()`, `compile()`, `invoke()`               |
| PublicSurface receives      | `Events.php`, `functions.php` — user-facing entry points           |
| Flows execute               | `RegisterEventListeners`, `EmitEvent`, `CompileEventListeners`     |
| Capabilities power          | `DispatchEvent`, `ResolveEventListeners`, `InvokeEventListener`    |
| Configuration assembles     | `RegisterEventDependencies`, `BuildEvents`                         |
| Foundation supports         | Data structures, enums, attributes, registry                       |
| No generic naming           | No Manager/Service/Helper/Util                                     |
