# RouteRegistrar

## Quick Summary

- Technical: Thin loader that resolves the underlying `HttpRequestRouter` (from `Router`) and requires the given route
  file so registrations run in-container.
- Always loads the route file; no cache markers or static collectors are used.

### For Humans: What This Means

It hands your routes file a `$router` that works and runs it—every boot gets the fresh route list.

## Terminology

- **HttpRequestRouter**: Internal router that actually registers and resolves route definitions.
- **Router facade**: Higher-level router that may wrap the HTTP router; we unwrap it when possible.
- **Route file**: Plain PHP file that expects `$router` and calls methods like `registerRoute`.

### For Humans: What This Means

There’s a low-level router doing the real work; the registrar makes sure your route file talks directly to it.

## Think of It

Like giving a chef the real stove instead of a display model—RouteRegistrar ensures the route file uses the actual
routing engine.

### For Humans: What This Means

Your recipe runs on the real kitchen, not a demo.

## Story Example

```php
$registrar = new RouteRegistrar($router);
$registrar->load(
    path: __DIR__.'/web.routes.php',
    cacheDir: __DIR__.'/../../storage/cache'
);
```

### For Humans: What This Means

You pass the router and file path; it includes the file with `$router` ready to use.

## For Dummies

1. Construct with a `Router` instance.
2. Call `load($path, $cacheDir)`.
3. Inside that file, use `$router` to register routes or a fallback.

### For Humans: What This Means

Give it the router and file; the registrar makes sure the file can talk to the router.

## How It Works (Technical)

- Stores the injected router.
- Requires the route file so it can call `$router->registerRoute()` and related helpers.

### For Humans: What This Means

It executes your route script in that context with the provided router.

## Architecture Role

- Lives in `Http/` as the bridge between the container-managed router and your route definitions.
- Called by `AppFactory::http()` after providers are registered.

### For Humans: What This Means

It’s the handoff point: container → router → route file.

## Methods

### Method: load(string $path, string $cacheDir): void

Technical: Unwraps to `HttpRequestRouter` when available and `require`s the route file with `$router` in scope.

### For Humans: What This Means

Run this after providers; it executes your route file against the real router.

#### Parameters

- `string $path` Absolute path to the routes file.
- `string $cacheDir` Currently unused placeholder for future cache handling.

#### Returns

- `void`

#### Throws

- Native PHP errors if the file is missing or contains errors.

#### When to use it

- During application boot right after providers are registered.

#### Common mistakes

- Forgetting to pass the router’s route file path; assuming static collectors still run.

## Risks & Trade-offs

Technical: No warm cache; route file runs every boot.

### For Humans: What This Means

You always reload routes; ensure the file is quick and correct.

## Related Files & Folders

Technical:

- `Core/AppFactory.php` — calls `load()`.
- `Http/HttpApplication.php` — consumes the router after loading.
- `Providers/HTTP/RouterServiceProvider.php` — wires the router stack.

### For Humans: What This Means

Provider installs the router, AppFactory calls the registrar, HttpApplication uses the router to serve requests.
