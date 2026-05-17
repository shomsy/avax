# Term
Controller

# Classification
Request Handler / Routing Endpoint

# Purpose
Receives an HTTP request, delegates to domain or application logic, and returns an HTTP response — acting as the thin coordination layer between the web layer and the application core.

# Why Allowed
Controller is a core MVC pattern term used across virtually every PHP framework: Laravel, Symfony, Yii, CodeIgniter, Zend/Mezzio, and Slim. It describes a specific responsibility — handling a request at a route boundary and producing a response. In modern architecture, controllers are intentionally thin: they parse input, invoke flows or use cases, and format output. The term is not a generic bucket; it is a well-defined layer in the request-response cycle.

# Allowed Contexts
- HTTP route handlers that coordinate request parsing, delegation, and response formatting
- API endpoint controllers that return JSON/XML responses
- CLI command controllers for console routes
- Web page controllers that render views or templates
- Form request handlers that validate and dispatch

# Forbidden Misuse
- As a dumping ground for business logic (fat controllers)
- As a service registry or dependency container
- As a place for database queries that should be in repositories or capabilities
- As a catch-all for code triggered by events, queues, or background jobs (those are not HTTP controllers)

# Ecosystem References
- https://laravel.com/docs/controllers
- https://symfony.com/doc/current/controller.html
- https://www.slimframework.com/docs/v4/objects/routing.html

# Allowed Patterns
- UserController (handles user-related HTTP routes)
- OrderApiController (handles order API endpoints)
- HealthCheckController (exposes health check endpoint)

# Forbidden Patterns
- Services/Controller (generic bucket)
- CommonController (unclear responsibility)
- BaseController with unrelated shared logic (use composition instead)
