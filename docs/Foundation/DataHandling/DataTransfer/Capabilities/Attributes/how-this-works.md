---
title: Attributes-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# Attributes How This Works

## What this folder is

This folder owns the small metadata DSL used by the data transfer runtime.

## Real commands or triggers that reach this folder

- Reflection through `ReadDataFieldAttributes::read(...)`

## Exact upstream handoffs

- `ReadConstructorDataFields::read(...)`
- `ReadPublicDataFields::read(...)`

## The simplest story

- `MapFrom` changes the input key.
- `ListOf` declares array item data object type.
- `CastWith` delegates conversion.
- `Hidden` removes output fields.
- `Optional`, `Required`, and `DefaultValue` tune missing-field behavior.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Inspect as ReadDataFieldAttributes::read
    participant Field as DataField::__construct
    participant Runtime as CreateDataObject::create
    Inspect ->> Field: instantiated attributes
    Field -->> Runtime: metadata for mapping/conversion/validation
```
