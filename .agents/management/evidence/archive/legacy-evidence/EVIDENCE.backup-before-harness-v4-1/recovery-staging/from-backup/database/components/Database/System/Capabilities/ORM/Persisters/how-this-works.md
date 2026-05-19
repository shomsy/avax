---
title: system-capabilities-orm-persisters-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Persisters How This Works

## What this folder owns

This folder owns translation between ORM intent and Query capability calls.

## The simplest story

- `UnitOfWork/UnitOfWork.php` decides that an entity must be inserted, updated, deleted, refreshed, or searched.
- `EntityPersister.php` reads entity metadata.
- It builds the right QueryBuilder flow through the `Query` capability.
- Read operations hand rows to `Hydration/Hydrator.php`; write operations update identifiers when needed.

## Direct files in this folder

- `EntityPersister.php` is the SQL-facing persistence adapter for ORM operations.

## Debug first

- Start here when entity metadata is correct but the generated query is wrong.
- Start here when `insertGetId`, `update`, `delete`, or `findBy` behavior diverges from entity mapping.

## What to remember

- This folder depends on Query instead of bypassing it.
- Persisters translate entity semantics into SQL semantics.

