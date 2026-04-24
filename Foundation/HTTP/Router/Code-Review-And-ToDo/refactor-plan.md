# Router Refactor Plan

## Completed

- Rebuilt Router around flow folders: `RegisterRoutes`, `BootstrapRoutes`, `ResolveRequest`, and `RunRoute`.
- Moved route definitions, route keys, route collections, trace, and metrics into `System/Capabilities`.
- Moved router config into `System/Configuration`.
- Moved exceptions and reflection cache into `System/Foundation`.
- Preserved public runtime and registration contracts at the component root.
- Deleted stale pre-refactor folders after the new `System` tree passed autoload smoke checks.
- Updated route helper behavior for scoped file registration, route constraints, and route pattern compilation.
- Updated tests and internal references away from old `Routing`, `Support`, `Bootstrap`, and related namespaces.

## Verification Plan

- Syntax check all Router source files.
- Syntax check Router tests.
- Run optimized Composer autoload and inspect Router-specific PSR-4 output.
- Smoke-load every class under `Foundation/HTTP/Router/System`.
- Run PHPUnit Router suite when `vendor/bin/phpunit` is available.

## Deferred

- PHPUnit behavioral execution is deferred only because the local vendor directory does not contain
  `vendor/bin/phpunit`.
- Existing non-source PSR-4 warnings for `Foundation/HTTP/Router/tests`, `benchmarks`, and `scripts` are left as-is
  because they are outside the production Router source tree.
