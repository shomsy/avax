---
title: ErrorReporting-how-this-works
owner: foundation-data-handling
last_reviewed: 2026-04-24
classification: internal
---

# ErrorReporting How This Works

## What this folder is

This folder owns structured failure data for data transfer operations.

## Real commands or triggers that reach this folder

- Unknown fields in `MatchInputToDataShape`
- Conversion failures in `ValueConversion`
- Validation failures in `FieldValidation`
- Instantiation failures in `InstantiateDataObject`

## Exact upstream handoffs

- `DataTransferFailure::__construct(...)`
- `DataTransferViolations::from(...)`
- `ExplainDataTransferFailure::explain(...)`

## The simplest story

- Every failure can carry many `DataTransferViolation` objects.
- Each violation has a machine code, path, human message, expected type, actual type, failed rule, and previous
  exception.
- Exception text is only a readable summary.
- Runtime callers should inspect `violations()` for source-of-truth diagnostics.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Flow as CreateDataObject::create
    participant Violations as DataTransferViolations::from
    participant Failure as DataTransferFailure::__construct
    participant Explain as ExplainDataTransferFailure::explain
    Flow ->> Violations: collect field failures
    Flow ->> Failure: throw with violations
    Failure ->> Explain: build readable message
    Failure -->> Flow: structured exception
```

## Compatibility

`DTOValidationException` keeps the old `getErrors()` API by converting structured violations into the legacy associative
array format.
