# Policy Engine

The policy engine is the structural warning and error layer over the authored graph.

## Surfaces

Use:

- `debugGraph()['policyFindings']`
- `validate()`

`debugGraph()` returns machine-readable findings.
`validate()` folds those findings into human-readable review output.

## Current Policy Codes

- `POL-001`: constructor arity suggests over-injection
- `POL-002`: shared visibility with one or zero known consumers suggests premature extraction
- `POL-003`: foundation unit depends on too many services and may be overgrown
- `POL-004`: one flow depends directly on another flow
- `POL-005`: concept naming is too generic for honest ownership diagnostics
- `POL-006`: flow unit uses shared or public visibility without entry intent
- `POL-007`: pooled lifetime contract violation (missing class-backed build or `ResettableInterface`)
- `POL-008`: service depends on container runtime internals (service locator drift)
- `POL-009`: service depends on raw settings/config globals outside configuration ownership

## Severity Model

- `warn`: review and justify before the shape calcifies
- `error`: treat as an architectural violation unless there is an explicit exception

Current severity assignments:

| Code    | Severity |
|---------|----------|
| POL-001 | `warn`   |
| POL-002 | `warn`   |
| POL-003 | `warn`   |
| POL-004 | `error`  |
| POL-005 | `warn`   |
| POL-006 | `warn`   |
| POL-007 | `error`  |
| POL-008 | `error`  |
| POL-009 | `warn`   |

## Structural Diff

`debugGraph()` also exposes `structureDiff`, which compares:

- current dependency graph versus compiled graph
- current ownership metadata versus compiled ownership metadata
- current slice manifests versus compiled slice manifests

This makes structural drift visible after compilation and before release claims.

## Policy Intent

The policy engine is not decoration.
It exists to keep the container hostile to architecture drift:

- no flow-to-flow dependency creep
- no generic naming camouflage
- no silent shared extraction
- no overgrown foundation lane
- no hidden structural changes between authored truth and compiled artifacts
