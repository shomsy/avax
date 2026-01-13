# Http

## What This Folder Represents

Technical layer for HTTP runtime components built on the container: the application runner, kernel, and route registrar.

### For Humans: What This Means

This is the “web engine” glue—everything that turns container-resolved services into HTTP request/response handling.

## What Belongs Here

- HTTP application wrapper (`HttpApplication`)
- HTTP kernel/pipeline coordinator (`HttpKernel`)
- Route registrar/loader (`RouteRegistrar`)

### For Humans: What This Means

If it coordinates HTTP flow (requests, middleware, routes), it lives here.

## What Does NOT Belong Here

- Core container kernel/pipeline classes (those stay in `Core/Kernel`)
- Service providers (they belong in `Providers/`)

### For Humans: What This Means

Keep HTTP orchestration here; keep DI internals and provider definitions in their own homes.

## How Files Collaborate

- `HttpApplication` runs the lifecycle using container scopes.
- `HttpKernel` (if used) runs middleware then router.
- `RouteRegistrar` loads routes into the router used by the app/kernel.

### For Humans: What This Means

The registrar feeds the router; the kernel/pipeline wraps resolution; the application coordinates scope and run.
