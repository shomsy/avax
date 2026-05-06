---
title: system-capabilities-migrations-design-column-how-this-works
owner: foundation-database-migrations
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Migrations / Design / Column How This Works

## What this folder is

This folder groups the column-level DSL and the column rendering path.

## Real commands or triggers that reach this folder

- `Database::migrations()` or `Database::schema()` plus migration console commands reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

Column authoring and column SQL emission stay close together here while still remaining separate responsibilities.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `DSL/` holds the next narrower ownership slice below this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `DSL/` holds the next narrower ownership slice below this folder.
- `Render/` holds the next narrower ownership slice below this folder.

## Writes and side effects

- This slice can read or write migration files, mutate schema, update migration history, seed data, or export SQL
  snapshots.

## Failure shape

- Loader defects, repository drift, schema DSL bugs, or migration execution failures surface through this slice.

## Debug first

- Start with `DSL/` holds the next narrower ownership slice below this folder.
- Start with `Render/` holds the next narrower ownership slice below this folder.

