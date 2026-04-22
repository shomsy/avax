---
title: console-how-this-works
owner: foundation-database-integrations
last_reviewed: 2026-04-22
classification: internal
---

# Console How This Works

## What this folder is

This folder contains CLI-facing adapters for Database migration and export commands. The adapters keep command ownership
out of `System` while delegating to runtime services from the Database component.

## Real commands that reach this folder

- `migrate`
- `migrate:rollback`
- `migrate:status`
- `make:migration`
- `db:seed`
- `db:export`

## Exact CLI front doors

- `Foundation/Commands/CommandDefinitions.php` maps command aliases to these wrapper classes

## Main decision point

- Each adapter decides how CLI arguments map to runtime services without introducing core behavior into `System`

## Writes and side effects

- Runs migrations
- Rolls back migrations
- Reads migration status
- Generates migration files
- Runs seeder classes
- Exports SQL snapshots

## Debug first

- `MigrateCommand.php`
- `MigrateRollbackCommand.php`
- `MigrateStatusCommand.php`
- `MakeMigrationCommand.php`
