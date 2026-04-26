# PHP Governance Profile

Use this profile for PHP applications and services.

This profile assumes modern PHP 8.x with Composer-based delivery and should be
used together with any relevant framework profile.

Apply it together with the relevant architecture overlay under
`../../architecture/profiles/**` when repo shape and slice ownership matter.

## Language Baseline

- use `declare(strict_types=1)` on all first-party PHP source files unless the
  file is generated or the project documents a different rule
- keep one repo-wide nullability style and do not mix styles randomly across
  the same public surface
- prefer explicit parameter, property, and return types over mixed or implicit
  behavior
- prefer `final` for concrete classes unless extension is an intentional part
  of the contract
- prefer `readonly` dependencies, DTOs, and value carriers when mutation is not
  required

## Modern PHP Design Rules

- prefer constructor promotion for immutable dependencies and configuration
- prefer DTOs, value objects, and enums over stringly-typed arrays for
  important boundaries
- prefer attributes over magic metadata arrays or ad hoc string conventions
  when runtime metadata matters
- use fluent APIs and DSL naming only when they improve readability and stay
  strongly typed, deterministic, and immutable by default
- avoid dynamic properties, ambient mutation, and unbounded magic in domain
  logic

## Architecture And Boundary Rules

- prefer clear feature or domain modules over global helper sprawl
- keep controllers, commands, handlers, and public facades thin
- keep orchestration in kernels, pipelines, actions, or clearly owned services
- keep framework glue, adapters, and transport concerns separate from core
  domain logic
- for infrastructure-heavy subsystems, prefer one thin facade, one explicit
  orchestrator, and modular single-purpose steps
- keep route registration, runtime resolution, middleware execution, and final
  dispatch as distinct responsibilities when the subsystem owns HTTP routing
- keep route or command definitions immutable after bootstrap unless a
  documented runtime extension model explicitly owns mutation
- keep the public API surface stable and narrow; internals may change, but
  compatibility boundaries must stay obvious

## Entry, Bootstrap, And Runtime Ownership

- root HTTP, CLI, queue, or webhook entry files must stay thin and delegate
  into owned feature or runtime modules
- shared bootstrap may wire infrastructure, but feature-specific bootstrap must
  own feature-local defaults, policies, and composition when that keeps
  boundaries clearer
- expose the smallest honest public surface: one request handler, one command
  runner, one sync entry, or another explicit ingress root as appropriate
- treat generated caches, manifests, route tables, and compiled containers as
  derived artifacts that must be rebuildable from canonical source
- if the runtime exposes only one public endpoint, expose only that endpoint
  and keep unrelated paths dark by default

## PSR And Interoperability Rules

- implement relevant PSR contracts at public boundaries when the subsystem is
  infrastructure-like or intended for reuse
- PSR-3 is the default logging contract when logging is public
- PSR-7 and PSR-15 should govern HTTP message and middleware boundaries when
  applicable
- PSR-11 should govern public container boundaries when a container is exposed
- local ergonomics may wrap PSR contracts, but must not silently break them

## Runtime Safety And Configuration

- bootstrap must fail closed when mandatory configuration is missing
- environment detection and mode switching must be explicit, testable, and not
  silently guessed in production
- compiled caches, generated configuration, and similar artifacts should be
  invalidated when their source inputs change materially
- writes to compiled artifacts or caches should use atomic temp-file plus rename
  patterns where possible
- avoid hidden global state; if global registration exists, constrain it to
  explicit bootstrap or policy layers

## Persistence And Transaction Rules

- validate and sanitize all request or command input before persistence
- use parameterized queries and safe ORM or query-builder patterns by default
- treat raw SQL as an explicit escape hatch with guard rails; never pass
  untrusted user input directly into raw expressions
- keep transaction boundaries explicit and rollback behavior deterministic
- nested transactions, unit-of-work, or deferred execution patterns require
  clear ownership and regression coverage
- migrations and schema changes should be reviewable, ordered, and reversible
  where practical

## Security And Data Handling

- escape output by context to reduce injection risk
- avoid dynamic include, `eval`, shell interpolation, and unsafe reflection
  shortcuts
- redact sensitive bindings, secrets, and PII before logs, traces, or exposed
  exceptions
- use `#[SensitiveParameter]` or an equivalent mechanism when secrets or raw
  bindings might otherwise leak into traces
- treat correlation identifiers and audit trails as security and recovery
  surfaces, not optional nice-to-haves

## Observability And Diagnostics

- instrument important lifecycle events with timestamps and correlation ids
- provide machine-readable validation or health output for infra-heavy packages
  when the component has meaningful runtime policy or diagnostics
- prefer explicit error objects or exceptions with actionable context over vague
  boolean failures
- keep diagnostics safe by default; raw or unredacted debug output should
  require explicit opt-in
- distinguish path-not-found, method-not-allowed, validation, auth, and
  internal failures explicitly at public boundaries when the runtime surface can
  express that difference
- keep the ingress exception boundary centralized; unexpected failures should
  map to a stable error contract instead of leaking raw stack behavior

## Testing And Delivery Rules

- cover critical PHP behavior with unit tests and integration tests
- add regression tests for transaction, cache, policy, and security edge cases
- use Composer scripts or another explicit entrypoint for validation and test
  flows
- use static analysis for serious codebases (`PHPStan`, `Psalm`, or equivalent)
- use mutation testing when correctness or security logic is central to the
  package
- record production-impacting config, migration, and runtime changes in release
  evidence

## PHPDoc And Commenting Rules

- remove noise comments above `namespace`, `use`, or trivial statements
- document public classes and methods with intent, not boilerplate
- add `@throws` tags for meaningful public failure modes
- explain jargon when a smart developer would otherwise need tribal knowledge to
  understand the method or class
- use imports consistently in code and docblocks; avoid unnecessary
  fully-qualified names
