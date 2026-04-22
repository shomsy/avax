---
title: system-capabilities-migrations-runmigrations-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Migrations / Run Migrations How This Works

## What this folder is

This folder owns migration execution and repository persistence for migration history.

## Real commands or triggers that reach this folder

- `Database::migrations()` or `Database::schema()` plus migration console commands reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

The repository knows what ran, the runner injects the builder and executes `up()` or `down()`, and the repository is
updated to reflect the final state.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `MigrationRepository.php` participates directly in the behavior owned by this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `MigrationRepository.php` participates directly in the behavior owned by this folder.
- `MigrationRunner.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can read or write migration files, mutate schema, update migration history, seed data, or export SQL
  snapshots.

## Failure shape

- Loader defects, repository drift, schema DSL bugs, or migration execution failures surface through this slice.

## Debug first

- Start with `MigrationRepository.php` participates directly in the behavior owned by this folder.
- Start with `MigrationRunner.php` participates directly in the behavior owned by this folder.
