# TypeScript Governance Profile

Use this profile for TypeScript repositories.

This profile assumes strict TypeScript and should be used together with any
relevant runtime or framework profile.

Apply it together with the relevant architecture overlay under
`../../architecture/profiles/**` when repo shape and slice ownership matter.

## Compiler And Type-System Baseline

- keep compiler strictness high for first-party code and treat relaxed flags as
  explicit exceptions, not defaults
- treat `any`, double-casts, and broad `as` assertions as escape hatches that
  require a narrow, documented reason
- prefer `unknown` at untrusted boundaries and narrow it through parsing,
  validation, or explicit guards
- prefer `readonly`, `as const`, and narrow literal inference for static
  configuration, registries, and finite contracts
- keep nullability, optionality, and partial-update semantics consistent across
  the same public surface

## Domain Modeling Rules

- model important contracts with explicit DTOs, value types, discriminated
  unions, and named result shapes rather than anonymous bags of data
- prefer stable boundary types over convenience-driven structural sprawl
- keep function signatures narrow and intentional; if a function needs many
  optional flags, extract an explicit input contract
- represent state machines and workflow phases as typed states, not loosely
  related booleans
- prefer types that expose real invariants over "accept everything, validate
  later" shapes

## Module And Boundary Rules

- keep feature entry points explicit and expose the smallest honest public
  surface per slice
- when the architecture is modular, cross-slice access should go through owned
  public entrypoints rather than deep imports into internal files
- do not let path aliases or barrel files erase real dependency direction
- keep transport, storage, framework glue, and pure domain logic separable in
  both imports and runtime behavior
- avoid type-only abstractions that hide runtime complexity, ownership, or
  failure modes

## Runtime Validation And Schema Rules

- runtime validation is mandatory for external input; compile-time types alone
  do not make untrusted data safe
- keep schemas close to the owning boundary and run them before business logic
- prefer deriving TypeScript types from runtime schemas or vice versa, but keep
  the relationship explicit and testable
- reject or strip unknown fields intentionally; do not let accidental input
  drift silently become part of the contract
- error envelopes for validation failures should be stable and machine-readable

## Function And Side-Effect Rules

- make side effects explicit in naming, placement, and return behavior
- avoid hidden mutation in shared objects, ambient caches, and module-level
  mutable state
- prefer pure transformations for mapping, normalization, and domain decisions
- async functions must own timeout, cancellation, and retry expectations
  explicitly when I/O is involved
- do not fire-and-forget promises in production flows unless ownership,
  observability, and failure handling are explicit

## Error And Result Modeling

- prefer explicit error classes or result shapes with stable machine codes over
  opaque strings or boolean failure signals
- `catch` blocks must narrow unknown failures deliberately and preserve useful
  context
- do not swallow type errors, parse failures, or impossible states behind
  fallback defaults unless the fallback is part of the documented contract
- keep server-facing and client-facing error detail separate so diagnostics stay
  useful without leaking unsafe internals
- when a boundary publishes a result contract, success and failure shapes should
  be deterministic

## Build, Tooling, And Configuration Rules

- align `tsconfig` with actual runtime and delivery targets, not editor fashion
- module format, path resolution, and emitted output must match the deployed
  runtime contract
- do not silence compiler errors with `@ts-ignore` or equivalent without a
  short reason and a removal path
- generated types may reduce drift, but generated output must never become an
  excuse for boundary ambiguity
- keep build-time environment access explicit and typed; do not scatter blind
  `process.env` or runtime-global reads through the codebase

## Security And Trust-Boundary Rules

- never cast untrusted input into trusted domain types without validation
- keep secrets, private keys, and server-only configuration out of client
  bundles and browser-visible code paths
- avoid unsafe HTML, script, URL, and shell construction patterns; encode or
  sanitize by context
- permission, tenant, and actor claims must be revalidated at the owned
  authorization boundary, not trusted because a type says so
- logging and tracing must redact sensitive values before typed objects are
  serialized

## Testing And Delivery Rules

- type-check is mandatory, but type-check alone is not proof of behavioral
  correctness
- test boundary adapters, validators, discriminated unions, and failure-path
  mapping where type soundness matters to runtime safety
- add regression coverage for previously fixed typing bugs, schema drift, and
  unsafe narrowing behavior
- keep CI strict enough to catch broken contracts before merge
- review runtime-facing type changes as API changes, not harmless refactors
