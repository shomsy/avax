---
title: CreateDataObject-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# CreateDataObject How This Works

## What this folder is

This folder owns the creation flow: target class plus input becomes one typed object or a structured failure.

## Real commands or triggers that reach this folder

- `DataTransfer::create(class: ..., input: ...)`
- `DataTransfer::tryCreate(class: ..., input: ...)`
- `CreateLegacyDTO::hydrate(...)` reuses the same matching, conversion, and validation units.

## Exact upstream handoffs

- `DataTransfer::create(...)`
- function: `CreateDataObject::create(...)`

## The simplest story

- `ReadTargetDataShape` asks inspection for the class shape.
- `MatchInputToDataShape` maps external keys to fields and records unknown fields.
- `ResolveFieldInputValue` applies defaults, nullability, and missing-field state.
- `ConvertInputValues` coerces values to declared types.
- `ValidateInputValues` returns structured violations.
- `InstantiateDataObject` creates the final object.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Create as CreateDataObject::create
    participant Shape as ReadTargetDataShape::read
    participant Match as MatchInputToDataShape::match
    participant Resolve as ResolveFieldInputValue::resolve
    participant Convert as ConvertInputValues::convert
    participant Validate as ValidateInputValues::validate
    participant Instantiate as InstantiateDataObject::instantiate
    Create ->> Shape: read class shape
    Create ->> Match: match input names
    Create ->> Resolve: resolve every field value
    Create ->> Validate: required/null checks
    Create ->> Convert: convert declared types
    Create ->> Validate: field rules
    Create ->> Instantiate: build object
```

## Failure shape

The flow rejects unknown input by default. Legacy DTO hydration uses `DataTransferConfig::legacy()` and ignores unknown
fields to preserve the old public API.
