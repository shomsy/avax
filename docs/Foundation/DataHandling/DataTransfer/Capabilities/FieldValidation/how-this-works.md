---
title: FieldValidation-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# FieldValidation How This Works

## What this folder is

This folder owns validation after mapping and around conversion. It reports field violations without instantiating
partially valid objects.

## Real commands or triggers that reach this folder

- `ValidateInputValues::validate(...)`

## Exact upstream handoffs

- `CreateDataObject::create(...)` calls required/null checks before conversion and field rules after conversion.

## The simplest story

- Required validation handles missing fields.
- Nullable validation handles explicit or default `null`.
- Attribute rules run after conversion so validators see typed values.
- Custom configured validation rules can be registered by attribute class.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Create as CreateDataObject::create
    participant Validate as ValidateInputValues::validate
    participant Required as ValidateRequiredField::validate
    participant Nullable as ValidateNullableField::validate
    participant Rules as ValidateFieldRules::validate
    Create ->> Validate: resolved values
    Validate ->> Required: missing field check
    Validate ->> Nullable: nullability check
    Validate ->> Rules: attribute/config rules
    Rules -->> Validate: violations
```

## Failure shape

Validation failures become `DataValidationFailed`. Legacy DTOs repackage the same violations into
`DTOValidationException::getErrors()`.
