# Application

## Quick Summary

- This file implements an HTTP-oriented application façade that wraps the container and provider lifecycle.
- It exists so your app has one “entry point” for booting providers, loading routes, handling requests, and managing scopes.
- It removes the complexity of gluing together router + container + provider boot order by centralizing the lifecycle.

### For Humans: What This Means (Summary)

This is the “main control center” of your web app: it starts up, loads routes, runs providers, handles a request, and then cleans up.

## Terminology (MANDATORY, EXPANSIVE)

- **Application façade**: A friendly API surface over deeper systems.
  - In this file: `Application` wraps a `ContainerInternalInterface` and exposes helper methods.
  - Why it matters: you don’t want every part of the app talking to kernel internals.
- **Provider lifecycle**: Register then boot providers.
  - In this file: `register()` adds providers; `boot()` boots them; `bootProvider()` calls provider boot through container invocation.
  - Why it matters: correct boot order prevents missing dependencies.
- **Scope**: A runtime boundary for scoped lifetimes (typically per request).
  - In this file: `run()` begins scope; `terminate()` ends scope.
  - Why it matters: request-specific state shouldn’t leak across requests.
- **Route loading**: Loading route definitions from file, optionally from cache.
  - In this file: `loadRoutes()` supports cached routes via `RouteCacheLoader`.
  - Why it matters: route registration can be expensive; caching helps.
- **PSR-11 container delegation**: Implementing `ContainerInterface` by delegating to an internal container.
  - In this file: `has()` and `get()` delegate.
  - Why it matters: it makes the app usable wherever a PSR container is expected.

### For Humans: What This Means (Terminology)

This class is where “all the startup pieces” come together so the rest of your app stays simple.

## Think of It

Think of it like a theater stage manager:

- Loads the script (routes).
- Brings actors on stage (providers).
- Starts the show (handles the request).
- Cleans the stage after (ends scope).

### For Humans: What This Means (Analogy)

You don’t want actors improvising setup; the stage manager makes everything predictable.

## Story Example

You call `Application::start($root)` to get an `ApplicationBuilder`, configure routes, then build the app. At runtime, you call `$app->run()`. The app starts a scope, boots providers, creates a request object, routes it, sends the response, then ends the scope.

### For Humans: What This Means (Story)

You get a “one method to run the app” experience without losing structure.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Walkthrough)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Create app (usually via builder).
2. Register providers.
3. Load routes.
4. Call `run()`.
5. App handles request and cleans up.

## How It Works (Technical)

The constructor registers base bindings (including config via `Settings`) and stores references in the container (`app` alias). Route loading uses a cache file when available; otherwise it evaluates the routes file and flushes buffered route definitions into `HttpRequestRouter`. Provider registration stores providers by class name and runs `register()`, then runs `boot()` once the app is booted. `run()` controls scope boundaries and dispatches request handling through the `Router`.

### For Humans: What This Means (Technical)

It’s a lifecycle orchestrator: setup → resolve → respond → cleanup.

## Architecture Role

- Why it lives in `Features/Operate/Boot`: it’s the runtime boot/execute coordinator.
- What depends on it: provider system, HTTP routing flow, and anything needing `app` access.
- What it depends on: internal container, router stack, settings/config loader.
- System-level reasoning: a single lifecycle coordinator reduces scattered bootstrap logic.

### For Humans: What This Means (Architecture)

If something is wrong with app startup or request lifecycle, this is the first place to look.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $basePath, ContainerInternalInterface $container)

#### Technical Explanation: __construct

Initializes the application, registers base bindings, and stores app references in the container.

##### For Humans: What This Means: __construct

It sets up the app’s “foundations” so other providers can build on top.

##### Parameters: __construct

- `string $basePath`: Root directory path.
- `ContainerInternalInterface $container`: The underlying container kernel façade.

##### Returns: __construct

- Returns nothing.

##### Throws: __construct

- No explicit exceptions.

##### When to Use It: __construct

- Usually called by builders/bootstrap code, not manually.

##### Common Mistakes: __construct

- Passing a container that isn’t fully bootstrapped for HTTP usage.

### Method: instance(string $abstract, object $instance)

#### Technical Explanation: instance

Registers an existing object instance into the underlying container.

##### For Humans: What This Means: instance

You’re saying: “when asked for this id, return this exact object.”

##### Parameters: instance

- `string $abstract`
- `object $instance`

##### Returns: instance

- Returns nothing.

##### Throws: instance

- Depends on container behavior.

##### When to Use It: instance

- When bridging runtime objects like the current request.

##### Common Mistakes: instance

- Registering request-specific instances outside of a request scope.

### Method: start(string $root)

