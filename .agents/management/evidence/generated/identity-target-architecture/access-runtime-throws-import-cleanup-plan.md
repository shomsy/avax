# Access Runtime Throws Import Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

- Replace fully-qualified `@throws \Avax\...` entries in `AccessRuntime` with imported
  exception names.
- Preserve runtime behavior.

## Reason

After previous import cleanups, `AccessRuntime` is the only remaining production Identity
file with `\Avax\Components` references.
