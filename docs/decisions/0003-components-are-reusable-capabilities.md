# ADR 0003: Components Are Reusable Capabilities

## Status

Accepted

## Context

Existing code mixes reusable capability code with runtime assumptions and bootstrap shortcuts.

## Decision

Components remain capability owners. The framework may adapt them, but components must not become runtime owners.

## Consequences

- Reuse remains possible outside one runtime model.
- Migration work can wrap existing components instead of rewriting them.
