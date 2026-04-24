# Router Review Evidence

## Evidence Table

| ID         | Check                                                    | Result                                                                                                         |
|------------|----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------|
| ROUTER-001 | Production Router source uses the new `System` tree only | Passed: old source folders were removed                                                                        |
| ROUTER-002 | Public contracts still load                              | Passed: `Router`, `RouterInterface`, and `RouterRuntimeInterface` autoload                                     |
| ROUTER-003 | Flow classes autoload                                    | Passed: `RouterDsl`, `BootstrapRoutes`, `HttpRequestRouter`, and `RouterKernel` autoload                       |
| ROUTER-004 | Core domain classes autoload                             | Passed: `RouteDefinition`, `RouteKey`, `RouteCollection`, `RouterTrace`, and `RouterMetricsCollector` autoload |
| ROUTER-005 | Syntax validation                                        | Passed for Router source and Router tests                                                                      |
| ROUTER-006 | Composer optimized autoload                              | Passed for Router source; remaining Router warnings are test/benchmark/script files only                       |
| ROUTER-007 | Route pattern compilation                                | Passed: `/users/{id}` compiles without duplicate slashes                                                       |
| ROUTER-008 | 404/405 contract                                         | Passed by inspection: `HttpRequestRouter::resolve()` throws explicit route exceptions                          |

## Commands Used

```bash
find Foundation/HTTP/Router/System -type f -name '*.php' -print0 | xargs -0 -n1 php -l
find tests/Foundation/HTTP/Router Foundation/HTTP/Router/tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
composer dump-autoload -o
```

## Test Runner Status

`vendor/bin/phpunit` is not available in this workspace, so PHPUnit execution is blocked until dev dependencies are
installed.
