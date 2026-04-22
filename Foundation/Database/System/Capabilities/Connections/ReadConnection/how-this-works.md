---
title: readconnection-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# ReadConnection How This Works

## What this folder is

This folder owns connection resolution, default-name selection, and in-memory remembering of opened connections.

## Real commands or triggers that reach this folder

- `Connections::connection()`
- `Connections::pdo()`
- `Transactions::on()`
- `Querying/CreateBuilder.php`

## Exact upstream handoffs

- `Connections.php` forwards all read-side connection access into this folder

## Main decision point

- `ReadConnection.php` decides whether to return a cached direct connection, pooled authority, or fresh open path

## Writes and side effects

- Remembers direct connections in memory
- Remembers instantiated pools in memory

## Debug first

- `ReadConnection.php`
- `ResolveDefaultConnection.php`
- `RememberConnection.php`
