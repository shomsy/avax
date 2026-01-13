# ContainerBuilder

## Quick Summary

- Technical: Low-level assembler that wires kernel, scopes, metrics, prototype factory, and default settings into a
  `Container`.
- It centralizes all DI engine wiring so high-level factories don’t reimplement bootstrap logic.

### For Humans: What This Means

This is the mechanic under the hood. It builds the engine (kernel) once and hands back a ready container.

## Terminology

- **KernelConfigFactory**: Helper that creates `KernelConfig` with strict/auto-define/dev flags and optional overrides.
- **ScopeRegistry/ScopeManager**: Track and manage lifetimes across scopes.
- **Prototype Factory**: Builds service prototypes from reflection and caches them.
- **Registrar**: Adds instances and bindings directly into the definition store.

### For Humans: What This Means

These are the parts of the engine: a config maker (with preset or custom profiles), scope manager, prototype builder,
and a registrar that records what the container knows.

## Think of It

Picture an automotive assembly bay: ContainerBuilder bolts together the chassis (definitions), engine (kernel),
electronics (metrics/timeline), and fluids (settings) before the car leaves the factory.

### For Humans: What This Means

You don’t assemble the car yourself; this builder hands you a finished one.

## Story Example

```php
use Avax\Container\Core\ContainerBuilder;

$container = (new ContainerBuilder())->build(
    cacheDir: __DIR__.'/storage/cache',
    debug: true
);
```

### For Humans: What This Means

A single call creates a fully wired container, ready for providers and runtime code.

## For Dummies

1. Call `build($cacheDir, $debug)` on a new builder.
2. It sets up definitions, scopes, resolvers, injector, invoker, metrics, and config.
3. It seeds default `Settings` and container self-references.
4. You receive a `Container` to pass into providers or factories.

### For Humans: What This Means

Ask the builder for a container; it fills in everything the kernel needs and returns it.

## How It Works (Technical)

- Creates DefinitionStore, ScopeRegistry, prototype analyzer/factory, resolver, engine, injector, and invoker.
- Builds KernelConfig via `KernelConfigFactory` with `$debug` presets or explicit strict/auto-define/dev overrides when
  provided.
- Wires circular references (engine/injector/invoker get the container).
- Seeds self-bindings (`ContainerInterface`, `Container`, `DefinitionStore`, `ScopeRegistry`) and default `Settings`
  plus `'config'` alias.

### For Humans: What This Means

It prepares all internals, links them to the container, and records the core services so you can resolve them
immediately—with either default debug-driven flags or custom profiles.

## Architecture Role

- Lives in `Core/` to keep wiring concerns out of high-level factories.
- Consumed by `AppFactory` (HTTP/CLI) to get a ready container.
- Shielding: changes to engine wiring stay localized here.

### For Humans: What This Means

If you change how the engine is built, you edit one file instead of every entrypoint.

## Methods

### Method: build(string $cacheDir, bool $debug = false): Container

Technical: Assembles kernel components, seeds defaults, returns a wired `Container`.

### For Humans: What This Means

Call this to get a ready container; pass `debug=true` for strict mode.

#### Parameters

- `string $cacheDir` Directory used for prototype caching.
- `bool $debug` Enables strict mode (no auto-define) when true.

#### Returns

- `Container` fully wired with defaults and self-bindings.

#### Throws

- Underlying reflection or filesystem errors if cache directory is invalid.

#### When to use it

- Before registering providers or creating specialized applications.

#### Common mistakes

- Passing a non-writable cache directory; forgetting providers still need to register app-specific services.

## Risks & Trade-offs

Technical: Centralizes wiring—great for consistency, but any error here blocks all boots. Seeds `Settings` and
`'config'` by default which providers may override.

### For Humans: What This Means

One file controls the engine; keep it correct. It installs a default config store that your providers can replace if
needed.

## Related Files & Folders

Technical:

- `Core/Kernel/KernelConfigFactory.php` — creates KernelConfig used here.
- `Core/AppFactory.php` — orchestrates providers/routes on top of this builder.
- `Features/Operate/Scope/ScopeManager.php` — manages lifetimes seeded here.

### For Humans: What This Means

Builder + config factory + scope manager form the trio that prepares the container for real workloads.
