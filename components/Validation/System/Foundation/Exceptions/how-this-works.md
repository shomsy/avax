---
title: Validation-Exceptions-how-this-works
owner: Validation
last_reviewed: 2026-04-26
classification: internal
---

# Validation Exceptions How This Works

## What this folder is

Owns exception types thrown when input data fails validation rules.

## Direct files in this folder

### ValidationException.php

Thrown when input data fails validation. Carries structured `metadata` array so callers can build user-facing error
responses. Provides `toArray()` and `getErrors()` for serialization.

## Debug first

- Start here when form submissions or API requests return 422 errors
- Inspect `$exception->getMetadata()` for the specific field-level violations
