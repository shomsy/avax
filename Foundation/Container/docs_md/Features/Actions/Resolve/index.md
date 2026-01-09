# Features/Actions/Resolve

## What This Folder Represents
This folder contains the “resolution engine” actions: logic that takes a `KernelContext` (service ID + overrides + parent chain) and produces a resolved instance/value. It exists to separate the *core resolution algorithm* from the kernel pipeline orchestration.

### For Humans: What This Means
This is where the container actually decides what to return for a service ID—build a class, run a factory closure, return a literal, or delegate to another binding.

## What Belongs Here
- `Engine`: The core instantiation-focused engine that follows definitions and autowires.
- `DependencyResolver`: Helper that resolves method/constructor parameters.
- Contracts under `Contracts/` used by kernel steps and other actions.

### For Humans: What This Means
If it decides how to resolve a service or its constructor/method parameters, it belongs here.

## What Does NOT Belong Here
- Lifecycle caching (that’s kernel lifecycle steps and strategies).
- Injection after construction (that’s `Features/Actions/Inject`).
- Definition storage itself (that’s `Features/Define/Store`).

### For Humans: What This Means
This folder builds/returns instances; it doesn’t store them, and it doesn’t inject into them.

## How Files Collaborate
The kernel pipeline calls `EngineInterface::resolve` via `ResolveInstanceStep`. The `Engine` consults `DefinitionStore` for bindings and contextual rules, optionally delegates resolution through the container, and uses `Instantiator` to build classes. When methods or constructors need parameter values, `DependencyResolver` resolves them using overrides and the container while preserving context.

### For Humans: What This Means
The kernel asks the engine for an instance. The engine checks definitions and can autowire. The instantiator builds objects. The dependency resolver fills constructor/method arguments.
