---
title: configuration-how-this-works
owner: foundation-database-configuration
last_reviewed: 2026-04-22
classification: internal
---

# Configuration How This Works

## What this folder is

This folder owns explicit assembly of the Database system. It replaces the old lifecycle and module registry pattern
with a direct composition root.

## Real commands or triggers that reach this folder

- `Database::configuration()`
- Container adapter resolution through `DatabaseServiceProvider`

## Exact upstream handoffs

- Callers configure the builder
- `DatabaseBuilder::ready()` constructs the system root and capability owners

## Main decision point

- `DatabaseBuilder::ready()` is the only place that wires the runtime graph together

## Writes and side effects

- None until `ready()` resolves the runtime objects

## Failure shape

- Invalid config or missing runtime prerequisites surface here first

## Debug first

- `DatabaseBuilder.php`
