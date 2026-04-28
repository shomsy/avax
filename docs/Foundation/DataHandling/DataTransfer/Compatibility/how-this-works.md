---
title: Compatibility-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# Compatibility How This Works

## What this folder is

This folder keeps the old `AbstractDTO` API working while moving behavior into the DataTransfer runtime.

## Real commands or triggers that reach this folder

- `new SomeDTO(data: [...])`
- `SomeDTO::toArray()`
- `SomeDTO::toJson()`

## Exact upstream handoffs

- `ObjectHandling/DTO/AbstractDTO.php` extends `LegacyAbstractDTO`.

## The simplest story

- `LegacyAbstractDTO` accepts the old array constructor.
- `CreateLegacyDTO` hydrates the existing object with legacy config.
- `SerializeLegacyDTO` delegates output to the new serializer.
- `DTOValidationException` keeps `getErrors()` while carrying structured violations.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Old as AbstractDTO::__construct
    participant Legacy as LegacyAbstractDTO::hydrateFrom
    participant Create as CreateLegacyDTO::hydrate
    participant Runtime as ConvertInputValues::convert
    Old ->> Legacy: data array
    Legacy ->> Create: target object + data
    Create ->> Runtime: reuse conversion/validation
    Runtime -->> Old: hydrated legacy DTO
```
