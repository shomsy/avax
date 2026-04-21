# ADR-002: Flow Versus Capability

Status: accepted

Rule:

- `Flow/` owns end-to-end stories
- `Capability/` owns shared abilities used by more than one story

Extraction threshold:

- keep logic local first
- extract only when the shared shape is clearer than duplicated local truth

Consequence:

- rate limiting, recovery details, and challenge semantics stay local until proven shared
- policy engines, identity runtimes, and registries live in capabilities
