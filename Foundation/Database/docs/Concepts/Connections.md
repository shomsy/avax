# Connections and Pooling

## What it is

The Connection layer manages the physical link between the PHP application and the database server.

- **Connection Manager**: A central registry that manages multiple database connections (e.g., `mysql`, `pgsql`).
- **Connection Pool**: An optional performance optimization that keeps a "warm" set of connections alive instead of opening and closing them for every request.

## Why it exists

- **Driver Abstraction**: You interact with an interface (`DatabaseConnection`) regardless of whether it's MySQL, SQLite, or Postgres.
- **Resource Management**: Opening a database connection is expensive (time and CPU). Pooling reuses connections, drastically improving performance under high load.
- **Fault Tolerance**: The system can detect "dead" connections via heartbeats and automatically prune or reconnect them.

## Configuration

Connections are typically defined with a DSN (Data Source Name) or an associative config array.

```php
$manager->addConnection('main', new MySqlConnection($dsn));
```

## Connection Pooling

When pooling is enabled:

1. When you ask for a connection, you "Borrow" one from the pool.
2. When your operation finishes, the connection is "Returned" to the pool.
3. If the pool is empty and below the limit, a new connection is spawned.
4. If the pool is full, your request waits (with a timeout).

## Common pitfalls

- **Connection Leaks**: If you borrow a connection manually but never return it, the pool will eventually "starve" and block all future requests. Always use the provided flows or wrappers.
- **Socket Timeouts**: Databases often close idle connections. The pool uses a `Heartbeat` to keep them alive or prunes them if they go stale.
- **Pool Size**: Setting the pool too small causes bottlenecks; setting it too large can overwhelm the database server's process limit.
