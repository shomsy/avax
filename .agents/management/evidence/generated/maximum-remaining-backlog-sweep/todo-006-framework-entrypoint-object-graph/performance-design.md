# Performance Design

## Hot Path Classification

Affected path:

- `App::handle()` request path
- `RunApplication::handle()` route dispatch path

## Performance Decision

Move default dispatch pipeline assembly out of request-time lazy initialization and into configuration/application creation.

## Cost Impact

- request-time first-hit lazy construction: removed
- boot/configuration-time construction: unchanged or slightly clearer
- steady-state dispatch: unchanged

## Measurement Decision

No microbenchmark is required for Slice A because no performance speed claim is made. The proof is architectural: work that used to be reachable from request handling is now assembled before `App` receives requests.

## Classification

HOT_PATH_IMPROVED_BY_BOUNDARY.

Performance status remains YELLOW for the full TODO because broader object-graph findings remain.
