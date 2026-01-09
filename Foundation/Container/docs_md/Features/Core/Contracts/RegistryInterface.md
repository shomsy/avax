# RegistryInterface

## Quick Summary
- Defines how services are registered: bind, singleton, scoped, instance, extenders, and contextual bindings.
- Returns fluent builders (`BindingBuilder`, `ContextBuilder`) for structured configuration.
- Exists to separate registration concerns from resolution/runtime concerns.

### For Humans: What This Means
It’s the “how you register things” interface.

## Terminology
- **Bind**: Register a normal (transient by default) binding.
- **Singleton**: Register a binding that will be cached globally.
- **Scoped**: Register a binding cached within a scope.
- **Extender**: A post-resolution decorator.
- **Contextual binding**: “When X needs Y, give Z.”

### For Humans: What This Means
It defines the ways you teach the container what to build.

## Think of It
Like adding entries to a phonebook and adding special routing rules.

### For Humans: What This Means
You’re setting up the directory and special cases.

## Story Example
Your app registers `LoggerInterface` as singleton, and adds an extender to wrap it with a profiler in dev. You also define that when `PaymentService` needs `HttpClient`, it should get a special client.

### For Humans: What This Means
You can register services, decorate them, and add “when X needs Y” rules.

## For Dummies
- Use `bind/singleton/scoped` to register.
- Use `instance` to register an existing object.
- Use `extend` to decorate.
- Use `when(...)->needs(...)->give(...)` to define contextual binding.

### For Humans: What This Means
It’s a structured way to configure container wiring.

## How It Works (Technical)
This interface is implemented by container builders/stores. The methods typically create definitions in a store and return builder objects that mutate the definition.

### For Humans: What This Means
Implementations write to the container’s definition store.

## Architecture Role
Used during boot/configuration. Runtime code should not usually depend on it.

### For Humans: What This Means
It’s mostly for startup, not for normal request handling.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: bind(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a binding.

##### For Humans: What This Means
Teach the container what to use for an ID.

##### Parameters
- `string $abstract`
- `mixed $concrete`

##### Returns
- `BindingBuilder`

##### Throws
- Registration errors.

##### When to Use It
Default registration.

##### Common Mistakes
Registering objects as concretes instead of instances.

### Method: singleton(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a shared binding.

##### For Humans: What This Means
Register “one shared instance.”

##### Parameters
- `string $abstract`
- `mixed $concrete`

##### Returns
- `BindingBuilder`

##### Throws
- Registration errors.

##### When to Use It
Shared infrastructure.

##### Common Mistakes
Using for mutable per-request state.

### Method: scoped(string $abstract, mixed $concrete = null): BindingBuilder

#### Technical Explanation
Registers a scoped binding.

##### For Humans: What This Means
Register “one instance per scope.”

##### Parameters
- `string $abstract`
- `mixed $concrete`

##### Returns
- `BindingBuilder`

##### Throws
- Registration errors.

##### When to Use It
Per-request state.

##### Common Mistakes
Forgetting to end scopes.

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation
Registers an existing instance.

##### For Humans: What This Means
Give the container a ready-made object.

##### Parameters
- `string $abstract`
- `object $instance`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Bridging legacy objects.

##### Common Mistakes
Registering instances too early before configuration is complete.

### Method: extend(string $abstract, callable $closure): void

#### Technical Explanation
Registers an extender callable.

##### For Humans: What This Means
Add a decorator step.

##### Parameters
- `string $abstract`
- `callable $closure`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Decorating services.

##### Common Mistakes
Mutating shared instances unexpectedly.

### Method: when(string $consumer): ContextBuilder

#### Technical Explanation
Begins a contextual binding configuration.

##### For Humans: What This Means
Start a “when X needs …” rule.

##### Parameters
- `string $consumer`

##### Returns
- `ContextBuilder`

##### Throws
- None.

##### When to Use It
Context-specific wiring.

##### Common Mistakes
Using consumer IDs inconsistently.

## Risks, Trade-offs & Recommended Practices
- **Risk: Runtime registration**. Changing bindings after boot can be dangerous.
- **Practice: Keep registration in boot**. Make configuration deterministic.

### For Humans: What This Means
Prefer configuring everything at startup.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/BindingBuilder.md`: Fluent builder.
- `docs_md/Features/Core/Contracts/ContextBuilder.md`: Contextual binding builder.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Where definitions are stored.

### For Humans: What This Means
Registry is the API; DefinitionStore is where the data ends up.
