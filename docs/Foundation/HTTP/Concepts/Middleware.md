# Middleware

Global middleware in `Foundation/HTTP/Middleware` is intentionally separate from Router route middleware.

- It owns request logging, session lifecycle, CORS, CSRF gating, rate limiting, tracing, and response shaping.
- It must stay PSR-15 compatible.
- It should not reach into deleted Router internals or depend on property-style request access.
