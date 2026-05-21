# Root Identity Runtime Import Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Remove fully-qualified `new \Avax\...` construction from root Identity runtime assembly.
- Add explicit imports for Access/Tenancy requirement collaborators.
- Preserve runtime assembly behavior exactly.

## Reason

`refactor-identity.md` lists “No fully-qualified class names inside methods when imports
are appropriate” as a known correction. The root Identity runtime builder is a small,
bounded production-code target for that cleanup.

## Out Of Scope

- No Auth assembly rewrite.
- No dependency graph split.
- No PublicSurface API change.
