# PublicSurface Factory Boundary Rule

## Decision

Added to:
- `how-to-architecture-extension-with-ddd.md` — Section 50
- `how-to-dependency-injection.md` — Section 15
- `how-to-design-components.md` — Section 25

PublicSurface may expose public factories only when they create public value/result objects or protect users from internal construction details. PublicSurface factories MUST NOT assemble runtime service graphs.
