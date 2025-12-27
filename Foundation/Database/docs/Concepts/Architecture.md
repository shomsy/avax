# Architecture Overview

This document covers the foundational architecture of the Database component, explaining the lifecycle management, module system, and configuration.

---

## Table of Contents

### Lifecycle

- [Kernel](#kernel)
- [Manifest](#manifest)
- [LifecycleInterface](#lifecycleinterface)

### Registry

- [ModuleRegistry](#moduleregistry)

### Configuration

- [Config](#config)

### Exceptions

- [DatabaseException](#databaseexception)
- [DatabaseThrowable](#databasethrowable)

---

# Lifecycle

## Kernel

**The "Heart" of the Database component.**

The Kernel is the central coordinator that bootstraps the entire database subsystem. When your application starts, the Kernel:

1. Reads the manifest to discover available modules
2. Registers each module with the registry
3. Boots all registered modules
4. On shutdown, gracefully tears down everything in reverse order

Think of it as the project manager who assembles the team, briefs everyone, and later dismisses them at the end of the day.

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `boot()` | Start up all database modules |
| `shutdown()` | Gracefully stop all modules |

```php
// Bootstrap the database component
$kernel = new Kernel(
    container: $container,
    registry: new ModuleRegistry(),
    eventBus: $eventBus
);

$kernel->boot();  // All modules are now ready

// ... application runs ...

$kernel->shutdown();  // Clean up on exit
```

**Lifecycle Flow:**

```
Application Start
      ↓
Kernel::boot()
      ↓
Read Manifest::getModules()
      ↓
For each module → Registry::register()
      ↓
Registry::boot()
      ↓
All modules operational
      ↓
... Application runs ...
      ↓
Kernel::shutdown()
      ↓
Registry::shutdown()
      ↓
Clean exit
```

---

## Manifest

**The "Table of Contents" for database modules.**

A static catalog that lists all available database modules. The Kernel reads this manifest to know which modules to load.

Think of it as the index page of a book — it tells you what chapters exist and where to find them.

```php
class Manifest
{
    public static function getModules(): array
    {
        return [
            'connection' => ConnectionModule::class,
            'query'      => QueryModule::class,
            'migration'  => MigrationModule::class,
            // ... more modules
        ];
    }
}
```

Modules are identified by name (string key) and a class name that implements the module interface.

---

## LifecycleInterface

**Contract for components with a start/stop lifecycle.**

Any class that needs to do setup work on boot and cleanup on shutdown should implement this interface.

```php
interface LifecycleInterface
{
    /**
     * Initialize the component.
     */
    public function boot(): void;
    
    /**
     * Clean up and release resources.
     */
    public function shutdown(): void;
}
```

---

# Registry

## ModuleRegistry

**The "HR Department" that manages database modules.**

The registry tracks all registered modules and coordinates their lifecycle. It knows:

- Which modules are registered
- Which modules have been booted
- The order to initialize and tear down

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `register($name, $class, $container)` | Add a module to the registry |
| `boot()` | Start all registered modules |
| `shutdown()` | Stop all modules in reverse order |
| `get($name)` | Retrieve a specific module instance |
| `has($name)` | Check if a module is registered |

```php
$registry = new ModuleRegistry();

// Register modules
$registry->register('connection', ConnectionModule::class, $container);
$registry->register('query', QueryModule::class, $container);

// Boot all at once
$registry->boot();

// Later, retrieve a specific module
$connectionModule = $registry->get('connection');

// Shutdown (modules are stopped in reverse order)
$registry->shutdown();
```

**Why reverse-order shutdown?**

If module B depends on module A, then:

- Boot order: A first, then B
- Shutdown order: B first, then A

This ensures B is cleaned up before A disappears.

---

# Configuration

## Config

**In-memory configuration repository with dot-notation access.**

A fluent, immutable container for database configuration. Supports nested access using dot notation (e.g., `database.connections.mysql.host`).

Think of it as a smart filing cabinet that can find nested folders with a single path.

**Key Methods:**

| Method | Purpose |
|--------|---------|
| `get($key, $default)` | Retrieve a value by key |
| `has($key)` | Check if a key exists |
| `set($key, $value)` | Set a value (returns new instance) |
| `all()` | Get all configuration as an array |

```php
$config = new Config([
    'database' => [
        'default' => 'mysql',
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'database' => 'myapp',
                'username' => 'root',
                'password' => 'secret'
            ],
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => '/path/to/db.sqlite'
            ]
        ]
    ]
]);

// Dot notation access
$host = $config->get('database.connections.mysql.host');
// Returns: '127.0.0.1'

// With default value
$timeout = $config->get('database.connections.mysql.timeout', 30);
// Returns: 30 (not set in config)

// Check existence
if ($config->has('database.connections.postgres')) {
    // ...
}
```

---

# Exceptions

## DatabaseException

**Base exception for all database errors.**

All database-related exceptions extend this class. It allows you to catch ANY database error with a single `catch` block.

Think of it as the "main fuse box" — if anything in the database layer fails, you can catch it here.

```php
try {
    $result = $builder->from('users')->get();
} catch (DatabaseException $e) {
    // Handles ANY database error:
    // - Connection failed
    // - Query syntax error
    // - Pool limit reached
    // - etc.
    $this->logger->error('Database error: ' . $e->getMessage());
}
```

**Exception Hierarchy:**

```
DatabaseThrowable (interface)
    └── DatabaseException (abstract class)
            ├── ConnectionException
            ├── ConnectionFailure
            ├── PoolLimitReachedException
            ├── InvalidCriteriaException
            └── ... other specific exceptions
```

---

## DatabaseThrowable

**Marker interface for database exceptions.**

An interface (badge) that all database exceptions implement. Useful for type-hinting when you want to catch only database-related errors.

```php
try {
    // Some operation
} catch (DatabaseThrowable $e) {
    // This catches any exception marked as database-related
}
```

---

# Module System Explained

## What is a Module?

A module is a self-contained feature set within the database component. Each module:

1. Has a name (identifier)
2. Has initialization logic
3. May have cleanup logic
4. May depend on other modules

## Module Lifecycle

```
┌─────────────────┐
│   Application   │
│     Starts      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Kernel::boot() │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Read Manifest  │───▶ List of modules
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   For each      │
│   module:       │
│  register()     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Registry::boot()│
│  (all at once)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Modules are   │
│    READY!       │
└────────┬────────┘
         │
    (app runs)
         │
         ▼
┌─────────────────┐
│Kernel::shutdown │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│Registry::shutdown│
│(reverse order)  │
└─────────────────┘
```

---

## Best Practices

1. **Always call shutdown()** — Even if it seems like nothing needs cleanup, call it for consistency.

2. **Use dot notation for config** — Makes nested configuration much more readable.

3. **Catch DatabaseException broadly** — Catch specific exceptions only when you need to handle them differently.

4. **Let the Kernel manage lifecycles** — Don't manually boot/shutdown modules.

5. **Register modules before booting** — The registry expects all modules to be registered before `boot()` is called.

---

## See Also

- [Connection Management](Connections.md)
- [Telemetry & Events](Telemetry.md)
- [QueryBuilder](../DSL/QueryBuilder.md)
