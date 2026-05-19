---
title: system-capabilities-orm-metadata-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Metadata How This Works

## What this folder owns

This folder owns reflection-based mapping discovery and the normalized metadata objects that the rest of the ORM reads.

## The simplest story

- `AttributeMetadataReader.php` reflects one entity class.
- It reads `Attributes/*` declarations from the class and its properties.
- It creates `EntityMetadata.php`, `FieldMetadata.php`, and `RelationMetadata.php`.
- `EntityManager.php`, `Hydrator.php`, and `EntityPersister.php` all rely on those metadata objects instead of rereading
  attributes themselves.

## Direct files in this folder

- `AttributeMetadataReader.php` is the reflection reader and metadata cache.
- `EntityMetadata.php` is the aggregate mapping for one entity class.
- `FieldMetadata.php` describes one mapped scalar field.
- `RelationMetadata.php` describes one mapped relation.

## Debug first

- Start in `AttributeMetadataReader.php` when a field, identifier, table, or relation is missing.
- Start in the metadata value objects when a downstream consumer reads the wrong normalized shape.

## What to remember

- This folder turns attributes into stable runtime metadata.
- The rest of the ORM should consume these objects, not duplicate reflection logic.

