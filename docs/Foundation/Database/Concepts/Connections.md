# Connection Management

This document covers all connection-related classes in the Database component, explaining how database connections are
established, pooled, and managed.

---

## Table of Contents

### Core Connections

- [PdoConnection](#pdoconnection)
- [ConnectionFactory](#connectionfactory)
- [ConnectionManager](#connectionmanager)
- [DirectConnectionFlow](#directconnectionflow)
- [DatabaseFlow](#databaseflow)

### Connection Pooling

- [ConnectionPool](#connectionpool)
- [ConnectionPoolFlow](#connectionpoolflow)
- [BorrowedConnection](#borrowedconnection)
- [PoolState](#poolstate)

### Value Objects

- [ConnectionConfig](#connectionconfig)
- [Dsn](#dsn)

---

# Core Connections

## PdoConnection

**A wrapper around PHP's native PDO connection.**

This is the actual database connection object. It wraps PHP's `PDO` instance and provides a consistent interface with
additional features like health-checking (`ping()`).

Think of it as a "phone line" to the database. You can make calls (queries) through it, and it can tell you if the line
is still connected.

**Key Methods:**

| Method      | Purpose                               |
|-------------|---------------------------------------|
| `getPdo()`  | Get the underlying PHP PDO instance   |
| `ping()`    | Test if the connection is still alive |
| `getName()` | Get the connection's identifier       |

```php
$connection = new PdoConnection($pdo, 'primary');

if ($connection->ping()) {
    $pdo = $connection->getPdo();
    $stmt = $pdo->query('SELECT * FROM users');
}
```

---

## ConnectionFactory

**Creates PDO connections from configuration arrays.**

The factory that actually "dials the phone number" to establish a database connection. It takes configuration (host,
database, credentials) and returns a working connection.

Handles:

- DSN (Data Source Name) generation for different drivers (MySQL, SQLite, PostgreSQL)
- PDO option defaults (error modes, fetch modes)
- Connection attribute configuration

```php
$factory = new ConnectionFactory();
$connection = $factory->create([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret'
]);
```

---

## ConnectionManager

**Manages multiple named database connections.**

The "receptionist" who keeps track of all your database connections. If you have multiple databases (primary, replica,
analytics), the manager knows them all by name.

Supports:

- Multiple named connections
- Default connection configuration
- Connection retrieval by name

```php
$manager = new ConnectionManager($config);

// Get the default connection
$primary = $manager->connection();

// Get a specific named connection
$analytics = $manager->connection('analytics');
```

---

## DirectConnectionFlow

**Fluent builder for establishing a single direct connection.**

A builder pattern for connecting to a database in a clear, step-by-step way. Instead of passing a big configuration
array, you chain method calls.

Think of it as planning a road trip: "I'll start → use this map → bring these supplies → then go."

```php
$connection = DirectConnectionFlow::begin()
    ->using([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'myapp',
        'username' => 'root',
        'password' => 'secret'
    ])
    ->withEvents($eventBus)       // Optional: enable event dispatching
    ->withScope($executionScope)  // Optional: attach correlation ID
    ->connect();
```

**Key Methods:**

| Method                  | Purpose                                |
|-------------------------|----------------------------------------|
| `begin()`               | Start a new flow (static factory)      |
| `using($config)`        | Set the connection configuration       |
| `withEvents($eventBus)` | Attach event bus for connection events |
| `withScope($scope)`     | Attach execution scope for tracing     |
| `connect()`             | Establish the connection and return it |

---

## DatabaseFlow

**Base fluent interface for all connection flows.**

The abstract foundation that both `DirectConnectionFlow` and `ConnectionPoolFlow` extend. Provides common configuration
methods.

---

# Connection Pooling

## ConnectionPool

**A "garage" of reusable database connections.**

Instead of opening a new connection for every request (expensive!), the pool maintains a set of pre-opened connections
that can be borrowed and returned.

Imagine a car rental service:

- **acquire()** — Rent a car (borrow a connection)
- **release()** — Return the car (connection goes back to the pool)
- **pruneStaleConnections()** — Remove cars that have been idle too long

**Key Configuration:**

```php
$pool = new ConnectionPool([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
    'name' => 'primary',
    'pool' => [
        'max_connections' => 10,      // Maximum cars in the fleet
        'max_idle_connections' => 5,  // Maximum parked cars
        'max_idle_time_seconds' => 300  // Retire cars idle > 5 minutes
    ]
], $eventBus);
```

**Key Methods:**

| Method                      | Purpose                              |
|-----------------------------|--------------------------------------|
| `acquire()`                 | Borrow a connection from the pool    |
| `release($connection)`      | Return a connection to the pool      |
| `pruneStaleConnections()`   | Remove old/dead connections          |
| `validateConnection($conn)` | Check if a connection is still alive |
| `ping()`                    | Test overall pool health             |
| `getMetrics()`              | Get pool statistics                  |
| `withScope($scope)`         | Attach tracing context               |

---

## ConnectionPoolFlow

**Fluent builder for creating connection pools.**

Like `DirectConnectionFlow`, but creates a pool instead of a single connection.

```php
$pool = ConnectionPoolFlow::begin()
    ->using([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'database' => 'myapp',
        'username' => 'root',
        'password' => 'secret',
        'pool' => [
            'max_connections' => 10,
            'max_idle_connections' => 5
        ]
    ])
    ->withEvents($eventBus)
    ->pool();  // Returns a ConnectionPool
```

---

## BorrowedConnection

**A RAII wrapper that auto-releases a pooled connection.**

When you borrow a connection from the pool, you get a `BorrowedConnection` instead of the raw connection. This wrapper
automatically returns the connection when it's destroyed (goes out of scope).

RAII = "Resource Acquisition Is Initialization" — the connection is released when the object is garbage-collected.

Think of it as a hotel key card: when you check out (drop the card), your room becomes available again.

```php
$borrowed = $pool->acquire();

// Use the connection
$result = $borrowed->getPdo()->query('SELECT * FROM users');

// Option 1: Explicit release
$borrowed->release();

// Option 2: Automatic release when $borrowed goes out of scope
// No need to call release() — destructor handles it
```

**Key Methods:**

| Method                    | Purpose                           |
|---------------------------|-----------------------------------|
| `getPdo()`                | Get the underlying PDO (proxied)  |
| `ping()`                  | Check connection health (proxied) |
| `release()`               | Return to pool immediately        |
| `getOriginalConnection()` | Get the wrapped PdoConnection     |

---

## PoolState

**Internal bookkeeper for pool statistics.**

Tracks how many connections have been created, how many are currently out, and enforces the maximum limit.

Not used directly — the pool uses it internally.

---

# Value Objects

## ConnectionConfig

**Immutable credentials container.**

A type-safe object holding all connection credentials. Instead of passing arrays with potential typos, use this
structured object.

The `#[SensitiveParameter]` attribute ensures passwords don't appear in stack traces or error logs.

```php
$config = new ConnectionConfig(
    driver: 'mysql',
    host: '127.0.0.1',
    database: 'myapp',
    username: 'root',
    password: 'secret',  // Marked as sensitive
    charset: 'utf8mb4',
    name: 'primary'
);

// Or create from an array
$config = ConnectionConfig::from([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'myapp',
    // ...
]);
```

---

## Dsn

**Data Source Name generator.**

Constructs the PDO DSN string for different database drivers. Instead of manually formatting
`mysql:host=127.0.0.1;dbname=myapp`, use this value object.

```php
$dsn = Dsn::for(
    driver: 'mysql',
    host: '127.0.0.1',
    database: 'myapp',
    charset: 'utf8mb4'
);

echo $dsn->toString();
// Output: "mysql:host=127.0.0.1;dbname=myapp;charset=utf8mb4"

// SQLite is handled differently
$sqliteDsn = Dsn::for(
    driver: 'sqlite',
    host: '',
    database: '/path/to/db.sqlite',
    charset: ''
);
echo $sqliteDsn->toString();
// Output: "sqlite:/path/to/db.sqlite"
```

---

## Best Practices

1. **Use connection pooling for web applications** — Opening a new connection for every request is slow. Pools reuse
   connections.

2. **Always release borrowed connections** — Either explicitly or via RAII (let the `BorrowedConnection` go out of
   scope).

3. **Configure pool limits sensibly** — Too few connections = bottleneck. Too many = overwhelming the database.

4. **Enable events for observability** — Attach an EventBus to track connection lifecycle events.

5. **Use ConnectionConfig instead of arrays** — Type safety and automatic sensitive parameter masking.

---

## See Also

- [Architecture Overview](Architecture.md)
- [Telemetry & Events](Telemetry.md)
- [QueryBuilder](../DSL/QueryBuilder.md)
