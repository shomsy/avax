# Framework Migration Map

## Scope of this iteration

This iteration covers the canonical framework slice across phases `0` through `10`, with legacy cleanup started in phase `11`.

## Implemented phases

1. `Phase 0: Governance lock`
   - ADR set created under `docs/decisions/`
   - governance mirror created under `docs/governance/`
   - risk register and review artifacts updated

2. `Phase 1: framework/System skeleton`
   - `PublicSurface/`
   - `Flows/`
   - `Capabilities/`
   - `Configuration/`
   - `Foundation/`

3. `Phase 2: first tests`
   - unit tests for runtime, request scope, state reset, component registry, boot flow
   - feature tests for boot, HTTP, and console public surface

4. `Phase 3: request scope and state reset`
   - `RequestScope`, `RequestScopeStore`, `RequestScopeId`
   - `StateResetRegistry`, `StateResetReport`, `ResetApplicationState`

5. `Phase 4: runtime abstraction`
   - `Runtime`, `RuntimeContext`, `RuntimeState`, `RuntimeRequest`, `RuntimeResponse`, `RuntimeResult`
   - `PhpFpmRuntime`
   - `CliRuntime`
   - `WorkerRuntimeInterface`, `WorkerLoop`, `WorkerLifecycle`, `WorkerRequest`, `WorkerResponse`

6. `Phase 5: HTTP framework flow`
   - `HandleIncomingHttp`
   - `OpenHttpRequestScope`
   - `CloseHttpRequestScope`
   - `HttpKernel`
   - direct reuse of `components/HTTP/Response` through `Avax\HTTP\Response\ResponseFactory`
   - `ReadIncomingHttpRequest`, `MatchHttpRoute`, and `RunHttpRoute`
   - route-backed bridge via `ApplicationBuilder::withHttpRoutes(...)` and `withHttpRouteDefinitions(...)`
   - existing `Presentation/HTTP/routes/web.routes.php` now runs through the framework bridge

7. `Phase 6: console framework flow`
   - `RunConsoleCommand`
   - `ConsoleKernel`
   - `bin/avax`
   - legacy catalog reuse through `components/Commands/CommandDefinitions.php`

8. `Phase 7: component migration order`
   - execution order fixed below
   - blockers and reuse strategy captured

9. `Phase 8: runtime adapters`
   - first-party adapter shells for `FrankenPhp`, `RoadRunner`, `Swoole`, and `Workerman`
   - generic runtime contract coverage through `tests/Contract/Runtime`
   - runtime leak checker under `tooling/refactor/check-runtime-leaks.php`

10. `Phase 9/10: docs and quality gates`
    - docs validator under `tooling/docs/validate-docs.php`
    - docs mirror validator under `tooling/docs/validate-docs-mirror-source.php`
    - repo tooling paths retargeted from the removed legacy `Foundation/` root to the live framework/test/tooling slice
    - `composer validate`, targeted `phpunit`, `phpstan`, docs validation, leak check, and `php-cs-fixer` dry-run now pass on the migration slice

11. `Phase 11: obsolete structure removal`
    - removed legacy `components/Avax.php`

## Component migration order

1. `Container`
2. `Config`
3. `Events`
4. `Request`
5. `Response`
6. `Router`
7. `Middleware`
8. `Console`
9. `Cache`
10. `Session`
11. `Filesystem`
12. `Database`
13. `Validation`
14. `Auth`
15. `View`
16. `Logging`

## Existing-code reuse decisions

- Reused directly:
  - `components/Commands/CommandDefinitions.php` for legacy CLI catalog visibility
- Reused directly after stabilization:
  - `components/HTTP/Response` through `Avax\HTTP\Response\ResponseFactory`
- Reused through explicit compatibility bridge:
  - `components/compat.php` for the smallest safe request/router namespace aliases needed by the current framework and test slice
- Reused through explicit framework bridge:
  - request translation into the existing `ServerRequest` component shape
  - route registration and matching through existing router builders, definitions, and matcher
  - existing web routes file through a temporary facade container boundary
- Deferred because of structural blockers:
  - current bootstrap path referencing missing `AppFactory`
  - mixed `Avax\\` and `components\\` namespaces in several components

## Immediate next migration targets

1. stabilize a reusable container boot path from existing container code
2. migrate `Middleware` into the canonical framework HTTP flow so route middleware stops being deferred
3. stabilize container-backed controller resolution for framework HTTP so controllers with dependencies no longer depend on the later container migration
4. start converting the response component from "stabilized reusable legacy slice" into `components/Response/System/`
5. remove the remaining full-suite blockers in legacy DataHandling and container/http compatibility surfaces
6. replace or upgrade the Rector toolchain so `rector --dry-run` becomes a trustworthy gate again under PHP 8.5
