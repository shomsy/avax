# AppFactory

## Quick Summary

- Provides deterministic entrypoints for building HTTP or CLI containers without legacy builders.
- Delegates low-level wiring to `ContainerBuilder`, then registers providers, loads routes, and returns
  `HttpApplication` (HTTP) or `Container` (CLI).
- Enforces explicit provider lists to keep boot predictable.

### For Humans: What This Means

One call sets up your app. You pick providers and routes; the factory builds the container and hands you a ready-to-run
application.

## Terminology

- **Deterministic Providers**: An ordered, explicit list of service providers—no auto-scan surprises.
- **ContainerBuilder**: The low-level assembler that wires kernel, scopes, metrics, and settings.
- **HttpApplication**: Thin runtime wrapper that opens a scope, resolves a request, and sends the response.
- **RouteRegistrar**: Loader that feeds routes into the router using a plain PHP file and the injected `$router`.

### For Humans: What This Means

You decide what loads and in what order; the factory handles the assembly and hands you a clean runtime object.

## Think of It

Like a flight director: ContainerBuilder spins up the engines, providers are the crew boarding, RouteRegistrar is the
flight plan, and HttpApplication is takeoff.

### For Humans: What This Means

You run one checklist and the plane is airborne—no hidden steps or surprise detours.

## Story Example

```php
use Avax\Container\Core\AppFactory;
use Avax\Container\Providers\HTTP\RouterServiceProvider;
use Avax\Container\Providers\Core\ConfigurationServiceProvider;

$app = AppFactory::http(
    providers: [
        ConfigurationServiceProvider::class,
        RouterServiceProvider::class,
    ],
    routes: __DIR__.'/../Presentation/HTTP/routes/web.routes.php',
    cacheDir: __DIR__.'/../storage/cache',
    debug: true
);

$app->run();
```

### For Humans: What This Means

The factory builds the container, installs providers, loads routes, and hands you something you can immediately run.

## For Dummies

1. Call `AppFactory::http()` with providers, a route file, a cache directory, and a debug flag.
2. The factory builds the container via `ContainerBuilder`.
3. Providers are registered and booted in order.
4. Routes are loaded into the router.
5. You get `HttpApplication`; call `run()`.

### For Humans: What This Means

Give it a list and a path; it wires everything and returns an app you can execute.

## How It Works (Technical)

- Uses `ContainerBuilder` to assemble the kernel, scopes, metrics, and default settings.
- Registers providers deterministically, then boots them so bindings exist before route loading.
- Resolves the router from the container and loads the route file through `RouteRegistrar`.
- For CLI, skips routing and simply returns the container after providers run.

### For Humans: What This Means

All the low-level wiring is centralized in ContainerBuilder; AppFactory just orchestrates providers and routes on top.

## Architecture Role

- Lives in `Core/` as the high-level entrypoint for HTTP and CLI runtimes.
- Depends on builder, provider contracts, router, and HttpApplication.
- Consumed by front controllers (`public/index.php`) or CLI scripts for deterministic boot.

### For Humans: What This Means

This is the “start here” surface: it delegates wiring, then hands you a ready runtime.

## Methods

### Method: http(array $providers, string $routes, string $cacheDir, bool $debug = false): HttpApplication

Technical: Builds the container, registers and boots providers, loads routes, returns `HttpApplication`.

### For Humans: What This Means

Use this for web requests: it prepares everything and gives you an app you can `run()`.

#### Parameters

- `array $providers` Ordered list of providers (class names or instances).
- `string $routes` Absolute path to the routes file that expects `$router`.
- `string $cacheDir` Directory for caches used by underlying components.
- `bool $debug` Enables strict mode and disables auto-define when true.

#### Returns

- `HttpApplication` ready to process HTTP requests.

#### Throws

- `InvalidArgumentException` if a provider doesn’t implement `ServiceProviderInterface`.
- Container resolution errors if required providers are missing.

#### When to use it

- Any HTTP front controller or test harness that needs a ready web runtime.

#### Common mistakes

- Omitting required providers (e.g., router) or pointing to a wrong route file.

### Method: cli(array $providers, string $cacheDir, bool $debug = false): Container

Technical: Builds the container via `ContainerBuilder`, registers and boots providers, returns the container for
non-HTTP workloads.

### For Humans: What This Means

Use this when you need the container for console jobs or workers without routing.

#### Parameters

- Same provider list semantics; no routes needed.
- `string $cacheDir` Directory for caches.
- `bool $debug` Controls strict/auto-define flags.

#### Returns

- `Container` wired and provider-booted, ready for CLI code.

#### Throws

- `InvalidArgumentException` for invalid providers.

#### When to use it

- CLI commands, queue workers, background jobs, or tests that don’t need HTTP.

#### Common mistakes

- Expecting routes to be loaded; this variant intentionally skips routing.

## Risks & Trade-offs

Technical: Requires explicit provider lists; missing a provider breaks boot. Route files must accept `$router`. Debug
mode flips strict/auto-define defaults.

### For Humans: What This Means

Be deliberate: list every provider you need and verify the route file path. Debug on means stricter rules and no
auto-wiring safety net.

## Related Files & Folders

Technical:

- `Core/ContainerBuilder.php` — low-level wiring.
- `Http/RouteRegistrar.php` — loads routes.
- `Http/HttpApplication.php` — runtime wrapper.
- `Providers/*` — the providers you pass in.

### For Humans: What This Means

These are the pieces AppFactory orchestrates; adjust them when you change boot behavior.
