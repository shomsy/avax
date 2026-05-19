# Architecture Profile: Layered

Version: 1.0.0
Status: Normative / Optional
Applies when: project `AGENTS.md` declares `layered` architecture.

## What This Profile Adds

Traditional horizontal layer separation. Each layer has a defined direction
of dependency. This profile is NOT the default — it must be explicitly selected.

## Layered Rules

- dependencies flow downward only: presentation → application → domain → infrastructure
- domain layer has no dependency on framework or persistence
- application layer orchestrates domain objects and calls infrastructure ports
- presentation layer (HTTP, CLI, queue) delegates to application layer
- cross-layer shortcuts are forbidden without documented exception

## Anti-Patterns

- domain entities importing HTTP request objects
- infrastructure calling presentation logic
- bypassing the application layer from presentation to persistence
