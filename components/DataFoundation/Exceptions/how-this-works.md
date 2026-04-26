---
title: DataFoundation-Exceptions-how-this-works
owner: DataFoundation
last_reviewed: 2026-04-26
classification: internal
---

# DataFoundation Exceptions How This Works

## What this folder is

Owns exception types thrown during DTO construction, property mapping, and type coercion.

## Direct files in this folder

### InvalidDTOClassException.php

Thrown when a specified DTO class does not exist or cannot be instantiated.

### InvalidPropertyException.php

Thrown when a DTO property is missing or structurally invalid during data transfer.

### InvalidTypeException.php

Thrown when a value does not match the expected type during data transfer.

### MissingPropertyException.php

Thrown when required data is absent from the input provided to a DTO constructor.

## Debug first

- Start in `InvalidDTOClassException` when `DataTransfer::create()` fails on class resolution
- Start in `MissingPropertyException` when required fields are missing from API input
- Start in `InvalidTypeException` when the wrong data type reaches a typed property
