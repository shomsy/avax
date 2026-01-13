# RouterServiceProvider

## Quick Summary

- Technical: Registers routing stack components (constraint validator, HTTP router, controller dispatcher, pipeline
  factory, router kernel, head fallback) and a `router` alias.
- Ensures strict-mode readiness by binding every routing dependency explicitly.

### For Humans: What This Means

It installs the entire navigation system—maps, rules, dispatcher, and fallbacks—so requests can find their handlers.

## Terminology

- **HttpRequestRouter**: Internal router that matches requests to route definitions.
- **ControllerDispatcher**: Invokes controller actions for resolved routes.
- **RoutePipelineFactory**: Builds middleware pipelines for a resolved route.
- **RouterKernel**: Coordinates fallback handling, routing, and pipeline dispatch.
- **HeadRequestFallback**: Converts HEAD to GET when needed to avoid 404s.
- **`router` alias**: Container alias that resolves to `Router`.

### For Humans: What This Means

These bindings are the routing toolkit: matcher, dispatcher, pipeline maker, fallback helper, and a friendly alias.

## Think of It

Like installing road signs, traffic lights, and an emergency detour plan before opening a new highway.

### For Humans: What This Means

You prepare all routing infrastructure up front so traffic (requests) flows smoothly.

## Story Example

During boot, `AppFactory::http()` builds the container and runs providers. This provider wires the router stack, so when
`RouteRegistrar` loads routes, everything the router needs is already in the container.

### For Humans: What This Means

By the time routes load, the router, dispatcher, and fallbacks are ready—no surprises later.

## For Dummies

1. Provider registers validator, HTTP router, dispatcher, pipeline factory, router kernel, and head fallback.
2. It adds the `'router'` alias for convenience.
3. Once routes are loaded, the router kernel can resolve and dispatch requests.

### For Humans: What This Means

This file installs every routing part; after it runs, the rest of the app can route requests safely.

## How It Works (Technical)

- Registers `RouteConstraintValidator`, then builds `HttpRequestRouter` injecting that validator.
- Registers core routing collaborators: `ControllerDispatcher`, `RoutePipelineFactory`, `Router`, `RouterKernel`, and
  `HeadRequestFallback`.
- Adds `'router'` alias returning the main `Router`.

### For Humans: What This Means

It wires every routing dependency explicitly so strict mode can resolve them without guessing.

## Architecture Role

- Lives in `Providers/HTTP` because it supplies HTTP routing primitives.
- Consumed by AppFactory/provider boot; required before loading routes.
- Keeps routing bindings deterministic and testable.

### For Humans: What This Means

If routing breaks, look here first—this provider is the single source of routing bindings.

## Methods

### Method: register(): void

Technical: Registers all routing services and the `router` alias.

### For Humans: What This Means

Call during boot to install the routing stack once.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None directly; resolution errors surface later if routing types are missing.

#### When to use it

- During application/provider bootstrap before routes are loaded.

#### Common mistakes

- Forgetting to include this provider in the AppFactory provider list.

## Risks & Trade-offs

Technical: Centralizes routing bindings—if this provider is omitted, routing fails fast. Adds all routing components
even if some apps don’t need them.

### For Humans: What This Means

Include it explicitly; without it, the router can’t start. If you don’t need HTTP, don’t register this provider.

## Related Files & Folders

Technical:

- `Http/RouteRegistrar.php` — consumes the router to load routes.
- `Http/HttpApplication.php` — runs the router at request time.
- `Core/AppFactory.php` — orchestrates provider registration.

### For Humans: What This Means

These pieces work together: AppFactory runs this provider, RouteRegistrar loads routes into the router, and
HttpApplication uses the router to handle requests.
