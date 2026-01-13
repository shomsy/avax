# Features/Core/Contracts

## What This Folder Represents

The public contracts that define the container’s core capabilities: registration, resolution, injection, compilation,
and contextual binding. They exist to decouple higher-level code from concrete implementations.

### For Humans: What This Means (Represent)

These interfaces are the “shape” of the container. You can swap implementations as long as they match these shapes.

## What Belongs Here

Interfaces like `ContainerInterface`, `RegistryInterface`, `ResolverInterface`, `InjectorInterface`,
`CompilerInterface`, and builder contracts.

### For Humans: What This Means (Belongs)

If it’s a core interface that other layers depend on, it belongs here.

## What Does NOT Belong Here

Concrete classes and heavy logic.

### For Humans: What This Means (Not Belongs)

No implementations here—only agreements.

## How Files Collaborate

The kernel and feature actions depend on these contracts so they can be tested and evolved without rewriting the entire
container. Builder interfaces make registration fluent and structured.

### For Humans: What This Means (Collaboration)

The rest of the system talks to these interfaces so it doesn’t care which implementation is underneath.
