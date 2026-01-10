# ApplicationBuilder

## Quick Summary

- This file provides a fluent builder for configuring an HTTP application around the container.
- It exists so bootstrap configuration reads like a story (routes, middleware, exception handling, build).
- It removes the complexity of wiring a container + router + config by centralizing “build the app” logic.

### For Humans: What This Means (Summary)

It’s the “setup wizard” for your app. You describe what you want, then it assembles the app.

## Terminology (MANDATORY, EXPANSIVE)

- **Fluent API**: A chainable configuration style.
  - In this file: `exposeWeb()->pipe()->handle()->build()`.
  - Why it matters: bootstrap code becomes readable and discoverable.
- **Bootstrap**: The process of constructing and configuring the container and app.
  - In this file: performed by `build()`.
  - Why it matters: boot must be deterministic and safe.
- **Middleware**: A wrapper around request handling.
  - In this file: stored as callables for future wiring (currently stubs).
  - Why it matters: it’s the standard place for auth/logging/CORS.
- **Exception handler**: Central handler for uncaught exceptions.
  - In this file: stored as a callback (currently stubbed for future wiring).
  - Why it matters: it provides consistent failure behavior.

### For Humans: What This Means (Terminology)

You get a clean place to configure “web app behavior” without scattering setup code.

## Think of It

Think of this builder like ordering a custom sandwich:

- Pick bread (routes).
- Add toppings (middleware).
- Choose what happens if something goes wrong (exception handler).
- Then “make it” (build).

### For Humans: What This Means (Analogy)

You decide the ingredients. The builder assembles them in the right order.

## Story Example

In your front controller, you do:
`Application::start($root)->exposeWeb('routes/web.php')->build()`.
The builder creates a container, binds core router/config services, then returns an `Application` ready to load routes and run.

### For Humans: What This Means (Story)

You get an application object without manually creating a container kernel.

## For Dummies

1. Create builder with base path.
2. Set web/api routes (optional).
3. Add middleware (optional).
4. Set exception handler (optional).
5. Call `build()` to get `Application`.

### For Humans: What This Means (Walkthrough)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

## How It Works (Technical)

The builder configures a `ContainerBuilder` (cache directory, core HTTP bindings, aliases, config bindings), then builds the container and constructs `Application`. Route files are loaded if configured. Middleware and exception handler callbacks are stored but currently only asserted for future integration.

### For Humans: What This Means (Technical)

It builds the container, makes the app, loads routes, and keeps placeholders for middleware/error wiring.

## Architecture Role

- Why it lives in `Features/Operate/Boot`: it’s an app bootstrap construction tool.
- What depends on it: user bootstrap code and `Application::start()`.
- What it depends on: `ContainerBuilder` and core HTTP bindings.
- System-level reasoning: builders reduce the chance of “half-configured” containers.

### For Humans: What This Means (Architecture)

It helps you avoid forgetting a critical binding during setup.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $basePath)

#### Technical Explanation: __construct

Initializes the builder and prepares a container builder with a cache directory.

##### For Humans: What This Means (__construct)

It sets the “project root” and configures where caches should live.

##### Parameters (__construct)

- `string $basePath`

##### Returns (__construct)

- Returns nothing.

##### Throws (__construct)

- Depends on container builder behavior.

##### When to Use It (__construct)

- Use `Application::start()` or new it directly.

##### Common Mistakes (__construct)

- Passing a base path that doesn’t contain expected folders (Config, var/cache).

### Method: exposeWeb(string $path)

#### Technical Explanation: exposeWeb

Sets the web routes file path to load during build.

##### For Humans: What This Means (exposeWeb)

“This file contains my web routes.”

##### Parameters (exposeWeb)

- `string $path`

##### Returns (exposeWeb)

- `$this`

##### Throws (exposeWeb)

- No explicit exceptions.

##### When to Use It (exposeWeb)

- For browser-oriented routes.

##### Common Mistakes (exposeWeb)

- Passing a path that isn’t reachable from runtime working directory.

