# JavaScript Architecture Profile

Use this overlay for module-based JavaScript applications and packages.

Apply it together with `../../architecture-standard.md`,
`../../../profiles/languages/javascript.md`, and any relevant framework
overlay.

## Core Law

- this profile narrows the universal vertical-slice law
- folder still says flow or capability
- file still says responsibility
- function still says exact action
- JavaScript flexibility must not weaken naming honesty

## Outer Shape

- the repo should read by feature, capability, or runtime lane first
- top-level buckets such as `components/`, `services/`, `utils/`, or `lib/`
  are acceptable only when they are truly stable shared foundations rather than
  the main app story
- browser, server, worker, and build concerns should stay explicit when they
  differ materially
- route, page, component, or runtime entrypoints should remain thin and obvious

## Slice Interior

- each slice should have one obvious entry or public facade
- use `state/`, `render/`, `actions/`, `effects/`, `adapters/`, `store/`, or
  `diagnostics/` only when the slice actually needs those distinctions
- pure transformations should sit closer to the owned state or decision logic;
  side effects should sit at the slice edge
- module-level mutable state is allowed only when the slice explicitly owns its
  lifecycle and cleanup
- do not hide the real flow behind event soup or ambient registries

Example UI-heavy slice:

```text
checkout/
  index.js
  state/
  render/
  actions/
  effects/
  styles/
  diagnostics/
```

## Library And Runtime Component Shape

- reusable packages should separate public API, declaration or registration,
  runtime resolution, execution, and diagnostics when those concerns all exist
- if a component exposes a builder, DSL, or registry, keep declaration distinct
  from runtime execution
- caches and compiled artifacts are derived from canonical inputs
- compatibility-sensitive entrypoints should stay narrow enough that a caller
  does not need internal folder knowledge

Example runtime component:

```text
router/
  index.js
  registration/
  resolution/
  execution/
  diagnostics/
```

## Browser And Server Boundary Rules

- do not mix browser-only and server-only behavior in the same slice interior
  without an explicit boundary
- when the same feature spans browser and server, keep the shared contract
  narrow and keep environment-specific adapters separate
- background workers, service workers, or job processors should look like owned
  runtime lanes, not hidden helpers
- failure mapping belongs at the ingress owner, not randomly across render and
  action modules

## Anti-Patterns

- a giant global `components/` tree as the only architecture
- repo-wide `hooks/`, `services/`, `stores/`, or `utils/` buckets with mixed
  ownership
- one module that owns state, rendering, effects, storage, and transport at the
  same time
- architecture that treats build-time, runtime, and feature logic as the same
  layer
