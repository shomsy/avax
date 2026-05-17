# EVIDENCE/v5.9/03-boot-dsl-design-lock.md

## V5.9 Boot DSL — Design Lock

**Date:** 2026-05-16
**Branch:** main
**Status:** DESIGN_LOCK

---

## 1. Public Boot DSL Entrypoint

**Decision:** `Avax` becomes the Boot DSL entry point with a fluent intent-first API.

```php
$app = Avax::boot()
    ->from(projectPath: $projectPath, environmentName: $environmentName)
    ->withProvider(AppServiceProvider::class)
    ->withRoutes(__DIR__ . '/routes.php')
    ->create();
```

The `Avax` class remains the public entry but no longer manually constructs services.
It delegates to an internal Boot DSL engine that owns the container lifecycle.

---

## 2. What the User Writes

Users write:

```php
// Minimal app
$app = Avax::boot()
    ->from(__DIR__)
    ->withProvider(AppServiceProvider::class)
    ->withRoutes(__DIR__ . '/routes/web.php')
    ->create();

$app->get('/', fn () => 'Hello');
$app->run();
```

```php
// With explicit config
$app = Avax::boot()
    ->from(projectPath: $path, environmentName: 'production')
    ->withProviders([
        AppServiceProvider::class,
        RouteServiceProvider::class,
        DatabaseServiceProvider::class,
    ])
    ->withConfig(__DIR__ . '/config')
    ->create();
```

The user writes **intent**: which providers, which routes, which config.
The user does **not** write: container bindings, service construction, provider scanning logic.

---

## 3. What the Boot DSL Collects

The Boot DSL collects:

| Collected item      | How                                  |
|---------------------|--------------------------------------|
| Project path        | `from(projectPath: ...)`             |
| Environment name    | `from(environmentName: ...)`         |
| ServiceProviders    | `withProvider()` / `withProviders()` |
| Route files         | `withRoutes()` / `withRouteFiles()`  |
| Config directory    | `withConfig()`                       |
| Clock override      | `withClock()`                        |
| Custom runtime name | `withRuntimeName()`                  |

The Boot DSL internally:

1. Creates the root `Container`.
2. Registers bootstrap bindings (Clock, ProjectPath, EnvironmentName).
3. Scans and registers `FrameworkServiceProvider`.
4. Scans and registers user-provided providers.
5. Optionally scans component directories for `*ServiceProvider.php`.
6. Calls `register()` on all providers in order.
7. Calls `boot()` on all providers in order.
8. Verifies all registered bindings resolve.
9. Freezes the container (no more registrations).
10. Creates the Runtime from the container.
11. Returns the App.

---

## 4. What the Boot DSL Does NOT Do

- Does NOT execute requests or jobs.
- Does NOT become a runtime adapter.
- Does NOT implement Swoole/RoadRunner/FrankenPHP integration.
- Does NOT split AuthBuilder.
- Does NOT refactor Response layer.
- Does NOT weaken any existing gates.
- Does NOT create fake DSL shells without real container lifecycle.
- Does NOT bypass stage lock.

---

## 5. Where Providers Are Registered

Providers are registered by the Boot DSL engine during its `register` phase:

```
Boot DSL register phase:
  1. Bind Container itself into Container
  2. Bind ProjectPath, EnvironmentName, Clock
  3. Register FrameworkServiceProvider
  4. Register user-provided providers (in declaration order)
  5. Register auto-scanned providers (if auto-scan enabled)
  6. Call register() on each provider in order
```

Provider ordering:

- `FrameworkCoreServiceProvider` (or equivalent bootstrap provider) MUST load first.
- User providers load next (in declaration order).
- Auto-scanned component providers load last (sorted by area).

---

## 6. Where the Root Container Is Created

The root Container is created by the Boot DSL engine at the start of the boot sequence:

```php
// Inside BootDslEngine (internal, not public):
$container = new Container();
```

The Container is created before any provider registration.
The Container is registered into itself as `ContainerInterface`.

---

## 7. Where Compile/Verify/Freeze Happens

All three phases happen inside the Boot DSL engine, between `register()` and `boot()`:

```
Boot DSL lifecycle:
  1. create container
  2. register bootstrap bindings
  3. register providers
  4. call register() on all providers
  5. COMPILE  — resolve all singleton bindings to verify wiring
  6. VERIFY   — check no unresolved required bindings
  7. FREEZE   — mark container as immutable (no more registrations)
  8. call boot() on all providers
  9. create Runtime from container
  10. return App
```

Compile, verify, and freeze are distinct:

- **Compile**: Resolve all singleton bindings to prove they can be constructed.
- **Verify**: Check that all required bindings have registrations.
- **Freeze**: Mark container as immutable — reject any subsequent `bind()`/`singleton()`/`register()`.

---

## 8. Where Provider Boot Happens

Provider `boot()` runs AFTER compile/verify/freeze:

```
After freeze:
  for each provider in order:
      provider->boot($container)
```

Boot is the last chance for providers to:

