# Schema Facade SQLite Proof Report

## Stage

V1-D3.3 DataStack/Database Schema Facade SQLite Proof

## Status

PROVEN-PARTIAL / BEHAVIOR-GREEN

## Tests

- tests/Unit/Components/DataStack/Database/SchemaFacadeSqliteTest.php

## Proven behavior

- ReadConnection can open SQLite in-memory connection from component config
- Connections can expose PDO for a named connection
- Query can create a QueryBuilder through CreateBuilder
- QueryBuilder statement() executes through PDOExecutor
- Schema public surface delegates to Schema capability
- Schema::create() creates a real SQLite table
- Schema::table() adds a real SQLite column
- Schema::dropIfExists() drops a real SQLite table

## Production bugs fixed

- QueryOrchestrator used named argument `scope`, but ExecutorInterface/PDOExecutor expect `executionScope`
- CreateBuilder used named argument `scope`, but QueryOrchestrator expects `executionScope`
- PDOExecutor no longer assumes global config() helper exists in isolated component tests

## Remaining risk

DatabaseBuilder::ready() is still suspected broken because constructor dependencies have drifted.
Do not fix it blindly. Inspect and repair as its own slice.
