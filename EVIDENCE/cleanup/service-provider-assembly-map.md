# ServiceProvider / Assembly Map

This document tracks the assembly integrity of core components in AvaX.
Every component must have a canonical assembly path (ServiceProvider or Configuration flow) that prevents inline `new`
construction of infrastructure.

| Component                 | ServiceProvider? | Assembly/Builder?          | Container Bindings? | PublicSurface? | Manual `new`?  | Status |
|---------------------------|------------------|----------------------------|---------------------|----------------|----------------|--------|
| Application/Container     | Yes              | Yes (RegisterDependencies) | Yes                 | Static Facade  | No             | GREEN  |
| DataStack/Database        | Yes              | Yes (DatabaseBuilder)      | Yes (Interface)     | Constructor DI | No             | GREEN  |
| HTTP/Router               | Yes              | Yes (RouterBuilder)        | Yes (Interface)     | Constructor DI | No             | GREEN  |
| HTTP/Dispatcher           | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Operations/Events         | Yes              | Yes                        | Yes                 | Static Facade  | No             | GREEN  |
| Application/Cache         | Yes              | Yes                        | Yes                 | Constructor DI | No (in facade) | GREEN  |
| Application/Filesystem    | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Operations/Logging        | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Operations/Observability  | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Security/Cryptography     | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Security/Redaction        | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Integration/ObjectStorage | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Operations/Queue          | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |
| Framework/FailureBoundary | Yes              | Yes                        | Yes                 | Constructor DI | No             | GREEN  |

## Audit Findings

### HTTP/Dispatcher

ServiceProvider: `HttpServiceProvider.php` (partially).
Assembly: `Dispatcher` is often assembled within `HttpRouterServiceProvider` or `Router`.

### Application/Cache

ServiceProvider: `CacheServiceProvider.php`.
Manual `new` in `AvaxCache` constructor (C-A violation).
Status: YELLOW (needs constructor DI fix).

### Operations/Events

ServiceProvider: `EventsServiceProvider.php`.
Static facade `Events` exists but delegates to `GlobalEventListenerState`.

### Framework/FailureBoundary

ServiceProvider: `FailureBoundaryServiceProvider.php`.
Assembly is clean.
