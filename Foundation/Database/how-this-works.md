---
title: database-how-this-works
owner: foundation-database
last_reviewed: 2026-04-22
classification: internal
---

# Database How This Works

## What this folder is

This folder owns the Database component as a standalone system. The component exposes one public root, one
container-free builder, five first-class capabilities, and optional integration adapters.

## Real commands or triggers that reach this folder

- Application bootstrap that resolves `Avax\Database\System\DatabaseInterface`
- Runtime code that calls `Database::configuration()->ready()`
- Runtime code that requests `connections()`, `query()`, `migrations()`, `transactions()`, or `telemetry()`
- Console flows that invoke migration wrappers under `Foundation/Database/Integrations/Console`

## Exact upstream handoffs

- `Foundation/Database/System/Configuration/DatabaseBuilder.php` assembles the runtime graph
- `Foundation/Database/System/Database.php` exposes the public component surface
- `Foundation/Database/Integrations/*` adapt the system to container and console front doors without changing the core

## Main decision point

- `DatabaseBuilder::ready()` decides which connection manager, grammar, telemetry bus, transaction runtime, and
  migration runtime are bound into the final `DatabaseInterface`

## Writes and side effects

- Opens PDO connections
- Executes SQL queries and migrations
- Emits telemetry events for connection and query activity
- Generates migration files through console adapters

## Failure shape

- Configuration or connection failures surface as Database exceptions from `System/Foundation/Exceptions`
- Query compilation and execution failures surface from `System/Capabilities/QueryBuilder/Exceptions`
- Migration failures surface from `System/Capabilities/Migrations/Exceptions`

## Debug first

- Start at `System/Configuration/DatabaseBuilder.php` for assembly problems
- Start at `System/Database.php` for public API shape issues
- Start at the relevant capability folder for runtime behavior defects
