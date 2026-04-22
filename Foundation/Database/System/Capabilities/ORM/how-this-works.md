---
title: system-capabilities-orm-how-this-works
owner: foundation-database-orm
last_reviewed: 2024-10-22
classification: internal
---

# System / Capabilities / ORM How This Works

Extension for attribute-driven entities (#[Column], #[Id]).

## Triggers

$db->entityManager()->find(User::class, 1).

## Upstream

Database::entityManager() → EntityManager w/ persister/hydrator/metadata.

## Main units

- EntityManager.php: Facade for find/findBy/insert/update/delete/refresh.
- Persisters/EntityPersister.php: Query-backed CRUD.
- Hydration/Hydrator.php.
- Metadata/EntityMetadata.php + AttributeMetadataReader.php.
