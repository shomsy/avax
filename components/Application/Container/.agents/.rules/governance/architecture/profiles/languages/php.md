# PHP Architecture Profile

Use this overlay when shaping PHP applications, packages, and infrastructure
components.

Apply it together with `../../architecture-standard.md`,
`../../../profiles/languages/php.md`, and any relevant framework overlay.

## Core Law

- the PHP repo should still read by flow and feature first
- business slices and technical slices obey the same naming law
- facades, kernels, pipelines, controllers, commands, and jobs are acceptable
  only when they describe a real owned role in the slice
- if a PHP pattern makes the structure less intuitive, simplify the pattern
  before adding more files

## Outer Shape

- the repo surface should scream business features or infrastructure
  capabilities, not technical drawers
- `src/`, `app/`, `modules/`, or `features/` are acceptable only when the next
  level still names real capabilities
- top-level `Controllers/`, `Services/`, `Repositories/`, or `Helpers/` as the
  primary repo story are not acceptable defaults
- public web, CLI, queue, and webhook entrypoints belong at stable edges such
  as `public/`, `bin/`, thin root commands, or framework-owned ingress files

## Clean Architecture Inside The Slice

- clean architecture is welcome inside a feature root when it clarifies
  dependency direction and ownership
- do not make the whole repo read as `Domain/Application/Infrastructure` by
  default if the product or package actually screams capabilities first
- large slices may use interior namespaces such as `domain/`, `application/`,
  `infrastructure/`, or `presentation/` under the owning feature root
- small slices should collapse layers instead of creating empty shells to look
  enterprise
- the dependency rule matters more than the layer label

## Reusable Package And Infra Component Shape

- reusable PHP components should separate the public surface from the runtime
  machinery behind it
- when a component has declaration and execution phases, keep
  registration/configuration distinct from runtime resolution and final
  execution
- use thin facades or interfaces for compatibility-sensitive APIs
- use kernels, orchestrators, or pipelines only where the flow genuinely needs
  them
- diagnostics, metrics, and cache artifacts are subordinate to the owning
  runtime flow, not second sources of truth

Example package shape:

```text
router/
  Router.php
  RouterInterface.php
  registration/
  resolution/
  execution/
  diagnostics/
```

## Application Slice Shape

- each feature should have one obvious composition root or flow owner
- transport, domain rules, persistence, and integrations may live inside the
  feature when the feature owns them
- feature-local bootstrap is acceptable when it keeps composition honest and
  prevents global wiring sprawl
- queue, command, and HTTP entrypoints should delegate into the same owned
  feature contracts instead of duplicating orchestration

Example application slice:

```text
billing/
  issue_invoice.php
  http/
  commands/
  domain/
  persistence/
  integrations/
  diagnostics/
```

## Public Surface And Lifecycle Rules

- declaration state such as routes, policy maps, or compiled definitions should
  become effectively immutable after bootstrap unless a documented extension
  model owns mutation
- caches, route manifests, and compiled containers are derived artifacts from
  canonical declarations
- exception mapping should happen at the ingress owner, not deep inside domain
  code
- backward-compatible public APIs should stay small, stable, and obvious even
  when internals evolve

## Anti-Patterns

- a global `app/Services` dump
- fat controllers, commands, or queue jobs acting as the real application layer
- global helpers or facades as hidden dependency injection
- whole-repo `Domain/Application/Infrastructure` layering that hides the actual
  feature story
- runtime mutation of declared route or policy state after boot without an
  explicit owner