- Subscribe to events
- Register middleware declarations
- Register route declarations
- Register health check declarations
- Wire facade bridges
- Finalize component wiring

Boot MUST be idempotent.
Boot MUST NOT register new dependencies.

---

## 9. Where Runtime Receives the Ready Graph

The Runtime is created AFTER all providers have booted and the container is frozen.

```php
$runtime = $container->get(RuntimeFactory::class)->create();
```

Or directly:

```php
$runtime = new Runtime(
    runtimeState: ...,
    clock: $container->get(Clock::class),
    projectPath: $container->get(ProjectPath::class),
    // ... all resolved from container
);
```

The Runtime receives already-assembled services.
The Runtime does NOT assemble anything.

---

## 10. How This Stays Runtime-Neutral

The Boot DSL:

- Knows nothing about ReactPHP, Swoole, RoadRunner, FrankenPHP.
- Does NOT create runtime adapters.
- Creates a neutral `Runtime` object.
- The Runtime object holds state, context, request scope, component registry.
- Runtime adapters (V4-17) use the Runtime object — they do not create it.

The Boot DSL output is an `App` that can be used by any runtime.

---

## 11. How This Avoids Service Locator Behavior

- Providers receive `ContainerInterface` only in `register()` and `boot()` — approved composition contexts.
- Runtime code receives specific dependencies through constructor injection.
- The Boot DSL engine is the only code that calls `Container::get()` for root objects.
- After freeze, the container is not accessible for resolution outside approved contexts.

---

## 12. How This Avoids Direct Runtime Instantiation

After the Boot DSL creates the App:

- `App::run()` receives a ready dispatcher.
- `App::handle()` receives a ready RuntimeRequest.
- Route handlers are invoked through `ResolveCallable` from the container.
- No `new ServiceClass()` exists in runtime path.

---

## 13. How This Preserves Old Builder Compatibility

The Boot DSL internally uses ApplicationBuilder-like mechanics but hides them behind fluent DSL.

For backward compatibility, the old path still works:

```php
// Old path — still works but deprecated
$builder = new ApplicationBuilder(...);
$avax = Avax::boot($builder);

// New path — preferred
$app = Avax::boot()
    ->from(__DIR__)
    ->withProvider(AppServiceProvider::class)
    ->create();
```

The old `Avax::boot(ApplicationBuilder)` delegates to the new Boot DSL engine internally.

---

## 14. Migration Path from ApplicationBuilder

Phase 1 (this slice):

- Boot DSL exists alongside ApplicationBuilder.
- `Avax::boot()` returns Boot DSL builder.
- `Avax::boot(ApplicationBuilder)` still works.

Phase 2 (later):

- ApplicationBuilder internals delegate to Boot DSL.
- ApplicationBuilder becomes a thin wrapper.

Phase 3 (later):

- ApplicationBuilder deprecated.
- Boot DSL is the only path.

---

## 15. Explicitly Out of Scope for This First Slice

- Auto-scanning component directories for ServiceProviders.
- Config file loading (YAML/PHP config directory parsing).
- Container compile optimization (warm compilation, caching).
- Provider priority/sorting beyond declaration order.
- Child container / request scope containers.
- Runtime adapter integration.
- AuthBuilder split.
- Response refactor.
- Any Fix-This cleanup.

**This slice delivers:**

1. Boot DSL fluent API (`Avax::boot()->from()->withProvider()->withRoutes()->create()`).
2. Root Container creation and lifecycle (create → register → compile → verify → freeze → boot).
3. Provider registration and boot ordering.
4. Runtime creation from container.
5. App returned as product.
6. One test proving the full path works.
7. Evidence document.

---

## 16. Proposed Internal Architecture

```
framework/
  System/
    Configuration/
      BootDsl/
        BootDslBuilder.php          — public fluent DSL builder
        BootDslEngine.php           — internal engine: container lifecycle
        ProviderRegistry.php        — provider collection + ordering
        BootPhase.php               — enum: Create, Register, Compile, Verify, Freeze, Boot, Run
```

```
framework/
  System/
    Flows/
      BootApplication/
        BootWithDsl.php             — flow: execute Boot DSL lifecycle
```

```
framework/
  System/
    PublicSurface/
      Avax.php                      — updated: delegates to Boot DSL
```

---

## 17. Target DSL Feeling

- **Boring**: No clever tricks, no magic, no hidden behavior.
- **Obvious**: Each method does what its name says.
- **Small**: Minimal API surface.
- **Readable**: Call sites read like intent, not machinery.
- **Intent-first**: User declares what they want, not how to build it.
- **No nested value-object construction at call sites**: One `from()` call, not 8 constructor params.
- **No mechanical wrapper chains**: Each method returns the builder, not a new sub-builder.
- **No user-facing runtime machinery**: User never sees ContainerInterface, ProviderRegistry, or BootPhase.

---

## 18. Design Lock Status

**DESIGN_LOCK — Ready for first vertical slice implementation.**

All 15 design questions answered.
Architecture decisions documented.
Scope boundaries defined.
Out-of-scope items listed.
Migration path defined.
