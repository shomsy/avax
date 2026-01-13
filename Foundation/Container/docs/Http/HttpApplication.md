# HttpApplication

## Quick Summary

- Thin HTTP runtime that opens a container scope, builds the request, resolves the router, sends the response, and
  closes the scope.
- Exists to replace the old multi-layer Application/Builder with a direct, minimal runner.

### For Humans: What This Means

It’s the “run loop” for the web: start scope, handle request, send response, end scope.

## Terminology

- **Scope**: Container lifetime boundary for a request.
- **Router**: Component that resolves Request to Response.
- **Send**: If the response has `send()`, it’s invoked to emit output.

### For Humans: What This Means

Open a request boundary, ask the router for an answer, output it, and clean up.

## Think of It

Like a cashier session: open drawer (scope), process customer (request→response), close drawer (end scope).

### For Humans: What This Means

Every request gets a clean start and finish.

## Story Example

Built via `AppFactory::http(...)`, then:

```php
$response = $app->run();
```

It injects the Request into the container, resolves Router, and returns/sends the Response.

### For Humans: What This Means

One call processes the current HTTP request using DI.

## For Dummies

1) `beginScope()`
2) `Request::createFromGlobals()`
3) `$router->resolve($request)`
4) Send response if possible
5) `endScope()`

### For Humans: What This Means

Start, handle, output, clean up.

## How It Works (Technical)

- Starts a scope on the container.
- Creates request from globals and registers it in the container.
- Resolves `Router` from the container and calls `resolve()`.
- If response has `send()`, it’s called; response returned either way.
- Scope is always ended in `finally`.

### For Humans: What This Means

The scope is guaranteed to close even if something fails mid-request.

## Architecture Role

- Lives in `Http/` as the execution wrapper.
- Depends on Container, Router, Request.
- Returned by `AppFactory::http()`.

### For Humans: What This Means

It’s the object you actually run in `index.php`.

## Methods

### Method: getContainer(): Container {#method-getcontainer}

Technical: Returns the container instance that powers the HTTP application.

### For Humans: What This Means

Gives you direct access to the DI container if you need to swap bindings or inspect state before running.

#### Parameters

- None.

#### Returns

- `Container` The underlying container.

#### Throws

- None.

#### When to use it

- In tests or bootstraps that need to inject fakes or inspect bindings.

#### Common mistakes

- Overwriting critical bindings without understanding strict mode implications.

### Method: run(): ResponseInterface {#method-run}

Technical: Runs the lifecycle; begins a scope, resolves the request, invokes the router, sends the response when
possible, and closes the scope.

### For Humans: What This Means

This is the “handle the HTTP request now” button; it opens scope, routes, responds, and cleans up.

#### Parameters

- None.

#### Returns

- `ResponseInterface` The HTTP response produced by the router.

#### Throws

- Exceptions from routing or resolution if bindings are missing or handlers fail.

#### When to use it

- In your front controller (`index.php`) or integration tests that simulate full HTTP flow.

#### Common mistakes

- Forgetting router/middleware providers; forgetting globals are read inside.

## Risks & Trade-offs

- If router isn’t bound, strict mode fails. Ensure providers or AppFactory prebindings cover Router stack.
- Response sending uses `send()` if present; otherwise relies on caller to output body.

### For Humans: What This Means

Make sure the router services exist; know whether your response auto-sends.

## Related Files & Folders

- `Http/HttpKernel.php`: Optional middleware kernel.
- `Http/RouteRegistrar.php`: Loads route definitions.
- `Core/AppFactory.php`: Builds and returns this app.

### For Humans: What This Means

AppFactory builds it; registrar feeds routes; kernel can wrap routing with middleware.
