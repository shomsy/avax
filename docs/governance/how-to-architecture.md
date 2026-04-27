# Architecture Governance Mirror

Canonical rule source: [AI Prompts/how-to-architecture.md](/home/shomsy/projects/avax/AI%20Prompts/how-to-architecture.md:1)

This mirror exists for the new `framework/System` migration slice.

Rules enforced in this iteration:

- `framework/System` owns lifecycle.
- `components/*` remain reusable capabilities.
- `PublicSurface/` delegates and stays small.
- `Flows/` own behavior.
- `Capabilities/` own reusable mechanisms.
- `Configuration/` assembles.
- `Foundation/` stays tiny.

See the PublicSurface extension mirror in [how-to-architecture-extension.md](/home/shomsy/projects/avax/docs/governance/how-to-architecture-extension.md:1).
