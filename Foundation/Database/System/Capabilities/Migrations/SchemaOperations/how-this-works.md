---
title: schemaoperations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# SchemaOperations How This Works

## What this folder is

This folder owns focused runtime schema operations that are exposed from the Migrations capability.

## Real commands or triggers that reach this folder

- `Migrations::dropIfExists()`
- `Migrations::truncate()`
- `Migrations::createDatabase()`
- `Migrations::dropDatabase()`

## Exact upstream handoffs

- `Migrations.php` delegates specific schema actions into this folder

## Main decision point

- Each file owns one exact schema action and nothing broader

## Writes and side effects

- Executes schema-changing SQL

## Debug first

- `DropTable.php`
- `TruncateTable.php`
- `CreateDatabase.php`
- `DropDatabase.php`
