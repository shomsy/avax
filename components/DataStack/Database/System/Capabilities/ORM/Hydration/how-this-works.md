---
title: system-capabilities-orm-hydration-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Hydration How This Works

## What this folder owns

This folder owns conversion from raw database rows into managed entity objects.

## The simplest story

- `Persisters/EntityPersister.php` reads one or more rows through the Query capability.
- `Hydrator.php` looks up the entity identifier in `IdentityMap/IdentityMap.php`.
- If the entity is already managed, the existing instance is reused.
- Otherwise a new object is created, populated, and registered in the identity map.

## Direct files in this folder

- `Hydrator.php` is the row-to-entity mapper for ORM reads.

## Debug first

- Start here when reads produce duplicate objects for the same identifier.
- Start here when a row contains the right data but the entity instance is not populated correctly.

## What to remember

- Hydration is read-side object assembly.
- It relies on metadata for property mapping and on the identity map for instance reuse.
