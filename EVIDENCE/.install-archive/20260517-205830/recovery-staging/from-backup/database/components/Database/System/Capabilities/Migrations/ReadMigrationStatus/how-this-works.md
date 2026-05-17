---
title: system-capabilities-migrations-readmigrationstatus-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Migrations / Read Migration Status How This Works

## What this folder is

This folder owns the read-only migration status view.

## Real commands or triggers that reach this folder

- `Database::migrations()` or `Database::schema()` plus migration console commands reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

It compares the filesystem migration set to the migration repository and returns one status snapshot instead of
executing anything.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `ReadMigrationStatus.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `ReadMigrationStatus.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can read or write migration files, mutate schema, update migration history, seed data, or export SQL
  snapshots.

## Failure shape

- Loader defects, repository drift, schema DSL bugs, or migration execution failures surface through this slice.

## Debug first

- Start with `ReadMigrationStatus.php` participates directly in the behavior owned by this folder.

