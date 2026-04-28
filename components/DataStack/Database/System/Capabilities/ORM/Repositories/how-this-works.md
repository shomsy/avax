---
title: system-capabilities-orm-repositories-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Repositories How This Works

## What this folder owns

This folder owns entity-centric repository helpers layered on top of `EntityManager.php`.

## The simplest story

- A caller requests `EntityManager::repository(User::class)`.
- `EntityManager.php` returns an `EntityRepository.php` instance for that entity class.
- Repository methods delegate back into `EntityManager.php` for metadata-aware reads and writes.

## Direct files in this folder

- `EntityRepository.php` is the thin repository wrapper for one entity class.

## Debug first

- Start here when repository convenience methods return the wrong entity set.
- Move to `EntityManager.php` or `Persisters/EntityPersister.php` when the repository call is only forwarding bad
  behavior downstream.

## What to remember

- Repositories are convenience surfaces, not a second persistence engine.
