# Database Component Documentation

Welcome to the Avax Database component documentation. This documentation provides both **conceptual explanations** and **DSL usage guides** for all aspects of the database system.

## Quick Navigation

### Concepts (Architecture and Internals)

| Document | Description |
|----------|-------------|
| [Architecture](Concepts/Architecture.md) | Kernel, modules, and lifecycle management |
| [Connections](Concepts/Connections.md) | Connection pooling and management |
| [IdentityMap](Concepts/IdentityMap.md) | Unit of Work pattern for deferred mutations |
| [Telemetry](Concepts/Telemetry.md) | Events, observability, and correlation tracking |

### DSL (Query Builder Usage)

| Document | Description |
|----------|-------------|
| [Filtering](DSL/Filtering.md) | WHERE clauses and logical operators |
| [Transactions](DSL/Transactions.md) | Transaction handling and savepoints |
| [Mutations](DSL/Mutations.md) | INSERT, UPDATE, DELETE, UPSERT |
| [QueryExecution](DSL/QueryExecution.md) | Running queries and handling results |
| [QueryStates](DSL/QueryStates.md) | Immutable state and clone semantics |
| [DeferredExecution](DSL/DeferredExecution.md) | Batching changes via IdentityMap |
| [PretendMode](DSL/PretendMode.md) | Dry-run SQL debugging |
| [RawExpressions](DSL/RawExpressions.md) | Safe inline SQL for power users |
| [GrammarTranslation](DSL/GrammarTranslation.md) | Multi-dialect SQL compilation |

## Finding Documentation in Code

All classes in the Database component include `@see` tags in their docblocks that link to the relevant documentation file. For example:

```php
/**
 * Connection pool implementation for managing reusable database connections.
 *
 * @see docs/Concepts/Connections.md
 */
class ConnectionPool { ... }
```

Hover over any class or method in your IDE to see the documentation link.

## Contributing to Documentation

When adding new features:

1. Update or create the relevant `.md` file
2. Add `@see` tags to all related classes
3. Keep docblocks concise; put details in Markdown
