# Term
Middleware

# Classification
Request Processing Pipeline Component

# Purpose
Intercepts, inspects, and optionally transforms HTTP requests and responses as they pass through a layered pipeline before reaching or after leaving the application core.

# Why Allowed
Middleware is one of the most universally recognized terms in web frameworks. PSR-15 (Middleware Interface) formalized it as a PHP-FIG standard. Laravel, Slim, PSR-15 implementations, Zend Expressive, Mezzio, and Symfony (via kernel listeners) all rely on this concept. Middleware describes a specific pipeline behavior: each layer decides whether to pass, short-circuit, modify, or reject the request. It is not a folder for random code — it is a well-defined contract with `handle(request, next)` semantics.

# Allowed Contexts
- HTTP request/response filtering
- Authentication and authorization checks
- CORS, rate limiting, caching headers
- Request transformation and validation
- Logging, timing, and telemetry injection
- Session management and CSRF protection

# Forbidden Misuse
- As a general "helper" for code that runs before controllers
- As a place to put business logic that should live in a flow or capability
- As a substitute for proper routing or controller design
- Middleware that does not call `$next` or return a response (broken chain)

# Ecosystem References
- https://www.php-fig.org/psr/psr-15/
- https://laravel.com/docs/middleware
- https://www.slimframework.com/docs/v4/concepts/middleware.html

# Allowed Patterns
- AuthenticateRequest
- EnforceRateLimit
- ApplyCorsHeaders
- TrimStrings
- VerifyCsrfToken

# Forbidden Patterns
- Helpers/Middleware (generic bucket)
- Utils/Middleware (concept dumping ground)
- CommonMiddleware (unclear responsibility)
