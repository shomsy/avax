# Singleton

## Quick Summary
- Marks a class as a singleton lifetime candidate.
- Exists to allow lifetime intent to be expressed directly on classes.

### For Humans: What This Means
When you add `#[Singleton]` to a class, you’re telling the container: “This should be shared, not rebuilt.”

## Terminology
- **Singleton lifetime**: One instance reused across the container’s lifetime.
- **Attribute-driven lifetime**: Deriving lifetime rules from code annotations.

### For Humans: What This Means
It’s a lifetime label that higher-level logic can respect.

## Think of It
Like putting “shared tool” on a toolbox item so everyone uses the same one.

### For Humans: What This Means
You’re marking that this object is meant to be reused.

## Story Example
A configuration service is stateless and expensive to create. The class is marked `#[Singleton]`. During registration or analysis, the container recognizes this and binds it as a singleton.

### For Humans: What This Means
You avoid rebuilding expensive stateless services.

## For Dummies
- Add `#[Singleton]` above the class.
- The container can interpret it to apply singleton lifetime.

### For Humans: What This Means
It’s a hint the container can use to choose caching behavior.

## How It Works (Technical)
The attribute targets classes and carries no fields. It acts as a marker discovered via reflection.

### For Humans: What This Means
It’s a simple tag with no extra data.

## Architecture Role
Part of core attribute vocabulary. Used by analysis/registration layers to derive lifetimes.

### For Humans: What This Means
It’s a foundational marker that influences lifetime decisions.

## Methods

_No public methods._

### For Humans: What This Means
It’s just a tag.

## Risks, Trade-offs & Recommended Practices
- **Risk: Misuse for stateful services**. Singletons with mutable state can cause bugs.
- **Practice: Use for stateless/shared infrastructure**. Logging, config, factories.

### For Humans: What This Means
Share things that are safe to share.

## Related Files & Folders
- `docs_md/Features/Core/Enum/ServiceLifetime.md`: Lifetime concepts.
- `docs_md/Core/Kernel/Strategies/SingletonLifecycleStrategy.md`: Storage behavior.

### For Humans: What This Means
See the lifetime enum and the singleton strategy to understand what “singleton” really means at runtime.
