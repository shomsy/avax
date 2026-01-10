# RegistryInterface

## Quick Summary

- Defines how services are registered: bind, singleton, scoped, instance, extenders, tags, and contextual bindings.
- Returns fluent builders (`BindingBuilder`, `ContextBuilder`) for structured configuration.
- Exists to separate registration concerns from resolution/runtime concerns.

### For Humans: What This Means (Summary)

It’s the “how you register things” interface.

## Terminology (MANDATORY, EXPANSIVE)

- **Bind**: Register a normal (transient by default) binding.
- **Singleton**: Register a binding that will be cached globally.
- **Scoped**: Register a binding cached within a scope.
- **Extender**: A post-resolution decorator.
- **Contextual binding**: “When X needs Y, give Z.”
- **Tagging**: Grouping services under a label.

### For Humans: What This Means (Terminology)

It defines the ways you teach the container what to build and how to organize it.

## Think of It

Like adding entries to a phonebook and adding special routing rules or stickers (tags).

### For Humans: What This Means (Analogy)

You’re setting up the directory and special cases.

## Story Example

Your app registers `LoggerInterface` as singleton, and adds an extender to wrap it with a profiler in dev. You also define that when `PaymentService` needs `HttpClient`, it should get a special client. Finally, you tag all `Report` services as `reports`.

### For Humans: What This Means (Story)

You can register services, decorate them, add “when X needs Y” rules, and group them with tags.

## For Dummies

- Use `bind/singleton/scoped` to register.
- Use `instance` to register an existing object.
- Use `extend` to decorate.
- Use `tag` to label groups.
- Use `when(...)->needs(...)->give(...)` for special cases.

### For Humans: What This Means (Walkthrough)

It’s a structured way to configure container wiring.

## How It Works (Technical)

This interface is implemented by container builders/stores. The methods typically create definitions in a store and return builder objects that mutate the definition. For batch operations like `tag`, it updates the underlying indices directly.

### For Humans: What This Means (Technical)

Implementations write to the container’s definition store.

## Architecture Role

Used during boot/configuration. Runtime code should not usually depend on it.

### For Humans: What This Means (Architecture)

It’s mostly for startup, not for normal request handling.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods Summary)

When you’re trying to use or debug this file, this is the part you’ll come back to.

### Method: bind(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation (bind)

Registers a transient binding.

##### For Humans: What This Means (bind)

Teach the container what to use for an ID.

##### Parameters (bind)

- `string $abstract`
- `mixed $concrete`

##### Returns (bind)

- `BindingBuilder`

### Method: singleton(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation (singleton)

Registers a shared binding.

##### For Humans: What This Means (singleton)

Register “one shared instance.”

### Method: scoped(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation (scoped)

Registers a scoped binding.

##### For Humans: What This Means (scoped)

Register “one instance per scope.”

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation (instance)

Registers an existing instance.

##### For Humans: What This Means (instance)

Give the container a ready-made object.

### Method: extend(string $abstract, callable $closure): void

#### Technical Explanation (extend)

Registers an extender callable.

##### For Humans: What This Means (extend)

Add a decorator step.

### Method: when(string $consumer): ContextBuilder

#### Technical Explanation (when)

Begins a contextual binding configuration.

##### For Humans: What This Means (when)

Start a “when X needs …” rule.

### Method: tag(string|array $abstracts, string|array $tags): void

#### Technical Explanation (tag)

Batch assigns tags to service identifiers.

##### For Humans: What This Means (tag)

Label one or more services.

## Risks, Trade-offs & Recommended Practices

- **Risk: Runtime registration**. Changing bindings after boot can be dangerous.
- **Practice: Keep registration in boot**. Make configuration deterministic.

### For Humans: What This Means (Risks Summary)

Prefer configuring everything at startup.

## Related Files & Folders

- `docs/Features/Core/Contracts/BindingBuilder.md`: Fluent builder.
- `docs/Features/Core/Contracts/ContextBuilder.md`: Contextual binding builder.
- `docs/Features/Define/Store/DefinitionStore.md`: Where definitions are stored.

### For Humans: What This Means (Relationships)

Registry is the API; DefinitionStore is where the data ends up.
