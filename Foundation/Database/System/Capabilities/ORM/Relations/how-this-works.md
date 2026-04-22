---
title: system-capabilities-orm-relations-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Relations How This Works

## What this folder owns

This folder owns shared relation taxonomy for ORM metadata.

## The simplest story

- `Metadata/AttributeMetadataReader.php` reads a relation attribute from an entity property.
- It converts that attribute into `RelationMetadata.php`.
- `RelationKind.php` gives the normalized enum that the rest of the ORM can switch on.

## Direct files in this folder

- `RelationKind.php` defines the supported ORM relation shapes.

## Debug first

- Start here when relation metadata exists but the relation kind is normalized incorrectly.

## What to remember

- This folder describes relation shape, not loading behavior by itself.
