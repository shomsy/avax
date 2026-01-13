# ADR-001: Avoid Traits for Core Logic

## Status

✅ Accepted

## Context

In many large PHP projects, [Traits](https://en.wikipedia.org/wiki/Trait_(computer_programming)) are used to share logic
between classes. However, they often lead to "invisible" dependencies, naming collisions, and difficulty in unit testing
because they cannot be mocked independently.

The Avax Container aims for maximum observability and strict separation of concerns.

## Decision

We decided to prohibit the use of Traits for core resolution logic. Instead, we use **Composition**.

- If logic needs to be shared, it must be extracted into a separate **Action** or **Service** class.
- The consumer then receives this service via the constructor or the Pipeline.

## Consequences

### Positive ✅

- **Explicit Dependencies**: You can see exactly what a class does by looking at its constructor.
- **Improved Mocking**: Every service can be swapped out in tests.
- **No Naming Collisions**: Avoids the "Trait flat-land" where method names might overlap.

### Negative ❌

- **More Files**: We have more small classes instead of fewer large ones.
- **Boilerplate**: Some extra "wiring" code is needed to pass services along.

## References

- [Composition over Inheritance](https://en.wikipedia.org/wiki/Composition_over_inheritance)
