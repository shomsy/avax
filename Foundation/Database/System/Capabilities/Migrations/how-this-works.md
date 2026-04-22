---
title: migrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# Migrations How This Works

## What this folder is

This folder owns the cohesive Laravel-style migration capability: blueprint DSL, migration loading and generation,
runner logic, repository state, console-facing operations, and export support.

## Real commands or triggers that reach this folder

- `DatabaseInterface::migrations()`
- Console migration commands under `Foundation/Database/Integrations/Console`
- Runtime schema setup or export flows

## Exact upstream handoffs

- `Migrations.php` is the public capability owner
- `Execution/Repository/MigrationRepository.php` owns migration history persistence
- `Execution/Runner/MigrationRunner.php` coordinates apply and rollback
- `Generate/*` discovers or creates migration files

## Main decision point

- `Migrations.php` decides whether the caller needs a builder-backed schema helper, a repository, a runner, a loader, a
  generator, or an exporter

## Writes and side effects

- Creates and drops tables
- Writes to the `migrations` repository table
- Loads migration files from disk
- Generates new migration files

## Failure shape

- Structural execution failures throw `MigrationException`
- Lower-level query and transaction failures bubble through the runner

## Debug first

- `Execution/Runner/MigrationRunner.php`
- `Execution/Repository/MigrationRepository.php`
- `Design/Table/Blueprint.php`
