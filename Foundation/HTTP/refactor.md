# HTTP Refactor

## Status

- Router remains the already-finished how-to compliant slice and was left as-is.
- Root HTTP runtime, middleware, context, response, security, and URI surfaces are now aligned with the current Router
  and Session boundaries.
- Session was flattened only where the old hierarchy was objectively broken: misplaced leaf actions were normalized,
  compatibility buckets were restored only for BC (`Audit`, `Events`, `Recovery`), and dead archive artifacts were
  removed.
- Legacy Request-in-component test trees and stale Session planning/doc files were removed in favor of repo-level tests
  and current ownership docs.
- New HTTP review and repo-doc mirror artifacts were added under `Foundation/HTTP/Code-Review-And-ToDo` and
  `docs/Foundation/HTTP`.

## Goals

- keep public HTTP entrypoints small and coherent
- keep ownership folders honest to the live runtime behavior
- remove parallel legacy worlds that describe a different architecture than the code
- keep Session BC where it matters without reviving `Core` and `Shared` manager-heavy internals

## Notes

- `HttpClient/*` still uses its own request/response object model and was not force-migrated into PSR-7 semantics in
  this pass.
- Router-specific PSR-4 issues in `benchmarks/`, `scripts/`, and component-local Router tests remain isolated to the
  already-refactored Router subtree.
- PHPUnit execution still depends on the tool being available in the workspace runtime.
