# HTTP Refactor Plan

## Completed

- fixed stale Router imports and runtime boundary mismatches in root HTTP files
- normalized PSR-15 middleware implementations and introduced `RateLimiterInterface`
- repaired `HttpContext`, `ResponseFactory`, `JsonResponse`, `VerifyCsrfToken`, and `UriBuilder` edge cases
- flattened misplaced Session leaf actions while preserving root ownership folders
- restored Session BC support contracts required by the live public surface and existing tests
- removed obvious legacy artifacts from Request/Session/Middleware/URI component-local trees
- added root HTTP documentation and review artifacts

## Deferred

- `HttpClient/*` object-model cleanup
- Router-local benchmark/script/test PSR-4 cleanup
- full runtime PHPUnit verification once the tool is available
