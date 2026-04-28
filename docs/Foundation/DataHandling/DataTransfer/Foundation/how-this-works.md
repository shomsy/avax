---
title: Foundation-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# Foundation How This Works

## What this folder is

This folder owns tiny value objects used across the runtime where primitive strings or arrays would obscure intent.

## Real commands or triggers that reach this folder

- `CreateDataObject::create(...)`
- `MatchInputToDataShape::match(...)`
- `ConvertValueToDeclaredType::convert(...)`

## Exact upstream handoffs

- Input normalization uses `InputData`.
- Nested diagnostics use `FieldPath`.

## The simplest story

- `InputData` normalizes arrays, objects, traversables, JSON serializable objects, and `toArray()` objects.
- `FieldPath` tracks nested failure paths.
- `ClassName`, `FieldName`, and `OutputData` make intent explicit at boundaries.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Create as CreateDataObject::create
    participant Input as InputData::from
    participant Path as FieldPath::append
    participant Failure as DataTransferViolation
    Create ->> Input: normalize raw input
    Create ->> Path: build field path
    Path -->> Failure: stable diagnostic path
```