#### Technical Explanation: start

Static entry point returning `ApplicationBuilder` for fluent configuration.

##### For Humans: What This Means: start

It’s the “start building my app” helper.

##### Parameters: start

- `string $root`

##### Returns: start

- `ApplicationBuilder`

##### Throws: start

- No explicit exceptions.

##### When to Use It: start

- As your main app bootstrap entry point.

##### Common Mistakes: start

- Confusing it with “run the app”; it only returns a builder.

### Method: bind(string $abstract, mixed $concrete = null)

#### Technical Explanation: bind

Registers a transient binding through the registrar.

##### For Humans: What This Means: bind

“Give me a new instance each time.”

##### Parameters: bind

- `string $abstract`
- `mixed $concrete`

##### Returns: bind

- `BindingBuilder`

##### Throws: bind

- Depends on container state.

##### When to Use It: bind

- For stateless/lightweight services.

##### Common Mistakes: bind

- Binding heavy services as transient and paying the cost repeatedly.

### Method: singleton(string $abstract, mixed $concrete = null)

#### Technical Explanation: singleton

Registers a singleton binding through the registrar.

##### For Humans: What This Means: singleton

“Create once, reuse forever (for this container).”

##### Parameters: singleton

- `string $abstract`
- `mixed $concrete`

##### Returns: singleton

- `BindingBuilder`

##### Throws: singleton

- Depends on container state.

##### When to Use It: singleton

- For shared infrastructure.

##### Common Mistakes: singleton

- Making request-specific state a singleton.

### Method: scoped(string $abstract, mixed $concrete = null)

#### Technical Explanation: scoped

Registers a scoped binding through the registrar.

##### For Humans: What This Means: scoped

“Reuse within one request/scope, then release.”

##### Parameters: scoped

- `string $abstract`
- `mixed $concrete`

##### Returns: scoped

- `BindingBuilder`

##### Throws: scoped

- Depends on container state.

##### When to Use It: scoped

- For request-bound services.

##### Common Mistakes: scoped

- Forgetting to manage scope boundaries.

### Method: make(string $abstract, array $parameters = [])

#### Technical Explanation: make

Forces construction of an object with optional parameter overrides.

##### For Humans: What This Means: make

“Build this now, even if it’s not registered the usual way.”

##### Parameters: make

- `string $abstract`
- `array $parameters`

##### Returns: make

- `object`

##### Throws: make

- Depends on resolution failures.

##### When to Use It: make

- Advanced scenarios requiring explicit parameter overrides.

##### Common Mistakes: make

- Using `make()` instead of proper registration in normal app code.

### Method: getContainer()

#### Technical Explanation: getContainer

Returns the underlying internal container interface.

##### For Humans: What This Means: getContainer

It’s the “escape hatch” when you need deeper control.

##### Parameters: getContainer

- None.

##### Returns: getContainer

- `ContainerInternalInterface`

##### Throws: getContainer

- No explicit exceptions.

##### When to Use It: getContainer

- Diagnostics and advanced integration.

##### Common Mistakes: getContainer

- Overusing the escape hatch and bypassing the app façade.

### Method: loadRoutes(string $path)

#### Technical Explanation: loadRoutes

Loads routes from cache if possible, otherwise from a routes file and then caches them.

##### For Humans: What This Means: loadRoutes

It learns what URLs exist and what they should do.

##### Parameters: loadRoutes

- `string $path`

##### Returns: loadRoutes

- Returns nothing.

##### Throws: loadRoutes

- Catches cache loading/writing exceptions internally; may throw if underlying router throws outside guarded paths.

##### When to Use It: loadRoutes

- During bootstrap, before `run()`.

##### Common Mistakes: loadRoutes

- Loading the same routes multiple times without tracking.

### Method: register(string|ServiceProvider $provider)

#### Technical Explanation: register

Registers a provider (by class name or instance), runs its register hook, and boots it immediately if the app is already booted.

##### For Humans: What This Means: register

It installs a feature module into your app.

##### Parameters: register

- `string|ServiceProvider $provider`

##### Returns: register

- `ServiceProvider`: The provider instance (existing or newly created).

##### Throws: register

- Container exceptions if provider boot invocation fails.

##### When to Use It: register

- During bootstrap to add providers.

##### Common Mistakes: register

- Registering providers after `run()` has started and expecting full determinism.

### Method: call(callable|string $callable, array $parameters = [])

#### Technical Explanation: call

Invokes a callable using container call semantics (dependency injection).

##### For Humans: What This Means: call

“Call this function/method and let the container fill in dependencies.”

##### Parameters: call

- `callable|string $callable`
- `array $parameters`

##### Returns: call

- `mixed`

