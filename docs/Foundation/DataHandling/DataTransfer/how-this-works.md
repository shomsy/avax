---
title: DataTransfer-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# DataTransfer How This Works

## What this folder is

This folder owns the runtime for turning external arrays or objects into typed data objects and reading those objects
back into transport-safe output.

## Real commands or triggers that reach this folder

- HTTP request DTO creation through `Request::fromRequest(...)`
- Application code calling `DataTransfer::create(...)`
- Legacy DTO construction through `new SomeDTO(data: [...])`

## Exact upstream handoffs

- `Foundation/DataHandling/DataTransfer/DataTransfer.php`
- function: `DataTransfer::create(...)`
- `Foundation/DataHandling/DataTransfer/Compatibility/LegacyAbstractDTO.php` -> `CreateLegacyDTO::hydrate(...)`

## The simplest story

- Input arrives as an array or object.
- `DataTransfer` reads the target class shape once.
- The runtime maps names, converts values, validates fields, and instantiates the object.
- Serialization walks the same shape model and removes hidden fields.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant API as DataTransfer::create
    participant Create as CreateDataObject::create
    participant Inspect as InspectDataShape::inspect
    participant Convert as ConvertInputValues::convert
    participant Instantiate as InstantiateDataObject::instantiate
    API ->> Create: class + input
    Create ->> Inspect: read DataShape
    Create ->> Convert: convert matched values
    Convert -->> Create: typed values or violations
    Create ->> Instantiate: create object
    Instantiate -->> API: typed data object
```

## Failure shape

Failures are reported through `DataTransferFailure` with `DataTransferViolations`. The exception message is readable,
but the violation objects are the source of truth.
