# HttpKernel

## Quick Summary

- Middleware-aware kernel that delegates HTTP requests to the router after running a pipeline.
- Provides a single `handle(Request): Response` entry for wrapping routing with middleware.

### For Humans: What This Means

It’s the “traffic cop” that runs middleware and then asks the router for the final response.

## Terminology

- **MiddlewarePipeline**: Chain that processes the request before router resolution.
- **Router**: Component resolving Request→Response.

### For Humans: What This Means

Middleware can inspect/modify the request before the router handles it.

## Think of It

Like airport security: you pass through checkpoints (middleware) before reaching the gate (router).

### For Humans: What This Means

Middleware layers add checks/features before the route logic runs.

## Story Example

Injected into a controller or app:

```php
$response = $kernel->handle($request);
```

### For Humans: What This Means

One call runs middleware then routing.

## For Dummies

1) Receive Request
2) Pass through middleware pipeline
3) Router resolves final Response

### For Humans: What This Means

Middleware wraps the router call.

## How It Works (Technical)

- `handle()` calls `$pipeline->handle($request, fn($req) => $router->resolve($req))`.
- Returns the router’s response after middleware completes.

### For Humans: What This Means

Middleware gets first pass; router is the fallback.

## Architecture Role

- Lives in `Http/` as an optional coordinator.
- Depends on `MiddlewarePipeline` and `Router`.
- Can be used by higher-level runners if you want explicit middleware chaining.

### For Humans: What This Means

Use it when you need middleware-aware request handling instead of calling the router directly.

## Methods

- `handle(Request $request): ResponseInterface`
    - Runs pipeline then router.
    - Common mistakes: pipeline not configured; router missing bindings.

## Risks & Trade-offs

- If pipeline is empty, it’s a thin wrapper; if misconfigured, can block routing.

### For Humans: What This Means

Ensure middleware registration matches your routes; otherwise you might short-circuit requests.

## Related Files & Folders

- `Http/HttpApplication.php`: Can call the kernel instead of router directly.
- `Http/RouteRegistrar.php`: Ensures routes exist for the router to resolve.
- `Core/AppFactory.php`: Builds the container that supplies these dependencies.

### For Humans: What This Means

Kernel is part of the HTTP trio with application runner and registrar.
