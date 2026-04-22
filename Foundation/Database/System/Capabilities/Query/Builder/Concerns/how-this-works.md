---
title: system-capabilities-query-builder-concerns-how-this-works
owner: foundation-database-query
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / Query / Builder / Concerns How This Works

## What this folder owns

This folder keeps `Builder/QueryBuilder.php` readable by splitting fluent methods into local traits.

## The simplest story

- `QueryBuilder.php` imports only the traits it needs.
- Each trait owns one slice of fluent behavior such as conditions, joins, ordering, grouping, aggregates, control flow,
  or advanced mutations.
- Terminal methods in `QueryBuilder.php` still own execution; the traits only shape state and fluent ergonomics.

## Direct files in this folder

- `HasConditions.php` owns `where*` style filtering helpers.
- `HasJoins.php` owns join construction.
- `HasOrders.php` owns ordering helpers.
- `HasGroups.php` owns grouping and having-style helpers.
- `HasAggregates.php` owns aggregate shortcuts.
- `HasAdvancedMutations.php` owns helpers such as upsert-like mutation flows.
- `HasControlStructures.php` owns control helpers such as `when`.
- `HasSoftDeletes.php` owns soft-delete-aware filters.
- `Macroable.php` owns runtime builder extension hooks.

## Debug first

- Start in the trait that matches the fluent method that produced the wrong state.
- Move back to `Builder/QueryBuilder.php` when the SQL is correct in state but wrong at execution time.

## What to remember

- Schema operations no longer belong here.
- Traits shape the builder API; they do not replace the builder as the execution owner.
