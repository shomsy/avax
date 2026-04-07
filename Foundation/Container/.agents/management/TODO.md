# TODO

Canonical active implementation queue.

## Rules

- keep newest items first
- keep each item outcome-oriented
- include acceptance criteria
- include owner only when needed
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | blocked | done
- `estimate`:
- `actual`:
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: TODO-003
  `created_at`: 2026-04-07 13:30 CEST
  `updated_at`: 2026-04-07 15:44 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Converge `Foundation/Container` to the final DX-first
    `DependencyInjection/` architecture, remove the legacy
    `Flow/` + `Capability/` tree, harden runtime behavior, rewrite shipped
    docs to the new vocabulary, and add Docker-backed smoke coverage for the
    final flow entries and work areas.
  `acceptance`: The public facade stays thin; only the final canonical tree and
    namespace remain; no legacy architectural vocabulary survives in `docs/`;
    Docker PHP lint is green; `tests/run-smoke-tests.sh` passes across the
    shipped flow entries and internal work areas.
  `links`: `Container.php`, `ContainerInterface.php`, `DependencyInjection/`, `docs/architecture.md`, `docs/Container.md`, `tests/run-smoke-tests.sh`

- `id`: TODO-002
  `created_at`: 2026-04-07 02:21 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Repackage `Foundation/Container` into explicit
    `DependencyInjection/Configuration/`, `DependencyInjection/Flow/`,
    `DependencyInjection/Capability/`, and
    `DependencyInjection/Foundation/` lanes, removing `Core/` and
    `Features/` as canonical roots.
  `acceptance`: `Container.php` stays the public facade; runtime resolution
    lives under `DependencyInjection/Capability/Resolution/Kernel`;
    assembly lives under `DependencyInjection/Configuration/`; system flows are
    named explicitly; old generic buckets are no longer the canonical
    architecture roots.
  `links`: `Container.php`, `DependencyInjection/Flow/`, `DependencyInjection/Capability/`, `DependencyInjection/Configuration/`, `docs/architecture.md`, `docs/concepts/injection-and-instantiation.md`

- `id`: TODO-001
  `created_at`: 2026-04-07 02:11 CEST
  `updated_at`: 2026-04-07 12:47 CEST
  `status`: done
  `estimate`: small
  `actual`: small
  `outcome`: Mark the legacy root `how-to-*.md` documentation as compatibility
    references and point contributors to the governed standards in
    `.agents/.rules` and `docs/`.
  `acceptance`: The root compatibility docs are explicitly legacy in place,
    and authoritative guidance is under `.agents/.rules` plus `docs/`.
  `links`: `AGENTS.md`, `how-to-coding-standards.md`, `how-to-code-review.md`, `how-to-document.md`, `docs/`
