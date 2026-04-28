# ADR 0002: framework/System Owns Runtime Lifecycle

## Status

Accepted

## Context

Boot, request handling, console execution, and state reset were previously spread across bootstrap scripts and
component-level code.

## Decision

`framework/System` owns boot, HTTP entry, console entry, request scope lifecycle, and application state reset.

## Consequences

- Lifecycle logic now has one explicit owner.
- Runtime adapters can depend inward on framework abstractions.
