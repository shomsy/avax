# Avax Database Component

Enterprise-grade SQL abstraction layer for PHP 8.3+. Built for security, reliability, and developer ergonomics.

## Features

- **Fluent Query Builder** — Immutable, chainable DSL for constructing SQL
- **Connection Pooling** — RAII-safe resource management with automatic cleanup
- **Transaction Management** — Nested transactions with automatic rollback
- **Identity Map / Unit of Work** — Deferred execution for optimized batch commits
- **Event-Driven Telemetry** — Correlation tracking with built-in binding redaction (OWASP compliant)
- **Multi-Dialect Support** — MySQL, PostgreSQL, SQLite via grammar abstraction

## Quick Start

```php
use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Avax\Database\QueryBuilder\Core\Executor\PDOExecutor;
use Avax\Database\QueryBuilder\Core\Executor\QueryOrchestrator;
use Avax\Database\QueryBuilder\Core\Grammar\MySQLGrammar;

$pdo = new PDO('mysql:host=localhost;dbname=app', 'user', 'pass');
$grammar = new MySQLGrammar();
$executor = new PDOExecutor(pdo: $pdo, connectionName: 'primary');
$orchestrator = new QueryOrchestrator(executor: $executor);

$builder = new QueryBuilder(grammar: $grammar, orchestrator: $orchestrator);

// SELECT
$users = $builder->from('users')
    ->where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// INSERT
$builder->from('users')->insert([
    'name' => 'Alice',
    'email' => 'alice@example.com'
]);

// TRANSACTION
$builder->transaction(function (QueryBuilder $query) {
    $query->from('orders')->insert(['total' => 100]);
    $query->from('audit_log')->insert(['action' => 'order_created']);
});
```

## Security

- **SQL Injection Protection**: All values are bound as parameters, never interpolated
- **Binding Redaction**: Sensitive data (passwords, PII) is automatically masked in logs
- **Raw SQL Guardrails**: `raw()` and `selectRaw()` include allowlist filters to block statement terminators and control
  characters

See [SECURITY.md](SECURITY.md) for full details.

## Architecture

See [ARCHITECTURE.md](ARCHITECTURE.md) for component overview and design philosophy.

## Stability

This component is **frozen core** (v1.0). Changes require documented justification. See [FREEZE.md](FREEZE.md).

## Testing

```bash
vendor/bin/phpunit tests/
```

## Examples

See [examples/transactional-flow.php](examples/transactional-flow.php) for advanced usage.

## License

Proprietary. Internal use only.
