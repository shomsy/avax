# Runtime Component

> This component owns the request lifecycle, middleware pipeline, and runtime execution context for the entire AvaX platform.

## What Lives Here

- **PublicSurface/** — Runtime entry points for request handling and middleware registration
- **Flows/** — Request processing flow, middleware execution flow
- **Capabilities/** — Middleware pipeline, request context, response emission
- **Configuration/** — Middleware registration, pipeline assembly
- **Foundation/** — Request, Response, Context value objects

## What Does NOT Live Here

- Authentication middleware (belongs in Identity component)
- Route matching (belongs in Router component)
- HTTP parsing or response formatting (belongs in HTTP component)
- Business logic or domain behavior (belongs in domain components)

## Ownership Model

Owned by the Platform Core team. Changes to the middleware pipeline, request lifecycle, or runtime execution model require core team review.

## Architectural Intent

Runtime is the execution backbone. It receives a request, runs it through the middleware pipeline, dispatches to the appropriate handler, and sends the response. It must not contain business logic, domain behavior, or component-specific concerns. All component-specific middleware is registered by those components through Runtime's configuration.

## Common Mistakes

1. **Adding domain logic to middleware** — Middleware should only handle cross-cutting concerns (auth, logging, CORS).
2. **Bypassing the pipeline** — Calling handlers directly instead of going through the pipeline breaks middleware guarantees.
3. **State leaks between requests** — Runtime must reset mutable state between requests in long-lived workers.

## Entry Points

- Start with `PublicSurface/Runtime.php` for the main runtime entry point
- Start with `Configuration/MiddlewarePipelineBuilder.php` for pipeline configuration
