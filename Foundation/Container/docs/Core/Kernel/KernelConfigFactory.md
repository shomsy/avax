# KernelConfigFactory

## Quick Summary

- Technical: Produces `KernelConfig` objects with consistent defaults and optional overrides for strict/auto-define/dev
  flags plus optional collaborators.
- Centralizes KernelConfig creation so other builders don’t duplicate flag wiring and can opt into explicit profiles
  when debug isn’t enough.

### For Humans: What This Means

It’s the preset dial for the kernel: you pass debug or explicit overrides, it sets the right switches and hands back a
config with optional metrics/policy/shutdown hooks.

## Terminology

- **KernelConfig**: Configuration describing how the container kernel resolves, injects, and manages scopes.
- **Strict Mode**: Refuses missing definitions; defaults to the value of the debug flag unless overridden.
- **Auto-define**: Allows transient auto-wired definitions; defaults to the inverse of strictness unless overridden.
- **EngineInterface**: Contract the resolution engine must satisfy; lets you swap engines without changing the factory
  API.
- **Dev Mode**: Enables development-only helpers/features; defaults to the debug flag unless overridden.

### For Humans: What This Means

Strict mode is the “no surprises” setting; auto-define is the “help me wire unknowns” setting; dev mode is the “extra
diagnostics” setting. You can keep the debug presets or override each one explicitly.

## Think of It

Like a profile selector (dev vs prod) for a car’s traction control with manual overrides: flip the main switch or tweak
individual assists to fit the road.

### For Humans: What This Means

Pick debug on/off and the factory sets the matching safety/performance profile, or override individual assists when you
need a custom mix.

## Story Example

```php
use Avax\Container\Core\Kernel\KernelConfigFactory;

$config = (new KernelConfigFactory())->create(
    engine: $engine,
    injector: $injector,
    invoker: $invoker,
    scopes: $scopes,
    prototypeFactory: $prototypeFactory,
    timeline: $timeline,
    metrics: null,
    policy: null,
    terminator: null,
    debug: false,
    strictMode: true,   // strict prod without dev features
    autoDefine: false,  // disable permissive autowiring in prod
    devMode: false
);
```

### For Humans: What This Means

You pass in the moving parts, can skip optional collaborators, and choose either the debug preset or explicit overrides
for strictness, autodefine, and dev helpers.

## For Dummies

1. Build engine, injector, invoker, scopes, prototype factory, timeline, and optional metrics/policy/terminator.
2. Call `create(..., debug: $debug)` or supply explicit `strictMode/autoDefine/devMode` overrides.
3. It returns `KernelConfig` with the flags resolved from your choices.

### For Humans: What This Means

Give it the parts and either a simple debug flag or detailed overrides; it sets the switches and hands back a ready
config.

## How It Works (Technical)

- Accepts all collaborators required by `KernelConfig` and lets metrics/policy/terminator be omitted.
- Resolves flags using `$strictMode ?? $debug`, `$autoDefine ?? ! $strictMode`, and `$devMode ?? $debug`.
- Returns a new `KernelConfig` instance using the provided collaborators and computed flags.

### For Humans: What This Means

It doesn’t invent new parts; it just wires the flags consistently, and you can keep defaults or override them without
editing callers.

## Architecture Role

- Lives in `Core/Kernel/` to keep config creation consistent across builders.
- Used by `ContainerBuilder` (and any future builders) to avoid duplicating flag logic while allowing explicit profiles.

### For Humans: What This Means

One place defines how the kernel is configured, keeping every entrypoint aligned and letting you opt into custom
strict/dev/autodefine mixes.

## Methods

### Method: create(..., bool $debug = false, ?bool $strictMode = null, ?bool $autoDefine = null, ?bool $devMode = null): KernelConfig {#method-create}

Technical: Builds `KernelConfig` with provided collaborators (metrics/policy/terminator optional) and resolves flags
from debug or explicit overrides.

### For Humans: What This Means

Pass components, choose debug on/off or override each flag; get a kernel config tuned for your chosen profile.

#### Parameters

- EngineInterface, InjectDependencies, InvokeAction, ScopeManager, ServicePrototypeFactory, ResolutionTimeline, optional
  CollectMetrics, optional ContainerPolicy, optional TerminateContainer, `bool $debug`, optional `bool $strictMode`,
  optional `bool $autoDefine`, optional `bool $devMode`.

#### Returns

- `KernelConfig` ready for use by `ContainerKernel`.

#### Throws

- None directly; invalid collaborator wiring will surface later during kernel use.

#### When to use it

- Any time you need a `KernelConfig` without rewriting flag logic, including strict-prod or custom profiles beyond the
  debug preset.

#### Common mistakes

- Forgetting to set explicit overrides when you need strict-prod without dev features; passing a concrete engine that
  doesn’t implement EngineInterface.

## Risks & Trade-offs

Technical: Centralizes flag logic—misconfiguration here affects all consumers; explicit overrides increase flexibility
but require deliberate choices.

### For Humans: What This Means

If you set the wrong flags here, every entrypoint inherits them; double-check the profile you want.

## Related Files & Folders

Technical:

- `Core/ContainerBuilder.php` — primary consumer.
- `Core/Kernel/KernelConfig.php` — object produced by this factory.
- `Core/AppFactory.php` — indirectly depends on consistent configs through the builder.

### For Humans: What This Means

Builder asks this factory for configs; kernel consumes them; AppFactory relies on the builder’s correctness and the
chosen profile.
