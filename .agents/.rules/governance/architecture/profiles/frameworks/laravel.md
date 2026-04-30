# Laravel Architecture Profile

Use this overlay for Laravel applications.

Apply it together with `../../architecture-standard.md`,
`../languages/php.md`, and `../../../profiles/frameworks/laravel.md`.

## Core Law

- this profile narrows the universal vertical-slice law
- Laravel terms are local implementation lanes, not the whole repo story
- folder still says flow or capability
- file still says responsibility
- function still says exact action

## Core Stance

- preserve Laravel ergonomics, but make the codebase scream features before
  framework layers whenever the app is large enough to need that clarity
- framework-owned ingress points such as routes, controllers, jobs, console
  commands, listeners, and policies stay thin
- clean architecture, if used, should live inside the feature root rather than
  forcing the whole repo into `Domain/Application/Infrastructure` theater

## Recommended Feature Shape

- prefer feature modules such as `app/Features/Billing/` or an equivalent
  namespace when the application is non-trivial
- keep route files, controllers, requests, policies, actions, resources, jobs,
  and persistence code grouped under the owning feature when that reduces
  cross-feature jumping
- shared app-wide infrastructure should remain boring and explicit under
  framework-appropriate homes such as `config/`, `bootstrap/`, or narrow
  support foundations
- `app/Models/` may remain as a compatibility shell when the project needs it,
  but real feature behavior should not dissolve into god models

Example feature:

```text
app/Features/Billing/
  Http/
  Actions/
  Domain/
  Persistence/
  Jobs/
  Resources/
  BillingServiceProvider.php
```

## HTTP, Queue, And Console Boundaries

- routes declare transport and delegate immediately
- controllers shape request and response but do not become orchestration dumps
- Form Requests, policies, and resources belong near the feature they protect
- jobs, listeners, and scheduled tasks should call owned feature actions or
  use-case modules instead of re-implementing the flow
- webhook and queue ingestion should reuse the same feature contracts as normal
  HTTP flows where possible

## Data And Model Boundaries

- Eloquent models may remain persistence-facing records, but domain decisions
  should not be smeared across model events, casts, observers, and accessors
- keep query, mutation, and integration logic close to the feature that owns
  the data contract
- global scopes, observers, and model magic are architectural dependencies and
  should stay rare and reviewable
- migrations and factories are supporting lanes, not the primary architecture

## Anti-Patterns

- a repo whose primary story is `app/Http`, `app/Models`, `app/Services`,
  `app/Repositories`
- route closures or middleware owning real business branching
- facades hiding hard dependencies in domain-critical paths
- Eloquent models acting as transport layer, domain layer, and persistence layer
  at the same time
