# BindingBuilder

## Quick Summary
- This file implements a fluent builder that refines a stored service definition.
- It exists so you can configure bindings without needing to understand `ServiceDefinition` internals.
- It removes the complexity of “where do I store this metadata?” by writing directly into `DefinitionStore`.

### For Humans: What This Means
You get a nice, chainable API (`->to()->tag()->withArgument()`) instead of manually editing the container’s internal registry.

## Terminology (MANDATORY, EXPANSIVE)
- **Binding**: A rule that maps an identifier (often an interface) to a concrete implementation.
  - In this file: the builder edits the binding by updating the underlying `ServiceDefinition`.
  - Why it matters: bindings are how the container knows what to instantiate.
- **Abstract**: The “service id” you ask the container for.
  - In this file: `$abstract` is the key used to look up the stored definition.
  - Why it matters: it’s the stable handle used across the container.
- **Concrete**: The actual class/factory/instance that fulfills the abstract.
  - In this file: `to()` sets `$definition->concrete`.
  - Why it matters: this decides what object is created.
- **DefinitionStore**: The central registry holding definitions, tags, contextual rules, extenders.
  - In this file: it’s the storage backing the builder’s changes.
  - Why it matters: the builder is only useful if it persists changes somewhere authoritative.
- **Tag**: A label used to group services for batch operations.
  - In this file: `tag()` records tag-to-service mappings in the store.
  - Why it matters: tags enable “give me all middleware” patterns.
- **Argument override**: A named constructor parameter you force to a specific value.
  - In this file: `withArgument()` writes into `$definition->arguments`.
  - Why it matters: you can inject scalars/config without custom factories.

### For Humans: What This Means
This builder is basically a “settings panel” for one service registration: what it points to, what labels it has, and which constructor knobs you want to lock in.

## Think of It
Think of `BindingBuilder` like customizing a contact in your phone: you pick their main number (`to()`), add groups (`tag()`), and add notes for special handling (`withArgument()`).

### For Humans: What This Means
It’s not doing the calling—it’s just saving the correct contact info so calling later is effortless.

## Story Example
You have `LoggerInterface`, but the default autowiring isn’t enough because you need a file path scalar. You bind the interface and override the `$path` argument in one chain. The container later resolves the service using those stored choices.

### For Humans: What This Means
Instead of building a factory class, you just “tell the container the truth” once.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You create a service definition (usually via `Registrar`).
2. You get back a `BindingBuilder`.
3. Every call you make updates the stored definition.
4. Later, the runtime engine reads the stored definition and does the heavy lifting.

Common misconceptions:
- “This builder creates the service.” It doesn’t—it only edits metadata.
- “Tags are magic.” They’re just labels stored in the registry.

Beginner FAQ:
- *Can I call `to()` multiple times?* Yes; last write wins because it updates the stored definition.
- *Does `withArguments()` replace or merge?* It merges by repeatedly calling `withArgument()`.

## How It Works (Technical)
`BindingBuilder` stores two things: a reference to `DefinitionStore` and the `$abstract` id. Each fluent method (`to`, `tag`, `withArgument`) looks up the definition from the store and mutates it, or delegates to store helpers (tagging). The builder returns itself so your code reads as a chain.

### For Humans: What This Means
It’s a tiny object that remembers “which service you’re editing” and then edits it whenever you call a method.

## Architecture Role
- Why it lives in this folder: it’s part of the public-friendly “Define” DSL.
- What depends on it: registration workflows (`Registrar`) and user bootstrapping code.
- What it depends on: `DefinitionStore` and `ServiceDefinition` as storage model.
- System-level reasoning: it keeps a clean separation between “declaration” and “execution”.

### For Humans: What This Means
This class is your “nice interface” to the container’s internal notebook.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Creates a builder bound to a specific service id and store.

##### For Humans: What This Means
It’s like opening the settings page for one service.

##### Parameters
- `DefinitionStore $store`: Where your changes get saved.
- `string $abstract`: Which service you’re editing.

##### Returns
- Returns nothing; it sets up the builder.

##### Throws
- No explicit exceptions.

##### When to Use It
- You don’t call it directly; `Registrar` typically does.

##### Common Mistakes
- Creating builders without first registering a definition in the store.

### Method: to(…)

#### Technical Explanation
Sets the concrete implementation/factory/nullable value for the service definition stored under `$abstract`.

##### For Humans: What This Means
You’re saying “when I ask for X, actually build Y”.

##### Parameters
- `string|callable|null $concrete`: The implementation (or factory) to use; `null` means “autowire”.

##### Returns
- Returns `$this` to continue the fluent chain.

##### Throws
- No explicit exceptions.

##### When to Use It
- When the container can’t guess the right class or you want a factory.

##### Common Mistakes
- Passing a built instance when you intended a factory closure.

### Method: tag(…)

#### Technical Explanation
Adds one or more tags for the service by delegating to the store’s tag indexing.

##### For Humans: What This Means
You’re putting the service into a labeled bucket for later bulk operations.

##### Parameters
- `string|string[] $tags`: One tag or many tags.

##### Returns
- Returns `$this`.

##### Throws
- No explicit exceptions.

##### When to Use It
- When you want “all services with tag X”.

##### Common Mistakes
- Using tags for uniqueness; tags are grouping, not identity.

### Method: withArguments(…)

#### Technical Explanation
Convenience helper that applies multiple named argument overrides by calling `withArgument()` repeatedly.

##### For Humans: What This Means
It’s a shortcut so you don’t write the same call many times.

##### Parameters
- `array $arguments`: A map of `name => value`.

##### Returns
- Returns `$this`.

##### Throws
- No explicit exceptions.

##### When to Use It
- When you have multiple scalar/config overrides to apply.

##### Common Mistakes
- Using numeric keys and expecting positional mapping; this builder uses parameter names.

### Method: withArgument(…)

#### Technical Explanation
Stores a single named constructor argument override into the `ServiceDefinition` for `$abstract`.

##### For Humans: What This Means
You’re forcing a constructor parameter to a specific value.

##### Parameters
- `string $name`: The constructor parameter name.
- `mixed $value`: The injected value.

##### Returns
- Returns `$this`.

##### Throws
- No explicit exceptions.

##### When to Use It
- When a dependency can’t be resolved by type (like a string path).

##### Common Mistakes
- Misspelling the parameter name; the override won’t match at runtime.

## Risks, Trade-offs & Recommended Practices
- Risk: Named argument overrides can silently drift if constructor parameter names change.
  - Why it matters: your configuration becomes stale and injection can break.
  - Design stance: prefer type-based injection; use overrides sparingly.
  - Recommended practice: keep overrides close to the binding and cover with tests.

### For Humans: What This Means
Overrides are powerful, but they’re also “stringly-typed”. Use them like spices, not as the main ingredient.

## Related Files & Folders
- `docs_md/Features/Define/Bind/Registrar.md`: Creates definitions and hands you this builder.
- `docs_md/Features/Define/Store/DefinitionStore.md`: The place your changes are persisted.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: The data structure you’re modifying.

### For Humans: What This Means
If you want to understand “where the changes go”, follow the chain from builder → store → definition.

