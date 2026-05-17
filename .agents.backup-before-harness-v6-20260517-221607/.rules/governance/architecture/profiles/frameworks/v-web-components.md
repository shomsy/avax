# Vanilla Web Components Architecture Profile

Use this overlay for native Web Components and custom-element systems.

Apply it together with `../../architecture-standard.md`,
`../languages/javascript.md` or `../languages/typescript.md`, and
`../../../profiles/frameworks/v-web-components.md`.

## Core Law

- this profile narrows the universal vertical-slice law
- component folders still have to read by capability first
- file and function naming still obey the same honesty law
- Web Component internals do not justify generic junk-drawer architecture

## Core Stance

- each complex component is a feature slice with one obvious public facade
- component architecture should scream capability first, then internal
  responsibilities
- clean separation should happen inside the component root, not as repo-wide
  generic folders

## Recommended Component Shape

- keep one public facade such as `index.js` or a component-named entry file
- keep the custom element class thin enough that state, rendering, and effects
  stay legible
- use `state/`, `render/`, `actions/`, `effects/`, `styles/`, and
  `diagnostics/` only when the component complexity justifies them
- keep attribute and property contracts, event emission, and Shadow DOM
  boundaries explicit

Example component:

```text
user-avatar/
  index.js
  component.js
  state/
  render/
  actions/
  effects/
  styles/
```

## Boundary Rules

- registration of the custom element should stay distinct from the internal
  runtime flow when that reduces ambiguity
- a component's public attributes, properties, slots, and events are the
  compatibility-sensitive surface
- DOM patching, timers, observers, async work, and subscriptions belong to the
  owned lifecycle boundary
- cross-component coordination should go through explicit contracts, not deep
  DOM reach-in or hidden global registries

## Anti-Patterns

- one giant component class that owns state, rendering, effects, styling, and
  integration logic without internal boundaries
- generic `components/` and `helpers/` buckets as the only architecture
- public facade files becoming large command centers
- detached components continuing to mutate shared DOM or state after teardown
