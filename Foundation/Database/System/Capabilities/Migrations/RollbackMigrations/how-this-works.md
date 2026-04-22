---
title: rollbackmigrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# RollbackMigrations How This Works

## What this folder is

This folder owns selecting the last applied migrations and coordinating their rollback.

## Real commands or triggers that reach this folder

- `Migrations::rollbacker()`
- `Integrations/Console/MigrateRollbackCommand.php`

## Exact upstream handoffs

- `Migrations.php` returns `RollbackMigrations.php` from this folder

## Main decision point

- `RollbackMigrations.php` decides which migration files correspond to rollback records

## Writes and side effects

- Reads migration batches
- Executes rollback operations

## Debug first

- `RollbackMigrations.php`
