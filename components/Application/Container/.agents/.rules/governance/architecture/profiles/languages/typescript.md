# TypeScript Architecture Profile

Use this overlay for TypeScript applications and packages.

Apply it together with `../../architecture-standard.md`,
`../../../profiles/languages/typescript.md`, and usually the JavaScript
architecture profile as the module baseline.

## Core Law

- this profile narrows the universal vertical-slice law
- folder still says flow or capability
- file still says responsibility
- function still says exact action
- type structure must reinforce the slice instead of replacing it

## Type-Shaped Slice Boundaries

- keep DTOs, result types, schemas, and public contracts next to the owning
  feature or package boundary
- avoid repo-wide `types/` or `interfaces/` drawers as the default
  architecture; those usually become junk drawers with weak ownership
- if a slice publishes a public type surface, keep it small and intentional
- if runtime schemas exist, keep them close enough to the boundary that the
  validation path is obvious
- type structure should reinforce the feature boundary, not erase it

## Interior Layers

- use separate interior modules for contracts, decisions, adapters, and runtime
  effects only when each distinction reduces mental noise
- keep transport, framework glue, and persistence dependent on the feature
  contract, not the other way around
- if a slice has both runtime and type-only helpers, name and place them so the
  reader can tell which one emits JavaScript and which one does not
- avoid barrel-heavy layouts that make every internal file look public
- prefer one explicit slice entrypoint over many half-public deep imports

## Architecture Rules For Large TS Systems

- use discriminated unions, result shapes, and schema-boundary modules to make
  state transitions and external contracts obvious
- if a slice spans frontend and backend code, keep cross-runtime contracts
  narrow and versionable
- generated types may exist, but the generated folder must not become the
  hidden design authority
- if a package ships types plus runtime code, the packaging boundary should
  clearly tell consumers what is stable API and what is internal machinery
- do not create a parallel "type architecture" disconnected from the runtime
  architecture

## Anti-Patterns

- a monolithic global `types/` directory that mirrors the whole repo
- type-only abstractions that hide the real runtime flow
- path alias sprawl that makes every feature import every other feature
- runtime validators far away from the public boundary they are supposed to
  protect