##### Throws: call

- Depends on container invocation behavior.

##### When to Use It: call

- When executing callables that rely on DI.

##### Common Mistakes: call

- Passing ambiguous strings that don’t resolve to real callables.

### Method: run()

#### Technical Explanation: run

Begins a scope, boots providers, creates a request, routes it, sends the response, and terminates the scope.

##### For Humans: What This Means: run

It runs one full HTTP request lifecycle.

##### Parameters: run

- None.

##### Returns: run

- `ResponseInterface`

##### Throws: run

- Returns in finally block ensures `terminate()` runs; routing errors may bubble depending on router behavior.

##### When to Use It: run

- In your front controller as the main “handle request” call.

##### Common Mistakes: run

- Calling `run()` without having loaded routes.

### Method: boot()

#### Technical Explanation: boot

Boots all registered providers once, marking the app as booted.

##### For Humans: What This Means: boot

It runs the “turn everything on” step after wiring is done.

##### Parameters: boot

- None.

##### Returns: boot

- Returns nothing.

##### Throws: boot

- Container exceptions if boot invocation fails.

##### When to Use It: boot

- Usually called internally by `run()`.

##### Common Mistakes: boot

- Assuming boot happens automatically without calling `run()` or `boot()`.

### Method: terminate()

#### Technical Explanation: terminate

Ends the current scope boundary.

##### For Humans: What This Means: terminate

It cleans up request-scoped instances so state doesn’t leak.

##### Parameters: terminate

- None.

##### Returns: terminate

- Returns nothing.

##### Throws: terminate

- Depends on scope subsystem.

##### When to Use It: terminate

- Called automatically by `run()` in `finally`.

##### Common Mistakes: terminate

- Ending scopes manually in the middle of request handling.

### Method: isBooted()

#### Technical Explanation: isBooted

Returns whether providers have been booted.

##### For Humans: What This Means: isBooted

It tells you if startup initialization has already happened.

##### Parameters: isBooted

- None.

##### Returns: isBooted

- `bool`

##### Throws: isBooted

- No explicit exceptions.

##### When to Use It: isBooted

- Diagnostics.

##### Common Mistakes: isBooted

- Using it as a “health check”; it’s only a boot flag.

### Method: basePath(string $path = '')

#### Technical Explanation: basePath

Resolves a filesystem path relative to the application base path.

##### For Humans: What This Means: basePath

It helps you build paths without hardcoding separators everywhere.

##### Parameters: basePath

- `string $path`

##### Returns: basePath

- `string`

##### Throws: basePath

- No explicit exceptions.

##### When to Use It: basePath

- When configuring cache directories or view paths.

##### Common Mistakes: basePath

- Passing already-absolute paths and expecting normalization.

### Method: get(string $id)

#### Technical Explanation: get

Proxies resolution to the underlying container (PSR-11).

##### For Humans: What This Means: get

“Get me this service from the container.”

##### Parameters: get

- `string $id`: Service identifier.

##### Returns: get

- `mixed`: The resolved service.

##### Throws: get

- `NotFoundExceptionInterface` if not found.
- `ContainerExceptionInterface` if resolution fails.

##### When to Use It: get

- When retrieving services directly (though injection is preferred).

##### Common Mistakes: get

- Using it inside classes instead of proper dependency injection.

### Method: has(string $id)

#### Technical Explanation: has

Checks if a service is registered in the underlying container (PSR-11).

##### For Humans: What This Means: has

“Do you know about this service?”

##### Parameters: has

- `string $id`: Service identifier.

##### Returns: has

- `bool`: True if known, false otherwise.

##### Throws: has

- No explicit exceptions.

##### When to Use It: has

- Conditional logic based on service availability.
- Diagnostics.

##### Common Mistakes: has

- Assuming `has()` implies `get()` will succeed (resolution errors can still happen).

## Risks, Trade-offs & Recommended Practices

- Risk: This class is “big” and can accumulate responsibilities.
  - Why it matters: it can become hard to evolve.
  - Design stance: treat it as an orchestrator; push specialized logic into dedicated services.
  - Recommended practice: keep route, provider, and scope logic testable via collaborators.

### For Humans: What This Means (Risks)

It’s okay for this to be the “hub”, but don’t let it become the “everything class”.

## Related Files & Folders

- `docs_md/Features/Operate/Boot/ApplicationBuilder.md`: Primary way to construct an application.
- `docs_md/Providers/index.md`: Providers registered into the application.
- `docs_md/Features/Operate/Scope/ScopeManager.md`: Scope boundaries used by `run()`/`terminate()`.

### For Humans: What This Means (Related)

Builder creates the app, providers install features, scope keeps requests isolated.
