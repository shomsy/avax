# ADR 0006: Request State Must Be Scoped

## Status

Accepted

## Context

Long-lived runtimes make implicit request globals unsafe.

## Decision

Request-local data lives in `RequestScope` and explicit `RuntimeContext`. Both are resettable through
`StateResetRegistry`.

## Consequences

- Two requests cannot share scoped values by accident.
- Worker-safe runtime adapters have a clear reset boundary.
