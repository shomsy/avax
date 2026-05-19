---
title: system-capabilities-migrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Migrations How This Works

## What this folder is

This folder owns the Database migration capability: schema DSL, migration loading and generation, execution and
rollback, status reporting, schema operations, exporting, and seeding.

## Real commands or triggers that reach this folder

- `Database::migrations()` or `Database::schema()` plus migration console commands reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

`Migrations.php` returns a narrow runtime service for the exact job the caller requested, so generation, loading,
execution, rollback, export, and seed flows stay separate but cohesive.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `CreateMigration/` holds the next narrower ownership slice below this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `CreateMigration/` holds the next narrower ownership slice below this folder.
- `Design/` holds the next narrower ownership slice below this folder.
- `Exceptions/` holds the next narrower ownership slice below this folder.
- `ExportDatabase/` holds the next narrower ownership slice below this folder.
- `LoadMigrations/` holds the next narrower ownership slice below this folder.
- `Migrations.php` participates directly in the behavior owned by this folder.
- `ReadMigrationStatus/` holds the next narrower ownership slice below this folder.
- `RollbackMigrations/` holds the next narrower ownership slice below this folder.
- `RunMigrations/` holds the next narrower ownership slice below this folder.
- `SchemaOperations/` holds the next narrower ownership slice below this folder.
- `SeedDatabase/` holds the next narrower ownership slice below this folder.

## Writes and side effects

- This slice can read or write migration files, mutate schema, update migration history, seed data, or export SQL
  snapshots.

## Failure shape

- Loader defects, repository drift, schema DSL bugs, or migration execution failures surface through this slice.

## Debug first

- Start with `CreateMigration/` holds the next narrower ownership slice below this folder.
- Start with `Design/` holds the next narrower ownership slice below this folder.
- Start with `Exceptions/` holds the next narrower ownership slice below this folder.