### Method: exposeApi(string $path)

#### Technical Explanation: exposeApi

Sets the API routes file path to load during build.

##### For Humans: What This Means (exposeApi)

“This file contains my API routes.”

##### Parameters (exposeApi)

- `string $path`

##### Returns (exposeApi)

- `$this`

##### Throws (exposeApi)

- No explicit exceptions.

##### When to Use It (exposeApi)

- For REST/JSON routes.

##### Common Mistakes (exposeApi)

- Mixing API and web routes unintentionally and applying the wrong middleware.

### Method: pipe(callable $middleware)

#### Technical Explanation: pipe

Adds a middleware callable to the builder’s pipeline list (future wiring).

##### For Humans: What This Means (pipe)

“Wrap requests with this behavior.”

##### Parameters (pipe)

- `callable $middleware`

##### Returns (pipe)

- `$this`

##### Throws (pipe)

- No explicit exceptions.

##### When to Use It (pipe)

- When assembling a global middleware stack.

##### Common Mistakes (pipe)

- Expecting the middleware to run when the wiring is still stubbed.

### Method: handle(callable $handler)

#### Technical Explanation: handle

Stores the global exception handler callback (future wiring).

##### For Humans: What This Means (handle)

“If something blows up, handle it like this.”

##### Parameters (handle)

- `callable $handler`

##### Returns (handle)

- `$this`

##### Throws (handle)

- No explicit exceptions.

##### When to Use It (handle)

- When you want centralized exception-to-response behavior.

##### Common Mistakes (handle)

- Passing a handler with the wrong signature and expecting DI to fix it automatically.

### Method: build()

#### Technical Explanation: build

Builds the container, constructs `Application`, loads configured routes, and returns the app.

##### For Humans: What This Means (build)

This is the “make the application” button.

##### Parameters (build)

- None.

##### Returns (build)

- `Application`

##### Throws (build)

- `RuntimeException` (as documented) if bootstrap fails.

##### When to Use It (build)

- Once during application startup.

##### Common Mistakes (build)

- Calling `build()` multiple times and creating multiple containers unintentionally.

### Method: withProviders(array $providers)

#### Technical Explanation: withProviders

Registers a list of service provider classes or instances to be loaded during the build process.

##### For Humans: What This Means (withProviders)

“Also load these extra providers that aren’t in the core framework.”

##### Parameters (withProviders)

- `array $providers`: List of class names (strings) or `ServiceProvider` instances.

##### Returns (withProviders)

- `$this`

##### Throws (withProviders)

- No explicit exceptions, but validation happens during `build()`.

##### When to Use It (withProviders)

- When you have application-specific providers that are not part of the core auto-discovery.

##### Common Mistakes (withProviders)

- Passing non-ServiceProvider classes.

### Method: registerCoreProviders(Application $app)

#### Technical Explanation: registerCoreProviders

Internal helper that scans the `Foundation/Container/Providers` directory and registers found providers.

##### For Humans: What This Means (registerCoreProviders)

It finds the built-in stuff (Auth, DB, etc.) and turns it on.

##### Parameters (registerCoreProviders)

- `Application $app`

##### Returns (registerCoreProviders)

- `void`

##### Throws (registerCoreProviders)

- No explicit exceptions.

## Risks, Trade-offs & Recommended Practices

- Risk: Placeholder wiring (middleware/exceptions) can confuse expectations.
  - Why it matters: you may assume `pipe()` already affects request handling.
  - Design stance: keep unfinished wiring explicit and documented.
  - Recommended practice: ensure your real kernel uses the stored middleware/handler or remove the API until implemented.

### For Humans: What This Means (Risks)

If it looks like it should work but doesn’t yet, that’s a trap. Make unfinished features obvious.

## Related Files & Folders

- `docs/Features/Operate/Boot/Application.md`: The object produced by this builder.
- `docs/Core/ContainerKernel.md`: Where resolution logic lives under the hood.

### For Humans: What This Means (Related)

Builder assembles the “shell”; kernel does the heavy lifting.
