# Database V1 Proof Checkpoint

## Stage

V1-D3 DataStack/Database Recovery Proof

## Status

PROVEN-PARTIAL / BEHAVIOR-GREEN

## PHPUnit

- 11 tests
- 67 assertions
- PASS

## Proven slices

### QueryBuilder

- SELECT compilation
- WHERE bindings
- WHERE IN bindings
- WHERE BETWEEN bindings
- JOIN compilation
- ORDER BY compilation
- LIMIT compilation
- OFFSET compilation
- DISTINCT compilation
- INSERT compilation and bindings
- UPDATE compilation and bindings
- DELETE compilation and bindings
- first()
- value()
- count()
- find()
- nested where through public where(Closure)
- fake executor boundary

### Schema Blueprint

- CREATE TABLE SQL generation
- ALTER TABLE SQL generation
- id()
- string()
- unique()
- boolean default()
- timestamps()
- dropColumn()
- renameColumn()

### Schema Facade SQLite

- ReadConnection opens SQLite in-memory connection from component config
- Connections exposes named PDO
- Query creates QueryBuilder through CreateBuilder
- QueryBuilder statement() executes through PDOExecutor
- Schema public surface delegates to Schema capability
- Schema::create() creates a real SQLite table
- Schema::table() adds a real SQLite column
- Schema::dropIfExists() drops a real SQLite table

## Production bugs fixed

- QueryState used `bindings`, but constructor expects `bindingBag`
- HasOrders used `order`, but QueryState::addOrder expects `orderNode`
- HasJoins used `join`, but QueryState::addJoin expects `joinNode`
- QueryBuilder used `state`, but GrammarInterface expects `queryState`
- HasAdvancedMutations used `state`, but GrammarInterface expects `queryState`
- Grammar used invalid `in_array(true, needle: ..., haystack: ...)`
- Grammar compiled nested where using QueryBuilder instead of QueryState
- HasConditions::whereNested() ignored immutable callback return value
- Blueprint called ColumnSQLRenderer::render() with `column` instead of `columnDefinition`
- Query / CreateBuilder / QueryOrchestrator / Connections used `scope` instead of `executionScope`
- CreateBuilder used `connection` instead of `databaseConnection`
- ReadConnection used `connection` instead of `databaseConnection`
- PDOExecutor used `previous` instead of `throwable` for QueryException
- PDOExecutor dispatch used `scope` instead of `executionScope`
- PDOExecutor no longer assumes global `config()` helper exists in isolated component tests

## Not yet proven

- DatabaseBuilder::ready()
- Migrations::migrate()
- MigrationLoader
- MigrationRunner
- MigrationRepository
- RollbackMigrations
- ReadMigrationStatus
- Seeder
- Transactions
- DatabaseExporter
- full component PHPStan
- full broken-reference recovery

## Next allowed slice

V1-D3.4 DatabaseBuilder assembly proof.

Do not unlock V2.
Do not start new features.
