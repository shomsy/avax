# Next.js Architecture Profile

Use this overlay for Next.js applications.

Apply it together with `../../architecture-standard.md`,
`../languages/javascript.md` or `../languages/typescript.md`,
`../../../profiles/frameworks/nextjs.md`, and usually the React architecture
overlay.

## Core Law

- this profile narrows the universal vertical-slice law
- Next.js routing terms are ingress lanes, not the full business architecture
- folder still says flow or capability
- file still says responsibility
- function still says exact action

## Core Stance

- `app/` or `pages/` is the ingress tree, not the full business architecture
- route segments should compose owned feature modules rather than swallow all
  behavior inline
- server and client boundaries must be explicit in both code and folder shape

## App Router Shape

- keep route segment files thin: compose feature modules, metadata, and
  navigation rules there
- put heavy feature logic in named modules outside the route segment shell when
  the feature is non-trivial
- group server actions, loaders, mutations, and cache invalidation near the
  feature that owns them
- client-only interaction modules should be clearly separated from server-only
  data and policy modules

Example route-backed feature:

```text
app/(dashboard)/billing/page.tsx
features/billing/
  server/
  client/
  components/
  actions/
  contracts/
```

## Runtime Boundary Rules

- route segment code should make SSR, ISR, CSR, and edge or node choices
  obvious
- caching, revalidation, and mutation paths are architecture concerns, not
  implementation details
- do not let server-only code leak into client components or client bundles by
  folder ambiguity
- treat `app/api/` handlers as ingress adapters that delegate into owned
  feature modules

## Anti-Patterns

- large route segment files that own fetching, validation, mutation, rendering,
  and cache invalidation together
- `lib/` or `utils/` becoming the real application layer
- server actions scattered without feature ownership
- unclear client/server folder boundaries that force readers to inspect imports
  to discover runtime
