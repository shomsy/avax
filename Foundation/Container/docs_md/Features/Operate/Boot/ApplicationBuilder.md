# ApplicationBuilder

## Quick Summary
- This file provides a fluent builder for configuring an HTTP application around the container.
- It exists so bootstrap configuration reads like a story (routes, middleware, exception handling, build).
- It removes the complexity of wiring a container + router + config by centralizing “build the app” logic.

### For Humans: What This Means
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

### For Humans: What This Means
You get a clean place to configure “web app behavior” without scattering setup code.

## Think of It
Think of this builder like ordering a custom sandwich:
- Pick bread (routes).
- Add toppings (middleware).
- Choose what happens if something goes wrong (exception handler).
- Then “make it” (build).

### For Humans: What This Means
You decide the ingredients. The builder assembles them in the right order.

## Story Example
In your front controller, you do:
`Application::start($root)->exposeWeb('routes/web.php')->build()`.
The builder creates a container, binds core router/config services, then returns an `Application` ready to load routes and run.

### For Humans: What This Means
You get an application object without manually creating a container kernel.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Create builder with base path.
2. Set web/api routes (optional).
3. Add middleware (optional).
4. Set exception handler (optional).
5. Call `build()` to get `Application`.

## How It Works (Technical)
The builder configures a `ContainerBuilder` (cache directory, core HTTP bindings, aliases, config bindings), then builds the container and constructs `Application`. Route files are loaded if configured. Middleware and exception handler callbacks are stored but currently only asserted for future integration.

### For Humans: What This Means
It builds the container, makes the app, loads routes, and keeps placeholders for middleware/error wiring.

## Architecture Role
- Why it lives in `Features/Operate/Boot`: it’s an app bootstrap construction tool.
- What depends on it: user bootstrap code and `Application::start()`.
- What it depends on: `ContainerBuilder` and core HTTP bindings.
- System-level reasoning: builders reduce the chance of “half-configured” containers.

### For Humans: What This Means
It helps you avoid forgetting a critical binding during setup.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $basePath)

#### Technical Explanation
Initializes the builder and prepares a container builder with a cache directory.

##### For Humans: What This Means
It sets the “project root” and configures where caches should live.

##### Parameters
- `string $basePath`

##### Returns
- Returns nothing.

##### Throws
- Depends on container builder behavior.

##### When to Use It
- Use `Application::start()` or new it directly.

##### Common Mistakes
- Passing a base path that doesn’t contain expected folders (Config, var/cache).

### Method: exposeWeb(string $path)

#### Technical Explanation
Sets the web routes file path to load during build.

##### For Humans: What This Means
“This file contains my web routes.”

##### Parameters
- `string $path`

##### Returns
- `$this`

##### Throws
- No explicit exceptions.

##### When to Use It
- For browser-oriented routes.

##### Common Mistakes
- Passing a path that isn’t reachable from runtime working directory.

### Method: exposeApi(string $path)

#### Technical Explanation
Sets the API routes file path to load during build.

##### For Humans: What This Means
“This file contains my API routes.”

##### Parameters
- `string $path`

##### Returns
- `$this`

##### Throws
- No explicit exceptions.

##### When to Use It
- For REST/JSON routes.

##### Common Mistakes
- Mixing API and web routes unintentionally and applying the wrong middleware.

### Method: pipe(callable $middleware)

#### Technical Explanation
Adds a middleware callable to the builder’s pipeline list (future wiring).

##### For Humans: What This Means
“Wrap requests with this behavior.”

##### Parameters
- `callable $middleware`

##### Returns
- `$this`

##### Throws
- No explicit exceptions.

##### When to Use It
- When assembling a global middleware stack.

##### Common Mistakes
- Expecting the middleware to run when the wiring is still stubbed.

### Method: handle(callable $handler)

#### Technical Explanation
Stores the global exception handler callback (future wiring).

##### For Humans: What This Means
“If something blows up, handle it like this.”

##### Parameters
- `callable $handler`

##### Returns
- `$this`

##### Throws
- No explicit exceptions.

##### When to Use It
- When you want centralized exception-to-response behavior.

##### Common Mistakes
- Passing a handler with the wrong signature and expecting DI to fix it automatically.

### Method: build()

#### Technical Explanation
Builds the container, constructs `Application`, loads configured routes, and returns the app.

##### For Humans: What This Means
This is the “make the application” button.

##### Parameters
- None.

##### Returns
- `Application`

##### Throws
- `RuntimeException` (as documented) if bootstrap fails.

##### When to Use It
- Once during application startup.

##### Common Mistakes
- Calling `build()` multiple times and creating multiple containers unintentionally.

## Risks, Trade-offs & Recommended Practices
- Risk: Placeholder wiring (middleware/exceptions) can confuse expectations.
  - Why it matters: you may assume `pipe()` already affects request handling.
  - Design stance: keep unfinished wiring explicit and documented.
  - Recommended practice: ensure your real kernel uses the stored middleware/handler or remove the API until implemented.

### For Humans: What This Means
If it looks like it should work but doesn’t yet, that’s a trap. Make unfinished features obvious.

## Related Files & Folders
- `docs_md/Features/Operate/Boot/Application.md`: The object produced by this builder.
- `docs_md/Core/ContainerKernel.md`: Where resolution logic lives under the hood.

### For Humans: What This Means
Builder assembles the “shell”; kernel does the heavy lifting.

