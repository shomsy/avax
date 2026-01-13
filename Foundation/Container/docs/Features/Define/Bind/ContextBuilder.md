# ContextBuilder

## Quick Summary

- This file defines a specialized fluent builder for "Contextual Injection" rules.
- It exists to solve scenarios where different classes need different implementations of the same interface (e.g., "
  ImageController needs S3Disk, but LogController needs LocalDisk").
- It removes the complexity of global implementation conflicts by allowing "Exception Rules" based on the consumer's
  identity.

### For Humans: What This Means (Summary)

This is the **"It Depends" Builder**. Sometimes, a global rule isn't enough. You use this to say: "Usually everyone gets
Implementation A, but for THIS specific class, I want you to give it Implementation B instead."

## Terminology (MANDATORY, EXPANSIVE)

- **Contextual Binding**: An injection rule that only applies if a specific "Consumer" is asking for a dependency.
    - In this file: Managed via the `ContextBuilder`.
    - Why it matters: It allows groups of classes to share an interface without being forced to use the exact same
      implementation.
- **Consumer**: The class that is *receiving* the dependency.
    - In this file: The `$consumer` property (can also be a wildcard like `App\Http\*`).
    - Why it matters: It defines the "Scope" of the exception rule.
- **Dependency (Needs)**: The interface or abstract ID that is being overridden.
    - In this file: The `$needs` property set in the `needs()` method.
    - Why it matters: It specifies *what* we are swapping out for the consumer.
- **Implementation (Give)**: The actual value or class to provide.
    - In this file: The value passed to the `give()` method.
    - Why it matters: It specifies *what* to swap in.

### For Humans: What This Means (Terminology)

Contextual binding is about **Smart Exceptions**. It identifies a **Consumer** (Who is asking?), a **Dependency** (What
do they need?), and an **Override** (What should we give them instead?).

## Think of It

Think of a **Parent with Two Kids**:

- **Global Rule**: "Everyone gets an Apple for a snack."
- **Contextual Rule**: "When the younger child (`consumer`) needs a snack (`needs`), give them a Banana instead (`give`)
  because they are allergic to apples."

### For Humans: What This Means (Analogy)

The global rule is good for most people, but the Contextual Rule makes sure that everyone gets exactly what is right for
them personally.

## Story Example

You have a `StorageInterface`. By default, it uses `LocalFileSystem`. However, you have a `VideoUploader` class that
needs to save files to the cloud. You write:
`$container->when(VideoUploader::class)->needs(StorageInterface::class)->give(S3Storage::class);`. Now, whenever
anything else asks for Storage, they get a local folder. But when the VideoUploader is built, it gets the cloud storage.
No one has to change their code, and everything works perfectly.

### For Humans: What This Means (Story)

It prevents "Naming Wars" where you have to call one interface `LocalStorage` and another `CloudStorage`. You just use
`StorageInterface` everywhere, and the container handles the logic.

## For Dummies

Imagine a universal remote.

1. **Setting**: "If I'm using the DVD Player..." (`when`)
2. **Target**: "...and I press the Power button..." (`needs`)
3. **Action**: "...turn on the Projector." (`give`)

### For Humans: What This Means (Walkthrough)

If you find yourself creating two different interfaces that do the same thing just so you can inject them into different
classes, STOP. Use a Contextual Binding instead.

## How It Works (Technical)

The `ContextBuilder` is a two-step process to ensure a readable DSL. First, the construction (via `when()`) captures the
consumer's name. Second, the `needs()` method captures the dependency to be overridden. Finally, the `give()` method
writes the triple (Consumer, Needs, Implementation) into the `DefinitionStore` using the `addContextual()` method. The
store uses these triples during runtime resolution to perform a "Best Match" lookup before falling back to global
defaults.

### For Humans: What This Means (Technical)

It’s like a "Conditional Statement" (`if-then`) for your container's memory. The builder just helps you type that
statement in a format that looks nice in your code.

## Architecture Role

- **Lives in**: `Features/Define/Bind`
- **Role**: Specialized Exception Builder.
- **Dependency**: `DefinitionStore`.

### For Humans: What This Means (Architecture)

It provides the "Specialized Logic" layer for the container.

## Methods

### Method: __construct(DefinitionStore $store, string $consumer)

#### Technical Explanation: __construct

Initializes the builder with the consumer pattern.

#### For Humans: What This Means

Targeting the "Who" of the special rule.

### Method: needs(string $abstract)

#### Technical Explanation: needs

Records the target dependency for the override.

#### For Humans: What This Means

Targeting the "What" of the special rule.

### Method: give(mixed $implementation)

#### Technical Explanation: give

Finalizes the rule by providing the override value and saving it to the store.

#### For Humans: What This Means

Targeting the "Result" of the special rule. "Presses the save button."

## Risks & Trade-offs

- **Wildcard Overlap**: If you have a rule for `App\*` and another for `App\Http\*`, you must be careful which one "
  wins" (usually the more specific one).
- **Refactoring**: Just like `give()` in the `BindingBuilder`, if you rename your interface or class, you must remember
  to update these string-based rules.

### For Humans: What This Means (Risks)

It’s powerful, but it can be hard to track if you have hundreds of these rules. Use them for "Heavy Lifting" (like
storage or logging) rather than for every single class.

## Related Files & Folders

- `Registrar.php`: The starting point (via `when()`).
- `DefinitionStore.php`: Where the contextual rule is eventually stored and searched.

### For Humans: What This Means (Relationships)

You start at the **Registrar**, use the **ContextBuilder** to write your exception, and the **Store** remembers it for
later.
