# TODO-006 Slice C Low-Level Design

## App Constructor

Add constructor collaborators:

- `FrameworkRouteRegistrar $routeRegistrar`
- `OpenHttpRequestScope $openRequestScope`
- `CloseHttpRequestScope $closeRequestScope`
- `CreateRuntimeRequestFromHttpRequest $createRuntimeRequest`

## Route Registration

`App::{get,post,put,patch,delete}` delegates to `FrameworkRouteRegistrar`.

`App::any()` delegates to `FrameworkRouteRegistrar::anyExpanded()`.

`App::handle()` passes `$routeRegistrar->collectRoutes()` to `RunApplication`.

## Runtime Request Conversion

New class:

`framework/System/Flows/HandleIncomingHttp/CreateRuntimeRequestFromHttpRequest.php`

It converts the canonical HTTP request object from `CreateRequestFromGlobals` into `RuntimeRequest`.

## Assembly Points

Update:

- `CreateApplication::make()`
- `CreateApplication::fromBuilder()`
- `BootDslEngine::createRuntimeAndApp()`

Each path already owns runtime/app assembly and can create:

- `FrameworkRouteRegistrar`
- `OpenHttpRequestScope`
- `CloseHttpRequestScope`
- `CreateRuntimeRequestFromHttpRequest`

## Failure Semantics

- request scope close remains in `finally`
- custom exception handler behavior remains unchanged
- no catch-and-ignore behavior is added

## Performance

Per-request construction is reduced for scope helpers.

No benchmark claim is made.
