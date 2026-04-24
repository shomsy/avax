# ARCHITECTURE NOTES

## Phase 0

- System Type: foundation HTTP runtime component
- Primary Consumers: router runtime, request assembly, middleware pipeline, session/security helpers
- Runtime Context: synchronous request/response lifecycle
- Lifecycle: active refactor cleanup with Router already normalized
- Public API Stability Requirement: moderate
- Backwards Compatibility: required for root HTTP and Session public surfaces, optional for dead internal legacy
  docs/tests

Primary axis:

> This system is fundamentally organized around **HTTP runtime ownership boundaries**.

Secondary axis:

> Secondary axis: **request/session capability slices where stateful behavior needs isolation**.

## Findings

### Finding: root HTTP runtime still imported removed Router internals

- Symptom: `AppKernel`, `HttpKernel`, and `RouterBootstrapper` referenced deleted Router classes and exception paths.
- Root Cause: Router refactor finished first, while outer HTTP integration layers stayed on the old API.
- Impact: outer HTTP orchestration could not safely load or compile against the live Router component.
- Evidence: runtime layers now depend on `RouterRuntimeInterface`, current Router exception classes, and DSL proxy
  registration.
- Risk Level: High

### Finding: middleware layer mixed PSR-15 contracts with property-style request access

- Symptom: middleware classes used `$request->method`, `$request->uri`, `$request->serverParams`, abstract middleware
  registrations, and non-PSR CORS/tracing signatures.
- Root Cause: partial migration toward PSR-15 stopped at the interface level and never finished the concrete middleware
  implementations.
- Impact: runtime behavior was inconsistent and several middleware classes were one request away from fatal errors.
- Evidence: middleware implementations now use PSR request APIs, local rate-limiter contracts, valid pipeline
  signatures, and safer default behaviors.
- Risk Level: High

### Finding: Session had two architectures at once

- Symptom: some Session files followed the new flow/capability tree, while many others lived in wrong paths, depended on
  missing `Core`/`Shared` classes, or had BC classes in the wrong folders.
- Root Cause: the hierarchy redesign started, but ownership entrypoints and BC shims were not finished.
- Impact: Composer PSR-4 discovery skipped live Session classes and the public surface was not trustworthy.
- Evidence: misplaced Session leaf classes were flattened, root owners were returned to correct ownership folders, BC
  support contracts were restored, and flow owners now depend on live store/recovery primitives.
- Risk Level: High

### Finding: component-local legacy docs and test trees described a dead system

- Symptom: `Request/tests`, `ToDo.md`, `middleware-documentation.md`, and several Session “enterprise/todo/changelog”
  markdown files remained in the component tree after the real architecture moved elsewhere.
- Root Cause: refactor planning artifacts accumulated faster than cleanup.
- Impact: reviewers could not tell which docs matched the real source tree and which ones were abandoned attempts.
- Evidence: dead docs/tests were removed and replaced with current `how-this-works.md`, review, and repo-doc mirror
  artifacts.
- Risk Level: Medium

## Decision

Keep and Improve. The live HTTP architecture is viable once the outer runtime and Session boundary drift are corrected.
The right move was targeted structural cleanup, not another rewrite.

## Decisions-Log

- 2026-04-23: aligned `AppKernel`, `HttpKernel`, and `RouterBootstrapper` with the current Router runtime/DSL split.
- 2026-04-23: normalized PSR-15 middleware behavior and introduced a local `RateLimiterInterface`.
- 2026-04-23: restored Session PSR-4 ownership shape and removed stale Request/Session legacy artifacts.
- 2026-04-23: added root HTTP refactor/review/how-this-works artifacts and repo docs mirror.

## Next Steps

- Run the HTTP-focused PHPUnit suites once PHPUnit is available.
- Decide separately whether `HttpClient/*` should stay on its current object model or be migrated to a stricter
  request/response ownership language.
- Clean Router-only benchmark/script/test PSR-4 drift in a dedicated Router follow-up instead of mixing it into outer
  HTTP work.
