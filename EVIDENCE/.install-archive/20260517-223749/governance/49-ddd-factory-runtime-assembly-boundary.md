# DDD Factory vs Runtime Assembly Rule

## Decision

Added to:
- `how-to-architecture-extension-with-ddd.md` — Section 51
- `how-to-design-components.md` — Section 26
- `how-to-dependency-injection.md` — Section 16

A DDD factory owns meaningful creation of domain/value/result objects when construction has invariants. A DDD factory MUST NOT assemble framework runtime service graphs. If a class assembles runtime services, it belongs in ServiceProvider or Configuration/Builders.
