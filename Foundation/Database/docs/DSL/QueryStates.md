# Query State (AST)

## What it does

`QueryState` is an immutable DTO that captures the "intent" of a query. It stores columns, tables, joins, filters, and bindings in a structured format before they are compiled into SQL.

## Why it exists

- **Immutability**: Every change to a query creates a new state, allowing for easy query branching without side effects.
- **Decoupling**: The builder logic is separated from the physical SQL representation.
- **Determinism**: The same state always compiles to the same SQL for a given grammar.

## When to use

- You rarely interact with `QueryState` directly. It is managed by the `QueryBuilder`.
- Access it via `QueryBuilder::getState()` if you need to inspect or replicate a query's internal structure.

## Structure

The state consists of specialized nodes:

- `WhereNode`: A single filter condition.
- `JoinNode`: A table relationship definition.
- `OrderNode`: A sorting instruction.
- `BindingBag`: A secure container for parameter values.

## Common pitfalls

- **Direct modification**: Never try to modify properties on a `QueryState` instance; it is strictly `readonly`. Use `withX()` or `addX()` methods which return new instances.
- **Binding order**: Bindings are linearized during compilation. Adding bindings out of order relative to the SQL fragments will cause query failures.
