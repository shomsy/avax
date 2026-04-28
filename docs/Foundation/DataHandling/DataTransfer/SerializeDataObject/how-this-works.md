---
title: SerializeDataObject-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# SerializeDataObject How This Works

## What this folder is

This folder owns output conversion from data objects to arrays, JSON, flat arrays, `stdClass`, and JSON:API documents.

## Real commands or triggers that reach this folder

- `DataTransfer::toArray(...)`
- `DataTransfer::toJson(...)`
- Legacy DTO methods such as `AbstractDTO::toArray()`

## Exact upstream handoffs

- `DataTransfer::toArray(...)`
- `SerializeLegacyDTO::toArray(...)`

## The simplest story

- `ReadDataObject` reads visible fields from the inspected shape.
- `NormalizeDataObjectValue` recursively normalizes enums, dates, nested objects, traversables, and arrays.
- Hidden fields are removed before normalization.
- JSON encoding uses `JSON_THROW_ON_ERROR`.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant API as DataTransfer::toArray
    participant Serialize as SerializeDataObject::toArray
    participant Read as ReadDataObject::values
    participant Normalize as NormalizeDataObjectValue::normalize
    API ->> Serialize: object
    Serialize ->> Read: read visible values
    Read -->> Serialize: raw field values
    Serialize ->> Normalize: recursively normalize output
    Normalize -->> API: transport-safe array
```

## Failure shape

Circular references normalize to `null` once an object has already been seen. Depth limits also return `null` after the
configured maximum.
