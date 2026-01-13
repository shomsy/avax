# web.routes.php

## Quick Summary

- Technical: Defines the default HTTP routes (`/`, `/health`, `/favicon.ico`) using `HttpRequestRouter` and a fallback
  that returns a 404 response.
- Uses the `$router` provided by `RouteRegistrar` to register routes without static collectors.

### For Humans: What This Means

This file is the small set of built-in endpoints and the “not found” handler for the app’s HTTP front door.

## Terminology

- **HttpRequestRouter**: Low-level router that registers and resolves routes.
- **Fallback**: Handler executed when no route matches.
- **Stream Response**: Response created from a string body for simple text replies.

### For Humans: What This Means

You’re talking directly to the router that runs at runtime, and there’s a defined “nothing matched” response.

## Think of It

Like a lobby directory: it lists “Home”, “Health”, “Favicon”, and a polite message when a room doesn’t exist.

### For Humans: What This Means

Visitors get clear directions, and if they ask for something unknown, they get a friendly 404.

## Story Example

`RouteRegistrar` includes this file, handing it `$router`. The file registers GET `/` to say “router is up”, GET
`/health` to say “ok”, GET `/favicon.ico` to return an empty 204, and sets a fallback 404 response.

### For Humans: What This Means

When the app boots, these routes become available automatically; unmatched paths get a clean 404 message.

## For Dummies

1. `$router` is provided by the registrar.
2. Call `registerRoute` for `/`, `/health`, and `/favicon.ico`.
3. Set a `fallback` that returns a 404 text response.

### For Humans: What This Means

Three routes are added, plus a default 404 handler—nothing else needed.

## How It Works (Technical)

- Each `registerRoute` call validates and stores a `RouteDefinition` inside `HttpRequestRouter`.
- Responses use `Stream::fromString()` for lightweight text bodies.
- The fallback closure builds a 404 `Response` using the request method and path for clarity.

### For Humans: What This Means

Routes are recorded in the real router, responses are simple text streams, and the fallback crafts a helpful 404
message.

## Architecture Role

- Lives under `Presentation/HTTP/routes` as the minimal route set the framework ships with.
- Loaded by `Http/RouteRegistrar` during `AppFactory::http()` boot.

### For Humans: What This Means

This is the default route pack; the factory loads it so the app has immediate endpoints.

## Methods

There are no class methods, but these callable registrations matter:

### Route: GET `/`

Technical: Returns 200 text “Avax components router is up.”

### For Humans: What This Means

Sanity check endpoint proving routing works.

#### Common mistakes

- Removing this without adding your own root route leads to 404s.

### Route: GET `/health`

Technical: Returns 200 text “ok.”

### For Humans: What This Means

Health probe for uptime checks.

#### Common mistakes

- Changing response shape without updating monitoring expectations.

### Route: GET `/favicon.ico`

Technical: Returns 204 with empty body and image/x-icon content type.

### For Humans: What This Means

Silently satisfies browser favicon requests.

#### Common mistakes

- Removing it causes noisy 404s in logs.

### Fallback

Technical: Returns 404 text with method and path.

### For Humans: What This Means

Unknown routes get a clear 404 message instead of an exception.

#### Common mistakes

- Throwing exceptions here will bubble to error handlers; keep it response-based for stability.

## Risks & Trade-offs

Technical: Minimal route set; applications must extend it. Fallback returns plain text, not HTML/JSON.

### For Humans: What This Means

Use this as a starter; replace or extend routes and adjust fallback to your API/HTML format.

## Related Files & Folders

Technical:

- `Http/RouteRegistrar.php` — includes this file.
- `Providers/HTTP/RouterServiceProvider.php` — wires router dependencies.
- `Core/AppFactory.php` — triggers route loading.

### For Humans: What This Means

Provider installs the router, AppFactory calls the registrar, and this file supplies the default routes.
