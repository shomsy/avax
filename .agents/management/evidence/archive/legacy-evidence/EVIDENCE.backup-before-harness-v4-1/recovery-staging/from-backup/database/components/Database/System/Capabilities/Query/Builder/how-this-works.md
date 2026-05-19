---
title: system-capabilities-query-builder-how-this-works
owner: foundation-database-query
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Query / Builder How This Works

## What this folder is

This folder owns the main fluent QueryBuilder type and its join helper.

## Real commands or triggers that reach this folder

- `Database::table(...)`, `Database::query()`, and any path that needs a connection-bound builder or query execution
  reach this folder.

## Exact upstream handoffs

- The parent slice hands work into this folder when it needs the behavior owned here.
- The files in this folder do the local work and return control upstream when their responsibility is complete.

## How this folder works

The builder returns clones as callers add clauses, then terminal methods hand the accumulated state to grammar and
execution layers.

## The simplest story

- A caller reaches the parent Database surface or the owning parent folder.
- This folder handles the one responsibility it owns.
- The result returns upstream or moves to the next local slice.

## The first important path

- The caller enters through the Database surface or the parent folder.
- `Concerns/` holds the next narrower ownership slice below this folder.
- Control returns to the caller or the next local slice once this folder finishes its job.

## Main units in this folder

- `Concerns/` holds the next narrower ownership slice below this folder.
- `JoinClause.php` participates directly in the behavior owned by this folder.
- `QueryBuilder.php` participates directly in the behavior owned by this folder.

## Writes and side effects

- This slice can read rows, mutate data, compile SQL, or schedule deferred writes depending on the folder below it.

## Failure shape

- Invalid criteria, wrong SQL shape, execution failures, or deferred-write defects surface through this slice.

## Debug first

- Start with `Concerns/` holds the next narrower ownership slice below this folder.
- Start with `JoinClause.php` participates directly in the behavior owned by this folder.
- Start with `QueryBuilder.php` participates directly in the behavior owned by this folder.

