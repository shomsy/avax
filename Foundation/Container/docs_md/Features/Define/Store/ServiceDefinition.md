# ServiceDefinition

## Quick Summary
- This file defines a lightweight DTO that describes how a service should be built and managed.
- It exists so the container can treat registrations as structured data instead of ad-hoc arrays.
- It removes the complexity of “how do I carry binding metadata across phases?” by being the shared blueprint between registration and resolution.

### For Humans: What This Means
This is the container’s “service card”: it says what the service is, how long it should live, and what special settings it has.

## Terminology (MANDATORY, EXPANSIVE)
- **DTO (Data Transfer Object)**: A simple object whose job is to carry data.
  - In this file: `ServiceDefinition` exposes public properties for speed and simplicity.
  - Why it matters: the container can pass definitions around without heavy abstractions.
- **Abstract**: The id you register and later ask the container for.
  - In this file: stored in `$abstract`.
  - Why it matters: it’s the primary key.
- **Concrete**: What the abstract points to (class name, factory closure, or instance).
  - In this file: stored in `$concrete`.
  - Why it matters: it decides what gets created.
- **Lifetime**: How instances are reused.
  - In this file: stored in `$lifetime`.
  - Why it matters: it controls reuse and scope boundaries.
- **Tags**: Labels for grouping services.
  - In this file: stored in `$tags`.
  - Why it matters: enables batch discovery and resolution patterns.
- **Arguments**: Named constructor argument overrides.
  - In this file: stored in `$arguments`.
  - Why it matters: enables scalar/config injection without factories.
- **Serialization hydration**: Reconstructing the object from stored data.
  - In this file: supported by `fromArray()` and `__set_state()`.
  - Why it matters: caching/compilation can restore definitions efficiently.

### For Humans: What This Means
Everything the container needs to “remember” about a service is stored here, in plain and readable fields.

## Think of It
Think of `ServiceDefinition` like a library catalog card for a book: title (abstract), where to find it (concrete), and special notes (tags, arguments, lifetime).

### For Humans: What This Means
It’s not the service itself—it’s the metadata that helps you find and use it correctly.

## Story Example
You register `MailerInterface` as a singleton and tag it as `infrastructure`. The container stores that information in a `ServiceDefinition`. Later, the resolver uses the lifetime to decide reuse behavior, and a tooling command can list all `infrastructure` services by reading tags.

### For Humans: What This Means
A single data object becomes the shared “truth” used by many different container features.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- A definition is created during registration.
- It’s stored in `DefinitionStore`.
- The engine reads it during resolution.
- It can be exported and re-imported for caching/compilation.

Beginner FAQ:
- *Why public properties?* For fast access and simpler serialization.
- *Is it safe to mutate?* During registration, yes. After bootstrapping, you should treat it as read-only configuration.

## How It Works (Technical)
The class stores core metadata: abstract id, concrete spec, lifetime enum, tags, and argument overrides. For caching/compilation, `toArray()` produces a serializable representation, while `fromArray()` and `__set_state()` reconstruct the object, including converting `ServiceLifetime` correctly.

### For Humans: What This Means
It’s designed to be both fast at runtime and easy to save/restore.

## Architecture Role
- Why it lives in this folder: it’s a core part of the definition store model.
- What depends on it: `DefinitionStore`, builders, and runtime resolution.
- What it depends on: `ServiceLifetime`.
- System-level reasoning: a stable shared model reduces coupling and makes the container’s phases composable.

### For Humans: What This Means
This is the “shared language” between the part that registers services and the part that builds them.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Initializes the definition with the required abstract id.

##### For Humans: What This Means
You can’t have a service definition without naming the service.

##### Parameters
- `string $abstract`: The identifier you will later resolve.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When you register a new service.

##### Common Mistakes
- Using unstable identifiers that change frequently.

### Method: __set_state(…)

#### Technical Explanation
Hydrates a definition from an array produced by `var_export()`.

##### For Humans: What This Means
It’s a way for cached PHP files to rebuild the object quickly.

##### Parameters
- `array $array`: Exported properties.

##### Returns
- `self`: A rehydrated `ServiceDefinition`.

##### Throws
- No explicit exceptions.

##### When to Use It
- You don’t call it manually; PHP calls it during `var_export` hydration.

##### Common Mistakes
- Assuming it validates everything; it trusts the data shape.

### Method: fromArray(…)

#### Technical Explanation
Builds a `ServiceDefinition` from a plain array, including lifetime conversion.

##### For Humans: What This Means
It turns stored data back into a real definition object.

##### Parameters
- `array $data`: Definition data (abstract, concrete, lifetime, tags, arguments).

##### Returns
- `self`

##### Throws
- Potentially throws if `ServiceLifetime::from()` receives invalid values (depends on enum behavior).

##### When to Use It
- During cache hydration or compilation.

##### Common Mistakes
- Providing a lifetime that isn’t a valid enum value.

### Method: toArray(…)

#### Technical Explanation
Serializes the definition to a plain array suitable for caching.

##### For Humans: What This Means
It’s the “pack it into a box” method.

##### Parameters
- No parameters.

##### Returns
- `array`: A serializable representation.

##### Throws
- No explicit exceptions.

##### When to Use It
- When you cache/compile container definitions.

##### Common Mistakes
- Forgetting that `$concrete` might be a closure and not serializable in all contexts.

## Risks, Trade-offs & Recommended Practices
- Risk: Storing closures in `$concrete` can complicate caching/serialization.
  - Why it matters: closures can’t always be exported safely.
  - Design stance: prefer class names for production caching; use factories with care.
  - Recommended practice: keep compiled container mode compatible with your concrete types.

### For Humans: What This Means
If you want “save to disk and reload”, avoid configurations that can’t be safely saved.

## Related Files & Folders
- `docs_md/Features/Define/Store/DefinitionStore.md`: Stores many definitions.
- `docs_md/Features/Core/Enum/ServiceLifetime.md`: Defines lifetimes.
- `docs_md/Features/Define/Bind/BindingBuilder.md`: Edits definitions fluently.

### For Humans: What This Means
To understand a definition, you also need to understand the store and the lifetime rules.

