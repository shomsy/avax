---
title: system-capabilities-orm-unit-of-work-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Unit Of Work How This Works

## What this folder owns

This folder owns deferred persistence decisions for ORM-managed entities: what is new, what is dirty, what is removed,
and in what order those operations flush.

## The simplest story

- `EntityManager.php::persist()` records an entity in `UnitOfWork.php`.
- `EntityManager.php::remove()` marks an entity for deletion.
- `EntityManager.php::flush()` asks `UnitOfWork.php` to execute pending inserts, updates, and deletes.
- `UnitOfWork.php` delegates the physical SQL work to `Persisters/EntityPersister.php` and keeps `IdentityMap.php`
  synchronized.

## Direct files in this folder

- `UnitOfWork.php` is the write-side coordinator for ORM-managed entities.

## Debug first

- Start here when inserts, updates, or deletes happen in the wrong order.
- Start here when a `persist()` or `remove()` call does not survive until `flush()`.

## What to remember

- This folder owns deferred ORM writes.
- Query itself no longer owns deferred identity behavior.

