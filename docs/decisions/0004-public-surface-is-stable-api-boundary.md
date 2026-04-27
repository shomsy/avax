# ADR 0004: PublicSurface Is the Stable API Boundary

## Status

Accepted

## Context

The repository lacked a clear filesystem boundary between stable external entrypoints and internal machinery.

## Decision

Stable framework entrypoints live in `framework/System/PublicSurface/` and must delegate into internal flows.

## Consequences

- `Avax`, `HttpKernel`, and `ConsoleKernel` are now explicit public entrypoints.
- Internal implementation can evolve behind those entrypoints.
