---
title: querying-how-this-works
owner: foundation-database-querying
last_reviewed: 2026-04-22
classification: internal
---

# QueryBuilder How This Works

## What this folder is

This folder owns the cohesive ORM-like query capability: fluent builder state, grammar compilation, executor dispatch,
raw-expression guardrails, and query-level exceptions.

## Real commands or triggers that reach this folder

- `DatabaseInterface::table('users')`
- `DatabaseInterface::query()->from('users')`
- Repository classes that receive a `QueryBuilder`
- Migration runtime when it needs builder-backed statements or transaction boundaries

## Exact upstream handoffs

- `Querying.php` creates builders per connection
- `Builder/QueryBuilder.php` owns the fluent API
- `Grammar/*` compiles SQL
- `Execution/*` sends compiled SQL to PDO

## Main decision point

- `Querying::builder()` binds the builder to one resolved connection, one transaction manager, and one
  telemetry scope

## Writes and side effects

- Executes selects, inserts, updates, deletes, upserts, and raw statements
- Can buffer deferred writes through the transaction identity map

## Failure shape

- Invalid user criteria throws `InvalidCriteriaException`
- Driver or SQL failures throw `QueryException`

## Debug first

- `Builder/QueryBuilder.php` for public API behavior
- `Grammar/BaseGrammar.php` for SQL shape
- `Execution/PDOExecutor.php` for driver issues
