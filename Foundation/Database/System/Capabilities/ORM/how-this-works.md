---
title: ORM-how-this-works
owner: Database
last_reviewed: 2026-04-26
classification: internal
---

# ORM How This Works

## What this folder is

This folder owns the Object-Relational Mapping layer: entity lifecycle, persistence, hydration, metadata reading, and
repository access.

## Real commands or triggers that reach this folder

- Any service that injects `EntityStore` to persist or query domain entities
- `DatabaseBuilder` assembles the `EntityStore` during bootstrap

## The simplest story

- A service calls `$entityStore->find(User::class, 42)`
- `EntityPersister` builds and executes the SQL query
- `Hydrator` maps the raw row into a typed entity object
- The hydrated entity is returned to the caller

## Direct files in this folder

### EntityStore.php

Coordinates entity lifecycle: find, persist, update, delete, and refresh. Delegates all database interaction to
`EntityPersister`.

### Entity.php

Base class for domain entities. Derives table names automatically from the class short name.

### Repository.php

Abstract base for custom entity repositories. Provides `findById`, `findBy`, `save`, `delete`, `exists`, `count` built
on `QueryBuilder`.

### EntityManager.php

**Deprecated.** Forwarding alias to `EntityStore`. Will be removed once all consumers are updated.

## Child folders in this folder

### Hydration/

Converts raw database rows into typed entity objects.

### Metadata/

Reads PHP attributes from entity classes to discover column mappings and relationships.

### Persisters/

Owns the actual SQL execution for insert, update, delete, and select operations.

### Repositories/

Provides `EntityRepository`, a generic repository bound to a specific entity class.

### Relations/

Describes and resolves entity relationships (one-to-many, many-to-many, etc.).

## Debug first

- Start in `EntityPersister` when entities fail to save or load
- Start in `Hydrator` when property types mismatch after hydration
- Start in `AttributeMetadataReader` when column mappings are wrong
