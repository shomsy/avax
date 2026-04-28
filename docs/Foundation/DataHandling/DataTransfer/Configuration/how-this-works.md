---
title: Configuration-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# Configuration How This Works

## What this folder is

This folder owns runtime policies that should not be hardcoded into mapping, conversion, or validation units.

## Real commands or triggers that reach this folder

- `DataTransfer::configure(...)`
- `DataTransfer::for(..., config: ...)`
- `DataTransferConfig::legacy()`

## Exact upstream handoffs

- `DataTransfer::config()` supplies the default runtime config.

## The simplest story

- Unknown field policy is explicit.
- Naming policy is injectable.
- Value casters and validation rules are registered by class.
- Legacy config preserves old DTO behavior where unknown fields are ignored.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant API as DataTransfer::for
    participant Builder as DataTransferBuilder::create
    participant Config as DataTransferConfig
    participant Create as CreateDataObject::create
    API ->> Builder: class + config
    Builder ->> Create: input + config
    Create ->> Config: mapping/conversion/validation policy
```
