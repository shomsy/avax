# EnsureDefinitionExistsStep

## Quick Summary
- Ensures there’s a `ServiceDefinition` available in the context before resolution continues.
- Pulls definitions from `DefinitionStore` when present, or creates an ephemeral definition for auto-wiring when allowed.
- In strict mode, throws when a definition is missing and auto-define can’t apply.

### For Humans: What This Means (Summary)
It makes sure the container has a “service recipe” to follow. If it can’t find one, it can optionally make a temporary recipe (auto-define) or fail fast in strict mode.

## Terminology (MANDATORY, EXPANSIVE)- **DefinitionStore**: Global registry of service definitions.
- **ServiceDefinition**: The “recipe” describing how to build a service (abstract, concrete, lifetime, extenders, etc.).
- **Auto-define**: Creating a temporary definition for classes that can be reflected and auto-wired.
- **Ephemeral definition**: A definition stored only in the current context (not persisted globally).
- **Strict mode**: A mode that turns missing definitions into immediate resolution errors.

### For Humans: What This Means
DefinitionStore is the recipe book; ServiceDefinition is a recipe; auto-define writes a temporary recipe; strict mode refuses to cook without an official recipe.

## Think of It
Like a kitchen prep station: before cooking starts, you must have a recipe card. If there’s no recipe, you can either improvise (auto-define) or stop and ask for the recipe (strict mode).

### For Humans: What This Means (Think)
No recipe means you either improvise safely or you stop early so you don’t cook random nonsense.

## Story Example
A developer resolves a class directly by FQCN without registering it. With auto-define on, this step detects that the ID is an instantiable class and creates an ephemeral transient definition so auto-wiring can proceed. With strict mode on, missing definitions cause an exception with a clear message.

### For Humans: What This Means (Story)
If you forget to register a class, auto-define can still make it work, but strict mode forces you to be explicit.

## For Dummies
1. If you’re resolving an injection target, skip (it’s handled elsewhere).
2. If the store has a definition, copy it into context metadata.
3. If the store doesn’t have it:
   - If auto-define is enabled and the service ID is an instantiable class, create a temporary `ServiceDefinition` with transient lifetime.
   - Otherwise, mark the definition as missing in metadata and optionally throw if strict mode is enabled.

Common misconceptions:
- “Auto-define registers it globally.” It doesn’t; it’s context-only.
- “Missing definition always fails.” Not if you treat the ID as a literal or enable auto-define.

### For Humans: What This Means (Dummies)
Auto-define is a temporary safety net, not a permanent registration. Missing definitions can still work for literals, but strict mode won’t allow it.

## How It Works (Technical)
`__invoke` checks for a definition in `DefinitionStore`. If found, it stores it in context metadata. If not found, and auto-define is enabled, it checks instantiability via `ReflectionClass` and creates an ephemeral `ServiceDefinition` with transient lifetime. Otherwise it stores warning metadata and optionally throws a resolution exception in strict mode.

### For Humans: What This Means (How)
It either pulls a real definition, creates a temporary one for auto-wiring, or records “missing” and optionally stops.

## Architecture Role
Runs early in the pipeline to ensure downstream steps (analysis, injection, lifecycle handling) have definition metadata to rely on. Depends on `DefinitionStore` and uses reflection only for auto-define checks.

### For Humans: What This Means (Role)
It’s the “do we have a recipe?” step that prevents later steps from working with missing or unknown definition state.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)
Populates `definition.instance` and `definition.source` metadata from the store, or creates an ephemeral transient definition for auto-wireable classes, or records missing metadata and throws in strict mode.

##### For Humans: What This Means (__invoke)
It makes sure the context knows what definition to use, or it fails early if it can’t.

##### Parameters (__invoke)
- `KernelContext $context`: Holds service ID and metadata.

##### Returns (__invoke)
- `void`

##### Throws (__invoke)
- `ResolutionException` (strict mode) when a definition is missing and can’t be auto-defined.

##### When to Use It (__invoke)
Invoked automatically near the start of resolution.

##### Common Mistakes (__invoke)
- Enabling auto-define and expecting persistent registration.
- Using strict mode without registering all required services.

### Method: isAutoDefinable(string $serviceId)

#### Technical Explanation (isAutoDefinable)
Determines whether the service ID is an instantiable class by checking `class_exists` and `ReflectionClass::isInstantiable()`.

##### For Humans: What This Means (isAutoDefinable)
It checks whether the service ID is a real, buildable class.

##### Parameters (isAutoDefinable)
- `string $serviceId`: Candidate service identifier.

##### Returns (isAutoDefinable)
- `bool`: True when the service can be auto-defined.

##### Throws (isAutoDefinable)
- None (reflection errors are caught and treated as false).

##### When to Use It (isAutoDefinable)
Internal helper when auto-define is enabled.

##### Common Mistakes (isAutoDefinable)
Assuming interfaces/abstract classes are auto-definable—they aren’t.

## Risks, Trade-offs & Recommended Practices
- **Risk: Silent missing definitions**. Without strict mode, missing definitions can slip through until later; consider strict mode in production.
- **Trade-off: Auto-define convenience vs explicitness**. Auto-define is convenient but can hide missing registration mistakes.
- **Practice: Use strict mode in CI**. Catch missing definitions early.
- **Practice: Keep auto-define scoped**. Prefer it for developer ergonomics, not as your main registration strategy.

### For Humans: What This Means (Risks)
Auto-define is great for convenience, but strict checks keep your system honest—use strict mode to catch mistakes.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Features/Define/Store/DefinitionStore.md`: Definition registry used here.
- `docs_md/Features/Define/Store/ServiceDefinition.md`: Definition object used in metadata.
- `docs_md/Features/Core/Enum/ServiceLifetime.md`: Lifetime enum used for default transient.

### For Humans: What This Means (Related)
Check the definition store and definition docs to understand what “a recipe” means, and the lifetime enum to see why transient is chosen.

### Method: __construct(...)

#### Technical Explanation (__construct)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

##### Parameters (__construct)
- See the PHP signature in the source file for exact types and intent.

##### Returns (__construct)
- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (__construct)
- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (__construct)
- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (__construct)
- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
