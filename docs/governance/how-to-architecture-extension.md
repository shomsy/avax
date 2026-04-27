# PublicSurface Governance Mirror

Canonical rule source: [AI Prompts/how-to-architecture-extension.md](/home/shomsy/projects/avax/AI%20Prompts/how-to-architecture-extension.md:1)

Rules enforced in this iteration:

- `PublicSurface/` contains stable external entrypoints only.
- Public classes delegate into `Flows/`.
- Runtime adapters do not live in `PublicSurface/`.
- Request-scoped mutable state does not live in `PublicSurface/`.
- The public API for the new slice is `Avax`, `HttpKernel`, and `ConsoleKernel`.
