---
title: system-capabilities-query-how-this-works
owner: foundation-database-query
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Query How This Works

## What this folder owns

This folder owns the Laravel-like query DSL: builder creation, immutable query state, SQL compilation, execution, and
query-specific exceptions. It does not own ORM identity or transaction orchestration.

## The simplest story

- A caller reaches `Avax\Database\Database::table('users')` or `Avax\Database\Database::query()->from('users')`.
- `Query.php` asks `CreateBuilder/CreateBuilder.php` for a connection-bound `Builder/QueryBuilder.php`.
- `QueryBuilder.php` updates immutable `State/QueryState.php`, `Grammar/*` compiles SQL, and
  `Execution/QueryOrchestrator.php` sends it to `Execution/PDOExecutor.php`.

## The first important path

- `System/Database.php::table()` forwards to `Query.php::from()`.
- `Query.php::from()` calls `Query.php::builder()`, which delegates to `CreateBuilder.php`.
- `CreateBuilder.php` resolves the right PDO connection, creates `QueryOrchestrator.php`, and returns a ready
  `QueryBuilder.php`.
- `QueryBuilder.php` compiles and executes the query when a terminal method such as `get()`, `first()`, `insert()`,
  `update()`, or `delete()` is called.

## Direct files in this folder

- `Query.php` is the capability owner and entrypoint for connection-aware builder creation.

## Child folders in this folder

- `Builder/` owns fluent query methods and terminal operations.
- `CreateBuilder/` owns creation of ready builders for one connection.
- `State/` owns immutable AST and query state objects.
- `Grammar/` owns SQL compilation rules.
- `Execution/` owns low-level query execution and pretend mode.
- `Exceptions/` owns query-specific failure types.
- `Enums/`, `DTO/`, and `ValueObjects/` hold query-local supporting types.

## Debug first

- Start in `Query.php` when the wrong connection or wrong entrypoint is used.
- Start in `CreateBuilder/CreateBuilder.php` when a builder is missing the right dependencies.
- Start in `Builder/QueryBuilder.php` for wrong SQL shape or wrong fluent behavior.
- Start in `Execution/PDOExecutor.php` for driver failures or redacted binding behavior.

## What to remember

- `Query` is SQL DSL plus execution, not ORM.
- `QueryBuilder::transaction()` is intentionally gone.
- Query-owned identity/deferred orchestration was removed from this capability.

