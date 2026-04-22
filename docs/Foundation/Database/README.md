# Database

`Foundation/Database` is now a clean-break `System` component with five public capability surfaces:

- `connections()`
- `query()`
- `migrations()`
- `transactions()`
- `telemetry()`

The public composition root is `Avax\Database\System\Database` and the canonical bootstrap path is:

```php
$database = Database::configuration()
    ->usingConfig($config)
    ->ready();
```

The canonical read/write entrypoints are:

```php
$database->table('users');
$database->query()->from('users');
```

The canonical structural entrypoint is:

```php
$database->migrations();
```

## Docs map

- `Concepts/`
  Cross-capability explanations for architecture, connections, transactions, telemetry, identity map, and migration
  concepts.
- `DSL/`
  QueryBuilder and migration DSL behavior, grammar translation, raw expressions, ordering, grouping, mutations, schema
  helpers, and transaction usage.
- `System/Capabilities/Migrations/`
  Migration-engine specific notes, examples, and type coverage documents moved out of the source tree.
- `archive/`
  Pre-migration documentation snapshots preserved for historical reference only. These files are no longer the canonical
  source of truth.

## Shape

- `Foundation/Database/System/Database.php`
  Public root for the component.
- `Foundation/Database/System/Configuration/DatabaseBuilder.php`
  Container-free composition builder.
- `Foundation/Database/System/Capabilities/Connections`
  Connection config, pool, direct connection, and acquisition lifecycle.
- `Foundation/Database/System/Capabilities/QueryBuilder`
  Cohesive ORM/query capability.
- `Foundation/Database/System/Capabilities/Migrations`
  Cohesive Laravel-style migration capability.
- `Foundation/Database/System/Capabilities/Transactions`
  Transaction boundaries, nested transactions, savepoints, and deferred flush support.
- `Foundation/Database/System/Capabilities/Telemetry`
  Event bus, event types, scope, and sequence tracking.
- `Foundation/Database/Integrations`
  Optional adapters for the Avax container and console entrypoints.
