# ContextBuilder

## Quick Summary

- Fluent builder for contextual bindings: choose a consumer, declare what it needs, and decide what it gets.
- Exists to express “when X needs Y, give Z” rules clearly.

### For Humans: What This Means (Summary)

It’s the builder you use for special-case wiring depending on who’s asking.

## Terminology (MANDATORY, EXPANSIVE)

- **Consumer**: The service that is requesting a dependency.
- **Needs**: The dependency identifier required by the consumer.
- **Give**: The concrete implementation/value/factory provided.

### For Humans: What This Means (Terminology)

It’s conditional wiring based on the caller.

## Think of It

Like a VIP rule: “When Alice asks for coffee, give decaf.”

### For Humans: What This Means (Analogy)

Same request, different result, depending on who asked.

## Story Example

When `PaymentService` needs `HttpClient`, you give a hardened client. When other services need `HttpClient`, they get
the default client.

### For Humans: What This Means (Story)

You can override dependencies in specific contexts.

## For Dummies

- Start with `when($consumer)`.
- Call `needs($abstract)`.
- Call `give($concrete)`.

### For Humans: What This Means (Walkthrough)

Three steps: who, what, which.

## How It Works (Technical)

This is a contract; implementations store contextual mapping rules in a definition store.

### For Humans: What This Means (Technical)

It describes the API; the store keeps the rules.

## Architecture Role

Part of registration/configuration flow.

### For Humans: What This Means (Architecture)

It’s how you define conditional wiring.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods Summary)

When you’re trying to use or debug this file, this is the part you’ll come back to.

### Method: needs(string $abstract): self

#### Technical Explanation (needs)

Declares the needed dependency.

##### For Humans: What This Means (needs)

Say what the consumer needs.

##### Parameters (needs)

- `string $abstract`

##### Returns (needs)

- `self`

### Method: give(mixed $concrete): void

#### Technical Explanation (give)

Sets what should be given for the declared need.

##### For Humans: What This Means (give)

Choose what the consumer will get.

##### Parameters (give)

- `mixed $concrete`

##### Returns (give)

- `void`

## Risks, Trade-offs & Recommended Practices

- **Risk: Surprise behavior**. Contextual rules can be hard to track; document them.
- **Practice: Keep rules minimal**. Use only for clear, intentional overrides.

### For Humans: What This Means (Risks Summary)

Use contextual bindings sparingly so your system stays understandable.

## Related Files & Folders

- `docs/Features/Core/Contracts/RegistryInterface.md`: Entry point.
- `docs/Features/Define/Store/DefinitionStore.md`: Where rules live.

### For Humans: What This Means (Relationships)

Registry starts the rule; the definition store keeps it.
