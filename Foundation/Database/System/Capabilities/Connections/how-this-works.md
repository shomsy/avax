---
title: connections-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# Connections How This Works

## What this folder is

This folder owns direct database connections, pooled connections, connection config normalization, and the acquire or
release lifecycle.

## Real commands or triggers that reach this folder

- `DatabaseInterface::connections()`
- `DatabaseInterface::table()` and `DatabaseInterface::query()` when a builder resolves a connection
- `Transactions` when a transaction binds to a physical connection

## Exact upstream handoffs

- `System/Configuration/DatabaseBuilder.php` creates `Connections`
- `Connections.php` forwards to `ConnectionManager`
- `ConnectionManager` decides between direct connection flow and pooled authority

## Main decision point

- `ConnectionManager::connection()` decides whether the requested connection is cached, pooled, or built fresh

## Writes and side effects

- Opens PDO connections
- Borrows and releases pooled connections
- Emits connection telemetry events

## Failure shape

- Missing config or unreachable databases throw connection exceptions

## Debug first

- `ConnectionManager.php`
- `DirectConnectionFlow.php`
- `Pool/ConnectionPool.php`
