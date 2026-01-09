# Kernel

## Quick Summary
- This file provides a small “application kernel” utility focused on resolving configured middleware.
- It exists to safely read middleware configuration without breaking the lifecycle on config errors.
- It removes the complexity of handling config failures by returning safe defaults (empty list).

### For Humans: What This Means
It’s the part that says “what middleware should run?” and it fails safely instead of crashing.

## Terminology (MANDATORY, EXPANSIVE)
- **Kernel**: A coordinator that prepares runtime behavior for request handling.
  - In this file: it focuses only on middleware configuration resolution.
  - Why it matters: kernels are where you centralize lifecycle decisions.
- **Middleware list**: An ordered list of middleware to apply.
  - In this file: read from config via a resolver closure.
  - Why it matters: ordering affects security and behavior.
- **Config resolver**: A callable that returns configuration data.
  - In this file: stored as `$configResolver` with a safe default.
  - Why it matters: it abstracts “where config comes from”.
- **Safety net**: Catching errors and returning fallback values.
  - In this file: `resolveConfiguredMiddlewares()` catches `Throwable` and returns `[]`.
  - Why it matters: it keeps request lifecycle from failing just because config is missing.

### For Humans: What This Means
This class keeps your app from crashing due to “missing config for middleware”.

## Think of It
Think of it like a shopping list reader. If the list is missing, it doesn’t panic—it just says “okay, we’ll cook without extras.”

### For Humans: What This Means
It’s better to run with no middleware than to crash because a config file wasn’t found (depending on your environment).

## Story Example
In development, you forget to set `kernel.middlewares` in config. Without safety, your app could crash before routing. With this kernel, middleware resolves to an empty array, and the rest of the lifecycle continues.

### For Humans: What This Means
It turns missing config into a predictable default instead of a fatal error.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Kernel is created with router and error handler.
2. It stores a config reader.
3. You call `resolveConfiguredMiddlewares()`.
4. You get an array (or `[]` if config fails).

## How It Works (Technical)
The constructor accepts an optional callable config resolver. If absent, it builds a default resolver that reads `kernel.middlewares` via a `config()` helper. The resolver is stored as a `Closure` for consistent invocation. Resolution catches all `Throwable` and enforces that the result is an array.

### For Humans: What This Means
It’s defensive code: “always return an array, even when config is broken.”

## Architecture Role
- Why it lives in `Features/Operate/Boot`: it supports request lifecycle setup.
- What depends on it: whatever component assembles middleware for request handling.
- What it depends on: router, error handler, and configuration resolver.
- System-level reasoning: middleware is cross-cutting; centralizing its configuration avoids duplication.

### For Humans: What This Means
If you want one consistent middleware list, one place should compute it.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(RouterInterface $router, ErrorHandler $errorHandler, callable|null $configResolver = null)

#### Technical Explanation
Stores router and error handler, and creates/stores a safe config resolver closure.

##### For Humans: What This Means
It prepares the kernel to ask “what middleware should I run?” safely.

##### Parameters
- `RouterInterface $router`
- `ErrorHandler $errorHandler`
- `callable|null $configResolver`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- During boot of HTTP request handling stack.

##### Common Mistakes
- Providing a resolver that throws and expecting the kernel not to catch it (it will catch and return empty).

### Method: resolveConfiguredMiddlewares()

#### Technical Explanation
Invokes the config resolver and returns the middleware list, or `[]` on any error.

##### For Humans: What This Means
It answers: “what middleware should we apply?”

##### Parameters
- None.

##### Returns
- `array`

##### Throws
- No (it catches `Throwable`).

##### When to Use It
- When building the middleware pipeline for a request.

##### Common Mistakes
- Treating an empty array as “everything is fine” in production; it may indicate missing config.

## Risks, Trade-offs & Recommended Practices
- Risk: Silent fallback hides misconfiguration.
  - Why it matters: in production, missing middleware can be a security issue.
  - Design stance: safe fallback is useful, but production should alert.
  - Recommended practice: log warnings when fallback happens (if a logger is available).

### For Humans: What This Means
Failing safely is good, but failing loudly (in logs/monitoring) is how you notice problems.

## Related Files & Folders
- `docs_md/Providers/HTTP/MiddlewareServiceProvider.md`: Registers middleware infrastructure.
- `docs_md/Providers/HTTP/RouterServiceProvider.md`: Routing is where middleware is typically applied.

### For Humans: What This Means
Middleware and routing usually work together: route chooses, middleware wraps.

