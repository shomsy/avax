---
name: avax-observability-failure-semantics
description: Ensures every meaningful change has clear failure behavior, debuggability, observability, and safe error semantics. Use for runtime, DI/container, boot/configuration, HTTP, security, filesystem, persistence, queue, worker, cache, external IO, failure boundaries, and exception/error changes.
---

# AvaX Observability and Failure Semantics

## Purpose

Every meaningful change must have clear failure behavior, debuggability, observability, and safe error semantics.

Code that fails silently, leaks state, or produces unsafe error messages is production-dangerous.

## Activation Triggers

Activate for tasks involving:

- runtime execution or lifecycle
- DI/container changes
- boot/configuration
- HTTP request/response handling
- security boundaries
- filesystem access
- persistence/database
- queue/message processing
- worker/server runtime
- cache operations
- external IO (HTTP calls, API calls, file reads)
- failure boundaries
- exception/error handling
- error message content
- logging behavior
- metric/tracing additions

## Failure Semantics Questions

The agent must answer:

- What can fail here?
- Where should it fail: boot, compile, configure, runtime, request, worker?
- What exception type should be thrown?
- Is the error message safe? (no secrets, no user data, no stack traces in production)
- Is sensitive data redacted in error output?
- Is a log event needed?
- Is a metric/tracing span needed?
- Is this retryable or fatal?
- Does this fail-open or fail-closed?
- What is the worker state after failure?
- Is this a user-facing or developer-facing message?
- How is this debuggable in production?

## Failure Classification

Classify each failure point:

- FAIL_CLOSED: safe default, operation stops
- FAIL_OPEN: operation continues (requires justification)
- RETRYABLE: transient failure, retry with limit
- FATAL: unrecoverable, must abort
- DEGRADED: partial functionality preserved

## Required Evidence

Every failure-prone task must include:

- `failure-semantics.md`
- `observability-review.md`

## Failure Semantics Gates

Before commit:

- [ ] failure points identified
- [ ] failure location classified (boot/runtime/request/worker)
- [ ] exception types appropriate
- [ ] error messages safe (no secrets, no sensitive data)
- [ ] sensitive data redacted
- [ ] log events defined where needed
- [ ] retry limits defined for retryable failures
- [ ] fail-closed behavior proven for security-sensitive paths
- [ ] worker state safe after failure
- [ ] debuggability path exists

## Error Message Safety

Error messages must not contain:

- secrets, tokens, passwords, API keys
- user PII
- raw SQL with user input
- filesystem paths revealing structure
- stack traces in production output
- internal class names that reveal architecture

Error messages must contain:

- operation context (what was attempted)
- safe failure reason (why it failed)
- actionable guidance (what to do next)
- correlation ID for debugging (if applicable)

## Observability Requirements

Log events must:

- be at correct level (debug/info/warning/error/critical)
- include safe context
- redact sensitive data
- be searchable and filterable
- not leak per-request state in long-lived workers

Metrics must:

- measure meaningful behavior
- have clear names
- have bounded cardinality
- not contain sensitive labels

Tracing must:

- span logical operations
- propagate correlation IDs
- redact sensitive attributes
- not create unbounded spans

## Long-Lived Worker Observability

For Swoole/RoadRunner/FrankenPHP/worker runtimes:

- log context must reset per request
- correlation IDs must be request-scoped
- metrics must not accumulate per-request state
- traces must be bounded per request
- error state must not bleed between requests

## Failure Mode Unclear Rule

Failure mode unclear for critical path = BLOCKER.

Critical paths include:

- authentication/authorization
- security boundaries
- data persistence
- financial transactions
- tenant isolation
- worker state reset

## Integration with Other Skills

This skill must be loaded together with:

- `avax-enterprise-remediation`
- `avax-enterprise-codecraft` for production-code changes
- `avax-runtime-performance-cache` for runtime failure behavior
- `avax-security-threat-model` for security failure modes
- `avax-test-evidence-quality` for failure behavior tests
- `review` skill

## Final Rule

No failure analysis, no critical path change.

No safe error messages, no commit.

No observability, no production-ready.
