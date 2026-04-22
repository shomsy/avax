---
title: createbuilder-how-this-works
owner: foundation-database-querying
last_reviewed: 2026-04-22
classification: internal
---

# CreateBuilder How This Works

## What this folder is

This folder owns creation of connection-bound `QueryBuilder` instances for the Querying capability.

## Real commands or triggers that reach this folder

- `Querying::builder()`
- `Querying::from()`

## Exact upstream handoffs

- `Querying.php` delegates builder instantiation into this folder

## Main decision point

- `CreateBuilder.php` wires one resolved connection, one transaction manager, one identity map, and one orchestrator

## Writes and side effects

- Resolves a connection
- Creates execution and transaction orchestration objects

## Debug first

- `CreateBuilder.php`
