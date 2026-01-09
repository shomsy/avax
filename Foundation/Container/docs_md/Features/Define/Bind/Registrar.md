# Registrar

## Quick Summary
- This file encapsulates the container’s service registration operations: bind, singleton, scoped, instance, when.
- It exists so you have a single, consistent place that creates definitions and returns builders.
- It removes the complexity of “how do I create a correct `ServiceDefinition`?” by centralizing that logic.

### For Humans: What This Means
`Registrar` is the clerk at the front desk: you tell it what you want registered, and it fills in the paperwork correctly.

## Terminology (MANDATORY, EXPANSIVE)
- **Registration**: The act of telling the container about a service.
  - In this file: registration creates and stores `ServiceDefinition` objects.
  - Why it matters: resolution can only be correct if registration is correct.
- **Lifetime**: The reuse policy for instances (singleton/scoped/transient).
  - In this file: chosen per method (`bind`, `singleton`, `scoped`, `instance`).
  - Why it matters: lifetime controls performance and correctness of stateful services.
- **DefinitionStore**: The registry that holds all definitions and indices.
  - In this file: it’s where definitions are added.
  - Why it matters: it’s the container’s “memory” of what you registered.
- **BindingBuilder / ContextBuilder**: Fluent builder objects returned for further configuration.
  - In this file: returned from registration methods.
  - Why it matters: they make configuration readable and keep the store’s internals hidden.

### For Humans: What This Means
You call simple methods like `singleton()` and you get back a “settings page” to keep configuring.

## Think of It
Think of `Registrar` like a passport office. You can apply for different document types (transient, scoped, singleton), and the office issues the correct paperwork, then lets you add extra details.

### For Humans: What This Means
It makes sure your registrations are “official” and consistent.

## Story Example
Your application bootstraps by registering a few core services. You don’t want every provider to know about `ServiceDefinition` internals, so you expose `Registrar` methods. Providers call `singleton(...)` and then refine the binding using the returned builder.

### For Humans: What This Means
You keep registration code clean and predictable, and you avoid copying the same setup logic everywhere.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You call `bind()` / `singleton()` / `scoped()`.
2. `Registrar` creates a `ServiceDefinition` with the correct lifetime.
3. It stores the definition in `DefinitionStore`.
4. It returns a `BindingBuilder` so you can add tags or override arguments.
5. For contextual rules, you call `when()` and use `ContextBuilder`.

## How It Works (Technical)
All registration methods funnel into a private `register()` helper: it creates a `ServiceDefinition`, sets `concrete` and `lifetime`, stores it, then returns a `BindingBuilder` pointing at the new definition. `instance()` is a special case: it registers an already-created object as a singleton. `when()` creates a `ContextBuilder` for contextual overrides.

### For Humans: What This Means
There’s one “real” implementation, and the public methods are just friendly aliases for common lifetimes.

## Architecture Role
- Why it lives in this folder: it’s part of the registration DSL.
- What depends on it: bootstrappers and providers.
- What it depends on: `DefinitionStore`, `ServiceDefinition`, and builders.
- System-level reasoning: centralizing registration reduces drift and inconsistent container behavior.

### For Humans: What This Means
One place to register services means fewer surprises later when the container starts building them.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Accepts the `DefinitionStore` used for all registrations.

##### For Humans: What This Means
You’re giving the registrar the notebook where it will write registrations.

##### Parameters
- `DefinitionStore $definitions`: The central definition registry.

##### Returns
- No return value.

##### Throws
- No explicit exceptions.

##### When to Use It
- Usually constructed inside container bootstrap code.

##### Common Mistakes
- Using multiple different stores unintentionally (splitting registrations).

### Method: bind(…)

#### Technical Explanation
Registers a transient service (new instance per resolution) and returns a `BindingBuilder`.

##### For Humans: What This Means
You’re saying “don’t reuse this; build a fresh one each time.”

##### Parameters
- `string $abstract`: The service id.
- `mixed $concrete`: Class name, closure, or null.

##### Returns
- `BindingBuilderInterface`: A builder to refine the binding.

##### Throws
- No explicit exceptions.

##### When to Use It
- Stateless services and lightweight objects.

##### Common Mistakes
- Using transient for stateful services you expected to be shared.

### Method: singleton(…)

#### Technical Explanation
Registers a singleton service (one instance for the container lifetime) and returns a `BindingBuilder`.

##### For Humans: What This Means
You’re saying “make one and keep it.”

##### Parameters
- `string $abstract`
- `mixed $concrete`

##### Returns
- `BindingBuilderInterface`

##### Throws
- No explicit exceptions.

##### When to Use It
- Expensive services (DB connection factories, configuration objects).

##### Common Mistakes
- Making request-specific state singletons.

### Method: scoped(…)

#### Technical Explanation
Registers a scoped service (one instance per scope boundary) and returns a `BindingBuilder`.

##### For Humans: What This Means
You’re saying “reuse it, but only inside the same request/session scope.”

##### Parameters
- `string $abstract`
- `mixed $concrete`

##### Returns
- `BindingBuilderInterface`

##### Throws
- No explicit exceptions.

##### When to Use It
- Request-bound services like “current user” or per-request caches.

##### Common Mistakes
- Forgetting to start/end scopes, then wondering why instances stick around.

### Method: instance(…)

#### Technical Explanation
Registers a pre-built instance as a singleton definition.

##### For Humans: What This Means
You already have an object; you want the container to hand out that exact object.

##### Parameters
- `string $abstract`: The service id.
- `mixed $instance`: The actual object/value.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Bridging existing objects into the container (legacy integration).

##### Common Mistakes
- Registering mutable global state as an instance without clear ownership.

### Method: when(…)

#### Technical Explanation
Creates a contextual binding builder for a consumer class.

##### For Humans: What This Means
You’re starting a sentence: “When this class needs something…”

##### Parameters
- `string $consumer`: The consumer class id.

##### Returns
- `ContextBuilderInterface`

##### Throws
- No explicit exceptions.

##### When to Use It
- When a single consumer needs a special implementation.

##### Common Mistakes
- Using contextual rules as the default configuration mechanism.

## Risks, Trade-offs & Recommended Practices
- Risk: Registration is mutable state; doing it late can cause confusing runtime behavior.
  - Why it matters: different parts of the app might see different bindings.
  - Design stance: register everything during a dedicated bootstrapping phase.
  - Recommended practice: keep registration code deterministic and centralized.

### For Humans: What This Means
Treat registrations like “startup configuration”, not something you change while the app is running.

## Related Files & Folders
- `docs_md/Features/Define/Store/DefinitionStore.md`: The store `Registrar` writes into.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: The blueprint it creates.
- `docs_md/Features/Define/Bind/BindingBuilder.md`: The fluent builder you get back.
- `docs_md/Features/Define/Bind/ContextBuilder.md`: The contextual override builder.

### For Humans: What This Means
If you want to understand registration, follow the path: registrar → definition → store.

