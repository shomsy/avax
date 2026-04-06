# Node.js Governance Profile

Use this profile for Node.js services and tooling backends.

This profile assumes actively supported Node.js LTS, lockfile-backed delivery,
and production services or operational tooling with explicit runtime ownership.

Apply it together with the relevant architecture overlay under
`../../architecture/profiles/**` when repo shape and slice ownership matter.

## Runtime Baseline

- use an actively supported Node.js LTS release and keep runtime upgrades tied
  to the repo support policy
- lockfile-backed installs are mandatory; wildcard versioning and mutable
  dependency behavior are not acceptable production defaults
- keep module format and runtime target explicit; do not mix ESM, CJS, and
  transpiled assumptions casually
- startup must be deterministic, observable, and fail closed when mandatory
  configuration is missing
- avoid blocking CPU or filesystem work on hot request paths; move heavy work to
  worker lanes, queues, or explicit background steps

## Architecture And Boundary Rules

- keep transport surfaces thin: routing, parsing, validation, auth guards,
  response shaping, and nothing more
- keep business orchestration in owned actions, services, or use-case modules
- keep persistence and external integrations behind explicit adapters or storage
  modules
- keep app workers disposable: request-local state may live in memory, but
  durable business truth and session continuity belong in external owned stores
- avoid hidden shared global state beyond read-only configuration and deliberate
  singleton infrastructure clients
- module boundaries should stay legible under growth; Node folders must not
  become a dumping ground for cross-cutting convenience code

## Configuration And Secret Rules

- validate runtime configuration at startup with a schema and stop boot on
  missing or malformed required values
- secrets must never live in source, fixtures, logs, or shipped build artifacts
- separate environment concerns through config, not production-only branching
  magic in arbitrary modules
- immutable artifact thinking is preferred: the same build should move across
  environments with config changes, not code changes
- reads of environment state should be centralized or wrapped so runtime drift
  stays diagnosable

## HTTP And Service Contract Rules

- validate body, query, params, headers, cookies, and webhook payloads before
  business logic
- keep external response contracts stable: status codes, error envelope, DTO
  shape, and pagination semantics must be explicit
- separate authentication from authorization and enforce authorization at the
  resource boundary
- use explicit request limits: body size, concurrency, server timeout, and
  upstream timeout
- idempotency keys should protect critical create or payment-like operations
  where replay would be dangerous

## Async, Concurrency, And Shutdown Rules

- every outbound call needs an explicit timeout and owned retry policy; retries
  are valid only when the operation is safe to repeat
- use `AbortController` or an equivalent contract when cancellation matters
- promise fan-out must be bounded and observable; avoid unbounded parallel work
  under user-controlled input
- if work moves to queues or background workers, define producer ownership,
  consumer ownership, retry budget, and dead-letter or quarantine behavior
  explicitly
- graceful shutdown is mandatory for long-running services: stop taking new
  work, drain active work, and close owned resources deterministically
- background jobs, queues, and schedulers must define ownership, deduping, and
  failure recovery explicitly

## Persistence And Data Rules

- use connection pooling and never connect per request
- use parameterized queries or safe ORM/query-builder paths; raw string
  concatenation for database commands is prohibited
- keep migration discipline explicit, ordered, and reviewable
- multi-tenant systems must enforce tenant isolation in data access, not only
  in route naming or UI assumptions
- cache behavior must define TTL, invalidation, and stampede protection rather
  than acting as an implicit correctness layer

## Security And Abuse-Resistance Rules

- apply safe HTTP defaults such as explicit header policy, restrictive CORS, and
  rate limiting where abuse cost exists
- do not trust JWT claims, session state, or caller-supplied identities without
  server-side verification of scope and permission
- never execute shell commands, dynamic imports, or template rendering paths
  from untrusted input without a tightly owned policy boundary
- keep SSRF, upload, and webhook boundaries explicit with allowlists, size
  limits, signature verification, and replay protection where relevant
- redact secrets, tokens, and sensitive personal data from logs, traces, and
  serialized error objects

## Delivery And Runtime Hardening Rules

- if containerized, prefer non-root runtime identities, minimal writable paths,
  and explicit read-only root filesystems where feasible
- drop unneeded runtime capabilities and block privilege escalation paths by
  default when the platform supports it
- build and release flows should make the hardened runtime the normal path, not
  an optional later hardening pass
- do not rely on mutable in-place container state for correctness; rebuild and
  replace disposable runtimes instead

## Observability And Diagnostics

- structured logs are required for request, worker, and job flows
- request or correlation ids must propagate through critical paths
- error handling should map failures deterministically and preserve actionable
  diagnostic context without leaking raw internals to clients
- expose explicit liveness and readiness semantics for services with runtime
  dependencies
- add metrics and tracing for critical services when latency, saturation, or
  dependency health matter to operations

## Testing And Delivery Rules

- cover core use-case logic with unit tests and critical routes with integration
  tests
- add regression coverage for auth, permission, tenant, migration, and timeout
  behavior when those concerns exist
- CI should run lint, type checks when applicable, tests, and dependency or
  supply-chain checks appropriate to the service risk
- production readiness requires evidence for health behavior, protected-route
  behavior, and failure-path handling, not only happy-path tests
- dependency audits, SBOM generation, and runtime hardening checks should be
  standard for serious services, not afterthoughts
