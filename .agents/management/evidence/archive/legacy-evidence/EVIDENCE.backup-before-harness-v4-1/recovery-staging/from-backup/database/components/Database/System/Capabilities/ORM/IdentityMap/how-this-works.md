---
title: system-capabilities-orm-identity-map-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Identity Map How This Works

## What this folder owns

This folder owns managed entity identity. It guarantees that one entity class plus one identifier resolves to one
in-memory object instance.

## The simplest story

- `Hydration/Hydrator.php` or `UnitOfWork/UnitOfWork.php` writes a managed entity into `IdentityMap.php`.
- `EntityManager.php::find()` checks the identity map before hitting the database.
- Subsequent reads for the same class and identifier return the same instance.

## Direct files in this folder

- `IdentityMap.php` stores and resolves managed entities by class and identifier.

## Debug first

- Start here when the same database row becomes multiple PHP objects.
- Start here when `EntityManager::find()` unexpectedly ignores an already managed instance.

## What to remember

- This is ORM ownership, not Query ownership.
- Identity stability is what lets the ORM behave like an identity-based unit of work instead of a row mapper.

