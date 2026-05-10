# How This Works: Database Component

## What This Component Does

The Database component provides a complete data access layer for AvaX V4, combining:

- **Query Builder** — fluent SQL construction with dialect-specific grammar (SQLite, MySQL, PostgreSQL, SQLServer)
- **Connection Management** — pooled connections, read/write separation, multiple database drivers
- **Transaction Management** — nested transactions with isolation level control
- **Schema & Migrations** — declarative table definitions, migration runner, status tracking, seeders
- **ORM Layer** — attribute-based entity mapping, identity map, unit of work, repositories, batch loading (DataLoader pattern), lazy reference proxies
- **Telemetry** — query timing, slow query detection, N+1 query detection, OpenTelemetry integration
- **Query Governance** — query reports, performance detection

## What This Component Does NOT Do

- Full ORM with lazy loading of relations (proxies exist but are limited to `LazyReference`)
- Query result caching
- Multi-database sharding (sharded pool exists but is incomplete)
- Column alteration migrations (add/modify/drop columns — only create/drop/truncate tables)
- Horizontal partitioning
- Distributed two-phase commit (capability file exists but is not production-ready)
- Real non-SQL driver implementations (Redis, MongoDB, Cassandra, etc. pools are stubs extending base pools)

## Public API

### System Root (`System/Database.php`)

```php
$db = Database::configuration()
    ->usingConfig([...])
    ->ready();

$db->table('users')->where('active', 1)->get();
$db->query()->builder()->from('posts')->select('id', 'title')->get();
$db->entityManager();
$db->schema();
$db->migrations();
$db->transactions();
$db->telemetry();
```

### PublicSurface Entrypoints

| File | Purpose |
|------|---------|
| `Database.php` | Main facade: `query()`, `table()`, `entityManager()`, `schema()`, `migrations()`, `transactions()`, `telemetry()` |
| `Query.php` | Query builder facade: `table()`, `from()`, `builder()`, `raw()` |
| `Schema.php` | Schema management surface |
| `SchemaBuilder.php` | Low-level schema builder |
| `Migrations.php` | Migration runner surface |
| `Transactions.php` | Transaction facade: `begin()`, `commit()`, `rollback()`, `run()` |
| `EntityManager.php` | Entity management (stub — empty class) |
| `Telemetry.php` | Database telemetry surface |
| `Entities.php` | Entity collection surface |
| `shortcuts.php` | Global helper functions |

### Configuration (`System/Configuration/DatabaseBuilder.php`)

```php
Database::configuration()
    ->addConnection('default', [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ])
    ->ready();
```

## Internal Flow

### Query Execution

```
PublicSurface (Database::table / Query::from)
  -> QueryCapability (Capabilities/Query/Query)
    -> QueryBuilder (Capabilities/Query/Builder/QueryBuilder)
      -> Grammar (Capabilities/Query/Grammar) — compiles SQL for target dialect
      -> State (Capabilities/Query/State) — tracks query state
      -> Execution (Capabilities/Query/Execution) — runs against PDO connection
    -> Connections (Capabilities/Connections/Connections)
      -> OpenConnection — builds physical PDO connection
      -> Pools — manages pooled connections
```

### Transaction Flow

```
Transactions::run(callable)
  -> TransactionsCapability (Capabilities/Transactions/Transactions)
    -> RunTransaction — manages transaction lifecycle
      -> Begin -> Callable -> Commit (or Rollback on exception)
```

### Migration Flow

```
Migrations surface
  -> MigrationEngine
    -> MigrationLoader — discovers migration files
    -> MigrationRunner — executes migrations in order
    -> MigrationRepository — tracks applied migrations
    -> SchemaBuilder — executes DDL via Blueprint + Grammar
```

### ORM Flow

```
EntityManager (stub — not wired)
Entity attributes -> AttributeMetadataReader -> EntityMetadata
EntityPersister -> QueryBuilder -> results -> Hydrator -> Entity objects
IdentityMap -> deduplicates entities within unit of work
DataLoader -> batch loading to avoid N+1
```

## Dependencies

### Internal AvaX Components

| Component | Usage |
|-----------|-------|
| `DataTransfer` | DataObjects for query results |
| `Data` | Domain model base classes |
| `Observability` | Telemetry integration (OpenTelemetry exporters) |

### External

| Dependency | Usage |
|------------|-------|
| `PDO` | Primary database connection driver |
| `ext-pdo_*` | SQLite, MySQL, PostgreSQL drivers |

## Failure Behavior

