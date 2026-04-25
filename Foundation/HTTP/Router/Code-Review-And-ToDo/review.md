# Router Refactor Review

## Scope

Reviewed and completed the Router component refactor described in `Foundation/HTTP/Router/refaktor.md`.
The reviewed surface is the HTTP Router source under `Foundation/HTTP/Router`, with public contracts preserved at:

- `Router.php`
- `RouterInterface.php`
- `RouterRuntimeInterface.php`
- `HttpMethod.php`
- `functions.php`

## Final Architecture

The component now follows one explicit execution model:

`Router::resolve()` -> `RouterKernel::handle()` -> `ApplyHeadRequestFallback` -> `HttpRequestRouter::resolve()` ->
`RoutePipelineFactory` -> `RoutePipeline` -> `ControllerDispatcher`

Route registration is isolated in `System/Flows/RegisterRoutes`.
Route boot and cache loading are isolated in `System/Flows/BootstrapRoutes`.
Request matching is isolated in `System/Flows/ResolveRequest`.
Route execution is isolated in `System/Flows/RunRoute`.
Shared domain objects live in `System/Capabilities`, configuration in `System/Configuration`, and framework-level
failures in `System/Foundation`.

## Findings Closed

| Finding                                                        | Evidence                                                                                                                                                     | Resolution                                                                                    |
|----------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------|
| Old namespace tree leaked into Composer autoload               | `Routing`, `Support`, `Bootstrap`, `System`, `Kernel`, `Validation`, `Tracing`, `Matching`, `Metrics`, `Snapshots`, `Exceptions` all existed beside `System` | Deleted the stale folders after the new `System` tree loaded successfully                     |
| Register DSL and runtime router were mixed                     | Root `RouterDsl.php` and old `Routing/*` carried registration concerns outside the flow folder                                                               | Moved DSL and builders under `System/Flows/RegisterRoutes`                                    |
| 404/405 flow was converted into a synthetic failure route      | `HttpRequestRouter::resolve()` caught route failures and returned `RouteResolutionContext::failure()`                                                        | Restored explicit `RouteNotFoundException` and `MethodNotAllowedException` throwing           |
| HEAD fallback used stale class naming                          | Old `HeadRequestFallback` naming survived in docs/source                                                                                                     | Replaced with `ApplyHeadRequestFallback` and wired it to the new exception contract           |
| Route path regex emitted double slashes for parameter segments | `/users/{id}` compiled as a pattern with an extra `/`                                                                                                        | Rewrote route pattern compilation to build segment-by-segment                                 |
| Global route helper functions had no valid scoped collector    | `functions.php` called `RouteCollector::current()` while the collector had no current scope                                                                  | Implemented a scoped route-file bridge and wired `RouteFileRegistrar` into it                 |
| `route_constraint()` was defined twice                         | First definition accepted `string`, second accepted `array`; the second could never load                                                                     | Kept the array helper and retained raw regex validation through `route_validate_constraint()` |
| PHP property hooks recursed into themselves                    | Several moved classes had getters returning the same property                                                                                                | Replaced recursive hooks with normal backing properties                                       |
| Moved files had missing imports                                | Several classes referenced `RouteDefinition`, `RouteKey`, `RouteCollection`, `ContainerInterface`, or `ServerRequest` through old namespaces                 | Added explicit imports under the new flow/capability namespaces                               |

## Validation

- `php -l` passed for every PHP file under `Foundation/HTTP/Router/System`.
- `php -l` passed for root Router PHP files.
- `php -l` passed for Router tests under `tests/Foundation/HTTP/Router` and `Foundation/HTTP/Router/tests`.
- `composer dump-autoload -o` no longer reports PSR-4 warnings for Router source files. Remaining Router warnings are
  limited to existing test, benchmark, and script files that are not PSR-4 source classes.
- Autoload smoke test loaded every class, interface, and enum under `Foundation/HTTP/Router/System`.
- Route pattern smoke test confirms `/users/{id}` compiles to `#^/users/(?<id>[^/]+)$#u`.

## Residual Risk

`vendor/bin/phpunit` is not present in the current vendor install, so PHPUnit execution could not be completed in this
workspace.
The route tests are syntactically valid, but full behavioral regression still requires the project test runner to be
installed.
