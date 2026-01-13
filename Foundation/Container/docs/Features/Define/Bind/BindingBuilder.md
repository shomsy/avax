# BindingBuilder

## Quick Summary

- This file defines a "Fluent API" that allows developers to refine and decorate service registrations.
- It exists to provide a readable, discovery-based way to set complex options like tags and custom constructor
  arguments.
- It removes the complexity of interacting with the raw `ServiceDefinition` by providing a clean set of methods (`to`,
  `tag`, `withArgument`, `withArguments`).

### For Humans: What This Means (Summary)

This is the **Option Menu** for your service registration. After you tell the Registrar "I want to register a service,"
the Registrar hands you this builder so you can say "and I want it to have these labels," and "use these specific values
for its constructor."

## Terminology (MANDATORY, EXPANSIVE)

- **Binding Builder**: A specialized tool for "Refining" a core registration.
    - In this file: The `BindingBuilder` class.
    - Why it matters: It makes configuration readable—you can "Chain" methods together in a sentence.
- **Fluent Interface**: A style of coding where methods return `$this` to allow chaining.
    - In this file: Most methods return `self`.
    - Why it matters: It feels like writing a natural language sentence: `register X -> to Y -> tag Z`.
- **Argument Injection (withArgument/withArguments)**: Manually telling the container exactly what to put into a class's
  constructor.
    - In this file: The `withArguments()` and `withArgument()` methods.
    - Why it matters: Vital when you have "Primitive" values (like a string database password) that the container
      can't "guess" by looking at types.
- **Tagging**: Adding searchable labels to a blueprint.
    - In this file: The `tag()` method.
    - Why it matters: It allows groups of services to be handled together later.

### For Humans: What This Means (Terminology)

The Builder is your **Decorating Tool**. It uses **Fluent Chaining** to make the code look nice, and it handles **Manual
Settings** (Arguments) for when you want to take over control from the container.

## Think of It

Think of **Ordering a Sandwich**:

- **Registrar**: You say "I want a sandwich."
- **BindingBuilder**: The menu that lets you say: "On wheat bread (`to`) ... with a 'Vegetarian' sticker (`tag`) ... and
  extra pickles (`withArgument`)."

### For Humans: What This Means (Analogy)

The Registrar gets you started, but the Builder is what makes the registration exactly what you need for your specific
app.

## Story Example

You are registering a `MailService`. It needs an `apiKey`. You register it with the Registrar, and then use the
`BindingBuilder` to say: `->withArgument('apiKey', 'SG.12345')`. Without this, the container would see the
`string $apiKey` in the constructor and say "I don't know what string to put here!" The builder lets you bridge that gap
between "Auto-wiring" and "Hard-coded settings."

### For Humans: What This Means (Story)

It lets you talk to the container in "Plain English" when it needs help understanding a specific class.

## For Dummies

Imagine you're filling out a form with checkboxes.

1. **Name**: You already did this (Registrar).
2. **Implementation**: "Use this class" checkbox (`to`).
3. **Tags**: "Include in group X" checkbox (`tag`).
4. **Arguments**: "Special Instructions" box (`withArgument`).

### For Humans: What This Means (Walkthrough)

Whenever you see a line of code like `$container->bind(A)->to(B)->tag(C)`, every method AFTER the first one is part of
the `BindingBuilder`.

## How It Works (Technical)

The `BindingBuilder` is a lightweight object that holds a reference to the `DefinitionStore` and the `abstract` service
name. Every time you call a method like `to()` or `tag()`, it fetches the `ServiceDefinition` object from the store,
modifies its public properties (like `$arguments` or `$concrete`), and returns itself (`return $this`) so you can keep
calling more methods. It acts as a "Filtered View" of the store.

### For Humans: What This Means (Technical)

It doesn't "Hold" the data—it just knows how to find the data in the container's registry and change it for you. It's
like a remote control for a specific entry in the database.

## Architecture Role

- **Lives in**: `Features/Define/Bind`
- **Role**: Fluent DSL Provider.
- **Contract**: Implements `BindingBuilderInterface`.
- **Collaborators**: Modifies `ServiceDefinition` via `DefinitionStore`.

### For Humans: What This Means (Architecture)

It is the "Developer Experience" layer. Its only goal is to make registration code look clean and be easy to write.

## Methods

### Method: to(mixed $concrete)

#### Technical Explanation: to

Updates the `concrete` property of the definition. Can be a class name or a closure.

#### For Humans: What This Means (to)

"Actually use THIS implementation for the service."

### Method: tag(string|string[] $tags)

#### Technical Explanation: tag

Delegates tag registration to the store to ensure indices are updated.

#### For Humans: What This Means (tag)

"Put these labels on the service so I can find it later in a group."

### Method: withArguments(array $arguments)

#### Technical Explanation: withArguments

Batch merges an array of key-value pairs into the definition's constructor arguments.

#### For Humans: What This Means (withArguments)

"Here is a manual list of values to use for the constructor."

### Method: withArgument(string $name, mixed $value)

#### Technical Explanation: withArgument

A convenience method for providing a single argument override.

#### For Humans: What This Means (withArgument)

"For the parameter named [name], use THIS specific value."

## Risks & Trade-offs

- **Fragility**: If your constructor parameter names change in your class, you must manually update your
  `withArgument()` calls, or the container will fail (because it won't find the parameter name).
- **Complexity**: Deeply nested argument arrays can become hard to read. Use closures for complex logic instead of large
  arrays.

### For Humans: What This Means (Risks)

Be careful! If you use `withArgument('apiKey', ...)` and later rename your class's variable to `$token`, the container
will get confused because it’s still looking for `apiKey`.

## Related Files & Folders

- `Registrar.php`: The one who creates the builder.
- `ServiceDefinition.php`: The data that the builder is actually changing.
- `DefinitionStore.php`: The place where the data is kept.

### For Humans: What This Means (Relationships)

The **Registrar** hands you the **Builder** so you can edit the **Blueprint**.
