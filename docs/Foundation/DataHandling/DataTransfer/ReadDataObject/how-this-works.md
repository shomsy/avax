---
title: ReadDataObject-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# ReadDataObject How This Works

## What this folder is

This folder owns reading values from an existing data object before output normalization.

## Real commands or triggers that reach this folder

- `SerializeDataObject::toArray(...)`

## Exact upstream handoffs

- `ConvertDataObjectToArray::convert(...)` -> `ReadDataObject::values(...)`

## The simplest story

- Inspect the object's shape.
- Filter fields through field visibility.
- Read initialized public/promoted values.
- Hand raw values to normalization.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Array as ConvertDataObjectToArray::convert
    participant Read as ReadDataObject::values
    participant Visible as ReadVisibleDataFields::read
    participant Values as ReadDataObjectValues::read
    Array ->> Read: object
    Read ->> Visible: filter hidden fields
    Read ->> Values: read field values
    Values -->> Array: raw values
```
