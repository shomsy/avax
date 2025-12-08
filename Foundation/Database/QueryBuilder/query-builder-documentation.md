## 📘 Gemini QueryBuilder – Complete Enterprise-Grade Documentation

### 🎯 Purpose & Vision

The `QueryBuilder` module is a **high-performance**, **security-hardened**, and **enterprise-architected** SQL query
abstraction layer, designed to support Clean Architecture, Domain-Driven Design (DDD), and modern PHP 8.3+ idioms.

It allows **fully deferred**, **transactional**, **composable** and **fluent** SQL construction with automatic quoting,
trait-driven modularization, and strict PSR-12 code hygiene.

---

## 🏛 Architecture & Layering

### Clean Architecture Layers:

- **Domain**: `QueryBuilderEnum`, exceptions, interfaces
- **Application**: `QueryBuilder`, fluent API, logic chaining
- **Infrastructure**: `DatabaseConnection`, `PDO`, `UnitOfWork`
- **Interface**: Traits (JOINs, WHEREs, SCHEMA, SoftDeletes, Transactions, etc.)

---

## 🔥 Core Capabilities

- ✅ **Fluent Builder API** – Method chaining
- ✅ **Unit of Work Pattern** – Atomic deferred batched writes
- ✅ **Nested Transactions** – Savepoints and rollback isolation
- ✅ **Strong Security** – OWASP-compliant identifier quoting, parameter binding
- ✅ **JOINs, WHERE, GROUP BY, ORDER BY FIELD, RAW SQL**
- ✅ **UPSERT, BATCH INSERT, TRUNCATE, SOFT DELETE, RESTORE**
- ✅ **Schema Manipulation** – CREATE, DROP, SWITCH DATABASE
- ✅ **Indexing Recommendations** – via query introspection
- ✅ **Identity Map** – Result-level memory cache
- ✅ **Driver Agnostic** – MySQL, PostgreSQL, SQLite, SQL Server, Oracle

---

## 📦 Class Responsibilities

### `BaseQueryBuilder`

- Table management
- PDO driver quoting
- Core validation and error isolation

### `QueryBuilder`

- Extends `BaseQueryBuilder`
- Integrates all traits
- Manages query registration and execution
- Flushes `UnitOfWork`

### `QueryBuilderEnum`

- Enums for query types and DB drivers
- Centralized validity control

### `QueryBuilderException`

- Robust structured exception handler for all builder logic

---

## 🔐 Security Architecture

- 💡 **quoteIdentifier()**: DB-driver-specific identifier sanitization
- 🔒 **validateColumnName()**: SQL-safe column regex enforcement
- 🧱 **prepare() with binding**: no raw execution ever
- ⚠ **raw()** still safe via `prepare`
- 🚨 No string interpolation allowed in any dynamic clauses

---

## 🧠 Trait Breakdown & Modules

| Trait                           | Key Methods                                            | Role                                  |
|---------------------------------|--------------------------------------------------------|---------------------------------------|
| `InsertUpdateTrait`             | `insert()`, `batchInsert()`, `upsert()`, `update()`    | Deferred write logic + injection safe |
| `SelectQueryTrait`              | `get()`, `first()`, `exists()`                         | Read/transform APIs                   |
| `JoinClauseBuilderTrait`        | `leftJoin()`, `rightJoin()`, `joinWithAlias()`         | Relational table composition          |
| `WhereTrait`                    | `where()`, `orWhere()`, `whereIn()`                    | Logical filters                       |
| `OrderByAndGroupByBuilderTrait` | `orderBy()`, `orderByField()`, `groupBy()`, `having()` | Query sorting, filtering              |
| `SoftDeleteAndDeleteTrait`      | `delete()`, `softDelete()`, `restore()`                | Safe data removal                     |
| `DatabaseTransactionTrait`      | `transaction()`, `commit()`, `rollbackTransaction()`   | Nested TXN support via SAVEPOINTs     |
| `ProvidesUnitOfWork`            | `registerQueryInUnitOfWork()`, `flush()`               | Aggregate control of DB ops           |
| `IdentityMapTrait`              | `addToIdentityMap()`, `getFromIdentityMap()`           | Cache/coherency layer                 |
| `QueryOptimizationTrait`        | `recommendIndexes()`, `showIndexingRecommendations()`  | Performance hints                     |
| `SchemaQueryBuilderTrait`       | `createDatabase()`, `dropDatabase()`, `renameTable()`  | Schema-level DDL APIs                 |

---

## 🧪 Example Scenarios

### Atomic Insert + Upsert + Flush

```php
$queryBuilder->table('users')
    ->insert(['name' => 'John'])
    ->upsert(['email' => 'john@example.com'], ['name'])
    ->flush();
```

### Complex Join + Group + Order

```php
$queryBuilder->table('orders')
    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
    ->groupBy('users.country')
    ->having('COUNT(orders.id)', '>', 10)
    ->orderByField('users.country', ['USA', 'CAN', 'UK'])
    ->get();
```

### Nested Transaction Isolation

```php
$queryBuilder->transaction(function () use ($queryBuilder) {
    $queryBuilder->insert([...]);

    $queryBuilder->transaction(function () use ($queryBuilder) {
        $queryBuilder->update([...], [...]);
        throw new RuntimeException("Abort inner block");
    });
});
```

---

## ✅ Validation & Safeguards

- All identifiers are sanitized
- All WHERE/ORDER columns are validated
- Driver-aware quote rules (Postgres = `"id"`, MySQL = `` `id` ``)
- Emulated prepares disabled: `ATTR_EMULATE_PREPARES => false`

---

## 🧱 Recommended Extension Points

- 🔍 **addPaginate()**: Automatic `LIMIT`/`OFFSET` builder
- 📦 **Model Integration**: Lightweight Active Record powered by `QueryBuilder`
- 🧩 **DTO hydration**: Return result into typed data objects
- 🧬 **Schema Discovery**: `getColumns()`, `describe()` support

---

## 🛠 Maintenance Checklist

- ✅ All query execution via `prepare`
- ✅ `flush()` guarantees atomic multi-query dispatch
- ✅ Nested transaction rollback supported via savepoints
- ✅ JOIN, ORDER BY, WHERE are all quote safe
- ✅ Logs via PSR Logger
- ✅ Method chaining and strict PSR-12 typing enforced

---

## 🧪 Test Strategy

- Unit tests for each trait
- Integration tests for all major query paths
- Edge tests for quoting, driver mismatch, nesting
- Benchmark comparisons on joins, pagination, batching

---

## 🧠 Cognitive Load Strategy

The purpose of this architecture is to **lower developer friction**, allow **introspectable and maintainable query
building**, and provide **clear extensibility points** without touching the underlying database engine logic.

> 🧙‍♂️ "Magic should be traceable."

---

## 🔁 Commit / Flush Philosophy

- Use `insert()`, `update()`, `upsert()` to stage
- Use `flush()` to **execute all in one ACID transaction**
- This is ideal for batch processes, migrations, or multi-step workflows

---

## ☠️ Anti-Patterns Prevented

- ❌ No raw string interpolation
- ❌ No SQL execution without prepared statements
- ❌ No mixed quoting or unvalidated identifiers
- ❌ No duplicate inserts due to Identity Map

---

## 🏁 Conclusion

The Gemini `QueryBuilder` is built for **mission-critical**, **secure**, and **scalable** SQL orchestration. It uses PHP
8.3+, modular traits, strict type enforcement, and modern software design techniques to create a **professional
foundation** for any data-layer abstraction.

