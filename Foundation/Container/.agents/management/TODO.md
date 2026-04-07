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

- `id`: TODO-002
  `created_at`: 2026-04-07 02:21 CEST
  `updated_at`: 2026-04-07 04:05 CEST
  `status`: done
  `estimate`: large
  `actual`: large
  `outcome`: Repackage `Foundation/Container` into explicit
    `Configuration/`, `Flows/`, `Capabilities/`, and `Foundation/` lanes,
    removing `Core/` and `Features/` as canonical roots.
  `acceptance`: `Container.php` stays the public facade; runtime resolution
    lives under `Capabilities/Resolution/Kernel`; assembly lives under
    `Configuration/`; system flows are named explicitly; old generic buckets
    are no longer the canonical architecture roots.
  `links`: `Container.php`, `Flows/`, `Capabilities/`, `Configuration/`, `docs/architecture.md`, `docs/concepts/injection-and-instantiation.md`

- `id`: TODO-001
  `created_at`: 2026-04-07 02:11 CEST
  `updated_at`: 2026-04-07 02:11 CEST
  `status`: todo
  `estimate`: small
  `actual`:
  `outcome`: Migrate legacy root `how-to-*.md` documentation into `docs/`
    when those docs are next touched.
  `acceptance`: New documentation only lands under `docs/`; the root
    compatibility docs are either migrated or explicitly marked legacy
    in place.
  `links`: `AGENTS.md`, `docs/`
