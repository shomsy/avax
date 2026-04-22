---
title: migrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# Migrations How This Works

## What this folder is

This folder owns the cohesive Laravel-style migration capability: blueprint DSL, migration loading and generation,
runner logic, repository state, schema operations, rollback/status helpers, and export support.

## Real commands or triggers that reach this folder

- `DatabaseInterface::migrations()`
- Console migration commands under `Foundation/Database/Integrations/Console`
- Runtime schema setup or export flows

## Exact upstream handoffs

- `Migrations.php` is the public capability owner
- `RunMigrations/MigrationRepository.php` owns migration history persistence
- `RunMigrations/MigrationRunner.php` coordinates apply and rollback
- `LoadMigrations/*` discovers migration files
- `CreateMigration/*` creates new migration files

## Main decision point

- `Migrations.php` decides whether the caller needs a schema operation, repository, runner, loader, generator,
  rollback/status helper, or exporter

## Writes and side effects

- Creates and drops tables
- Writes to the `migrations` repository table
- Loads migration files from disk
- Generates new migration files

## Failure shape

- Structural execution failures throw `MigrationException`
- Lower-level query and transaction failures bubble through the runner

## Debug first

- `RunMigrations/MigrationRunner.php`
- `RunMigrations/MigrationRepository.php`
- `Design/Table/Blueprint.php`
