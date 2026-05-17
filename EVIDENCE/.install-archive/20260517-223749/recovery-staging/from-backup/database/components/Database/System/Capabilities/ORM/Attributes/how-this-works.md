---
title: system-capabilities-orm-attributes-how-this-works
owner: foundation-database-orm
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities / ORM / Attributes How This Works

## What this folder owns

This folder owns the PHP 8 attribute language that entity classes use to declare ORM mapping.

## The simplest story

- An entity class declares attributes such as `#[Entity]`, `#[Table]`, `#[Id]`, `#[Column]`, and relation attributes.
- `Metadata/AttributeMetadataReader.php` reflects those attributes.
- The reader converts them into `EntityMetadata`, `FieldMetadata`, and `RelationMetadata`.

## Direct files in this folder

- `Entity.php` marks a class as ORM-managed and can declare a custom repository.
- `Table.php` overrides the physical table name.
- `Column.php` maps one property to one column.
- `Id.php` marks the identifier field.
- `GeneratedValue.php` marks database-generated identifiers.
- `ManyToOne.php`, `OneToMany.php`, `OneToOne.php`, and `ManyToMany.php` declare relation shape.
- `JoinColumn.php` declares the physical join column used by a relation.

## Debug first

- Start here when an entity class is annotated correctly in intent but metadata still looks wrong.
- Then move to `Metadata/AttributeMetadataReader.php` to confirm the reader is interpreting the attribute instances the
  right way.

## What to remember

- These files declare mapping intent only.
- They do not execute SQL or manage object state by themselves.