| Scenario | Behavior |
|----------|----------|
| Connection failure | `ConnectionFailed` exception; retry available via `RetryablePool` |
| Query failure | `QueryFailed` / `DatabaseQueryFailed` exception with query context |
| Transaction failure | Automatic rollback on exception; `TransactionFailed` exception |
| Migration failure | Each migration runs in a transaction; atomic rollback on failure |
| No connections configured | `InvalidArgumentException` from `DatabaseBuilder::ready()` |
| Pool limit reached | `PoolLimitReachedException` |
| Deadlock | Detection and retry via transaction governance |

### Exception Hierarchy (`System/Foundation/`)

```
DatabaseThrowable (interface)
  -> DatabaseException (base)
       -> ConnectionException, ConnectionFailure
       -> QueryFailed, DatabaseQueryFailed
       -> TransactionFailed, DatabaseTransactionFailed
       -> MigrationException
       -> PoolLimitReachedException
```

## Runtime Safety

| Concern | Mechanism |
|---------|-----------|
| SQL injection | Parameterized queries only — raw values not interpolated |
| Connection limits | Pool `maxConnections`, idle timeout, connection timeout |
| Query timeout | Configurable at connection and query level |
| Read/write separation | `ReadConnection` capability routes reads to replica |
| Transaction isolation | Explicit isolation level support (Read Uncommitted → Serializable) |
| Connection pool safety | `BorrowedConnection` with automatic return on scope exit |
| N+1 detection | `NPlusOneDetector` in QueryGovernance and Telemetry |
| Slow query detection | `SlowQueryDetector` with configurable threshold |

## Examples

### Basic Query

```php
use Avax\Components\DataStack\Database\System\Database;

$db = Database::configuration()
    ->addConnection('default', ['driver' => 'sqlite', 'database' => ':memory:'])
    ->ready();

$users = $db->table('users')
    ->where('active', 1)
    ->orderBy('name')
    ->limit(10)
    ->get();
```

### Transactional Work

```php
$result = $db->transactions()->run(function () use ($db) {
    $db->table('accounts')->where('id', 1)->decrement('balance', 100);
    $db->table('accounts')->where('id', 2)->increment('balance', 100);
});
```

### Schema and Migrations

```php
$blueprint = new Blueprint('users');
$blueprint->id();
$blueprint->string('name');
$blueprint->string('email')->unique();
$blueprint->timestamps();

$db->schema()->create('users', $blueprint);
```

### Telemetry

```php
$telemetry = $db->telemetry();
$timeline = $telemetry->getTimeline();
$slowQueries = $telemetry->getSlowQueries(thresholdMs: 100);
$n1Reports = $telemetry->detectN1Queries();
```

## Known Limits

| Limit | Detail |
|-------|--------|
| `EntityManager` is a stub | Empty class — not wired into the component assembly |
| Connection pools are partially implemented | Many database-specific pools (Redis, MongoDB, Cassandra, etc.) are stubs with `@todo` markers; `ArrayPooledConnection` used as placeholder |
| No column alteration migrations | Only create/drop/truncate tables — no add/modify/drop column support |
| No query caching | No result caching layer exists |
| No multi-database sharding | `ShardedPool` exists but is not production-ready |
| Projections incomplete | `Capabilities/Query/Projections/` directory exists but is not fully implemented |
| ORM is partial | Attribute mapping, identity map, and persisters exist but `EntityManager` is not wired |
| Non-SQL pools are stubs | Redis, MongoDB, Cassandra, Elasticsearch, Neo4j, ClickHouse, YugabyteDB, CockroachDB, SQLServer pools are skeleton classes |
| Two-phase commit not ready | `TwoPhaseCommit.php` exists but is not integrated |
| Documentation incomplete | Component-level documentation is limited to `how-this-works.md` (template content) |

## Current Status

**Status: YELLOW**

- 296 PHP files in `System/`
- 21 test files with substantial coverage for QueryBuilder, Transactions, Schema, Grammar, and Connection Pools
- QueryBuilder (589 lines) — implemented with conditions, joins, aggregates, orders, groups, soft deletes, control structures, advanced mutations, macro support
- TransactionManager — implemented with isolation levels and nested transaction support
- Blueprint & Schema — implemented for table creation, column definitions, indexes, foreign keys
- Migrations — implemented with runner, repository, loader, status, seeders, CLI commands
- Connections — read/write separation implemented; pool infrastructure exists but many driver-specific pools are stubs
- ORM — attributes, metadata reader, hydrator, identity map, entity persister, repositories, DataLoader exist; `EntityManager` is empty
- Telemetry — query timeline, slow query detection, N+1 detection, OpenTelemetry integration implemented
- Missing: concrete `EntityManager` wiring, full connection pool implementations, projections, column alteration migrations, documentation
