# React Architecture Profile

Use this overlay for React frontends.

Apply it together with `../../architecture-standard.md`,
`../languages/javascript.md` or `../languages/typescript.md`, and
`../../../profiles/frameworks/react.md`.

## Core Law

- this profile narrows the universal vertical-slice law
- React component trees do not replace feature ownership
- folder still says flow or capability
- file still says responsibility
- function still says exact action

## Core Stance

- React components are not the whole architecture; they are one rendering
  surface inside feature slices
- keep the repo screaming pages, features, or capabilities before generic
  component taxonomies
- shared UI primitives may live in a dedicated design-system or `ui/` area, but
  product behavior should stay under feature ownership

## Recommended Feature Shape

- each product feature should have one obvious composition entry
- keep presentational components, state, server interactions, mutations, and
  diagnostics near the owning feature
- split container/presentation, hooks/actions, or state/view only when the
  distinction reduces mental noise
- feature-local components are preferred over prematurely shared global
  components

Example feature:

```text
checkout/
  index.tsx
  components/
  state/
  queries/
  actions/
  effects/
  styles/
```

## Boundary Rules

- components should render and delegate; they should not become the only home
  for domain and integration logic
- hooks should have clear ownership; avoid a giant global `hooks/` folder as
  the real architecture
- server calls, cache updates, and optimistic flows should be grouped by the
  feature they affect
- shared `ui/` or `design-system/` code must stay stable and generic enough to
  justify being cross-feature

## Anti-Patterns

- the repo story is only `components/`, `hooks/`, `pages/`, `utils/`
- business logic hidden in custom hooks with no feature owner
- route-level features depending on deep internals of another feature's
  components
- a global state folder becoming the accidental source of truth for unrelated
  features
