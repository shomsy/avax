# Middleware - how this works

`Middleware/*` is the global pipeline layer around the router.

- `MiddlewareInterface` and `RequestHandlerInterface` define the PSR-15-style contract used by
  `Psr15MiddlewarePipeline`.
- Concrete middleware classes are small runtime owners: logging, CORS, CSRF verification, session lifecycle, rate
  limiting, and response shaping.
- `MiddlewareRegistry`, `MiddlewareGroupResolver`, and `MiddlewareResolver` are assembly helpers, not business logic
  owners.
- Route-specific middleware remains the Router subsystem's responsibility. This folder is for global HTTP pipeline
  concerns.
