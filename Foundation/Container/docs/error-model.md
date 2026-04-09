# Error Model

Errors should read like a story of what broke, not like a collapsed stack trace.

## Required Shape

Relevant runtime and validation failures now aim to include:

- requested unit
- dependency path
- owning slice when ownership matters
- required scope when lifetime matters
- active composition conditions when conditional composition matters
- concrete failure
- likely fix

## Examples Of Story-Grade Failures

- top-level access to a flow-local service explains that the service is not part of the top-level surface and suggests
  `entry()` or an exported capability
- cross-slice access errors explain both the consumer slice and dependency slice
- scope failures explain which scope kind is required and suggest the exact `openScope()` call
- missing runtime input errors explain which input is missing and suggest overrides, `forContext()`, or a default value
- inactive conditional services explain the current environment/flags/tenant/region/mode plus the unmet condition

## Anti-Pattern Resistance

The container now resists several bad defaults:

- no silent top-level access to flow-local services
- no silent cross-slice access to `private` or `internal` units
- no scoped fallback into shared storage
- no disposable transient ownership lie
- no implicit override collisions without diagnostics

The goal is not to punish valid composition.
The goal is to make the wrong thing difficult and visible.
