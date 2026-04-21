# Express Architecture Profile

Use this overlay for Express applications and API services.

Apply it together with `../../architecture-standard.md`,
`../languages/javascript.md` or `../languages/typescript.md`, and
`../../../profiles/frameworks/express.md`.

## Core Law

- this profile narrows the universal vertical-slice law
- Express terms are local implementation lanes, not the whole repo story
- folder still says flow or capability
- file still says responsibility
- function still says exact action

## Core Stance

- Express owns ingress wiring, not the whole application architecture
- route registration, middleware policy, request validation, and business
  execution should remain distinct concerns
- the repo should still scream features or runtime capabilities first

## Recommended Shape

- keep `app.js`, `server.js`, or similar startup files minimal
- feature modules may own their own HTTP adapters under the feature root
- global middleware should be infrastructure-like and cross-cutting
- feature-specific request shaping belongs close to the feature handler it
  protects

Example feature:

```text
orders/
  http/
  actions/
  persistence/
  integrations/
  diagnostics/
```

## Route And Middleware Rules

- route files should declare paths and delegate to owned handlers or feature
  actions
- middleware should handle transport-level concerns, validation, auth, and
  narrow cross-cutting policy
- do not hide core business branching inside middleware stacks
- if a route layer defines schemas, params, or auth posture, keep them close to
  the route declaration
- error mapping should converge at one ingress boundary, not be improvised in
  every handler

## Anti-Patterns

- business logic spread across `routes/`, `middlewares/`, and `controllers/`
- one global `services/` folder as the real application layer
- route files that mutate app-wide registration state implicitly
- handlers that directly own transport, orchestration, persistence, and third-
  party calls at once
