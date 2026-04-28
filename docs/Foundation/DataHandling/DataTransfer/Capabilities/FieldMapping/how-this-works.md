---
title: FieldMapping-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# FieldMapping How This Works

## What this folder is

This folder owns the relationship between external input names and internal data field names.

## Real commands or triggers that reach this folder

- Shape inspection through `ReadConstructorDataFields::read(...)`
- Input matching through `MatchInputToDataShape::match(...)`

## Exact upstream handoffs

- `ReadMappedInputName::read(...)`
- `MapInputNameToField::map(...)`

## The simplest story

- `MapFrom` provides an explicit external input name.
- If there is no `MapFrom`, `DataTransferConfig::inputNameFor(...)` applies the configured naming policy.
- If there is no policy, the field name is the input name.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Shape as ReadConstructorDataFields::read
    participant ReadName as ReadMappedInputName::read
    participant Match as MatchInputToDataShape::match
    participant Map as MapInputNameToField::map
    Shape ->> ReadName: field + attributes
    ReadName -->> Shape: input name
    Match ->> Map: shape fields
    Map -->> Match: input-name-to-field map
```

## Debug first

If a field is missing even though the input contains data, check `MapFrom` and the configured naming policy first.
