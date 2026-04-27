# ADR 0005: Runtime Adapters Must Not Leak Into Core

## Status

Accepted

## Context

Runtime-specific code becomes expensive when it leaks into request, response, router, or session components.

## Decision

Adapters such as `PhpFpmRuntime` and `CliRuntime` stay behind framework runtime abstractions.

## Consequences

- Runtime-specific APIs stay isolated.
- Core flows remain readable and portable across runtimes.
