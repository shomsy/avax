# HTTP - how this works

`Foundation/HTTP` is now the integration layer around four live concerns:

1. Request assembly and typed input ownership in `Request/*`.
2. Runtime orchestration in `AppKernel.php`, `HttpKernel.php`, and `RouterBootstrapper.php`.
3. PSR-15-compatible middleware, response, security, context, and URI helpers.
4. Session capability/flow slices under `Session/*`.

Router is already a separate, how-to-compliant subsystem and acts as an external dependency boundary for the rest of
HTTP.

The practical request flow is:

1. A `ServerRequest` is assembled in `Request/*`.
2. `AppKernel` builds a middleware stack and delegates execution to `HttpKernel`.
3. Global middleware runs through `Psr15MiddlewarePipeline`.
4. The runtime router resolves the internal `ServerRequest`.
5. Responses are created through `ResponseFactory` and shaped by response middleware when needed.
6. Session/state helpers are delegated to explicit Session capability owners instead of hidden manager layers.
