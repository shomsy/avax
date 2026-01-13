# Features/Define/Bind

## What This Folder Represents

This folder is the fluent “binding DSL” of the container.

Technically, `Features/Define/Bind` contains builder objects that let you register services in a readable,
intention-revealing way. These builders don’t perform resolution; they mutate the `DefinitionStore` by creating or
refining `ServiceDefinition` objects and by adding contextual rules. The goal is a developer-friendly API that stays
decoupled from the store’s internal layout.

### For Humans: What This Means (Represent)

This is the part where you get to write configuration in a way that feels like English, not like poking arrays.

## What Belongs Here

- Fluent builders like `BindingBuilder` and `ContextBuilder`.
- Small orchestration helpers like `Registrar` that create definitions and return builders.

### For Humans: What This Means (Belongs)

If you call `$container->bind()->to()->tag()` style APIs, the “speech translator” for those calls lives here.

## What Does NOT Belong Here

- Definition persistence details and indices (those live in `Features/Define/Store`).
- Resolution and instantiation steps (those live under `Core/Kernel` and `Features/Actions`).

### For Humans: What This Means (Not Belongs)

Builders are for *writing down rules*, not for *executing rules*.

## How Files Collaborate

`Registrar` creates initial `ServiceDefinition` objects and stores them in `DefinitionStore`. `BindingBuilder` then
refines the created definition (concrete implementation, tags, argument overrides). `ContextBuilder` writes contextual
overrides into the store using a two-step “needs → give” flow.

### For Humans: What This Means (Collaboration)

`Registrar` starts the sentence, `BindingBuilder` finishes it, and `ContextBuilder` handles “except when…” special
cases.

