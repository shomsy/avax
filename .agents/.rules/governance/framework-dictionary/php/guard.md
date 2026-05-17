# Term
Guard

# Classification
Authentication / Authorization Component

# Purpose
Intercepts a request or operation to verify identity, enforce access rules, or validate permissions before allowing the request to proceed to its intended target.

# Why Allowed
Guard is a recognized authentication pattern in PHP and broader web frameworks. Laravel introduced it as an authentication guard system (`SessionGuard`, `TokenGuard`, `SanctumGuard`) that defines how users are authenticated per request. Symfony uses firewall listeners and authenticators for similar behavior. ASP.NET Core also uses guards in its authentication pipeline. A guard answers "who is this user, and are they authenticated?" — it is distinct from middleware (which handles broader request concerns) and policies (which handle resource-level authorization). Guards focus on identity verification and session management.

# Allowed Contexts
- Per-request user authentication and session resolution
- Token-based authentication (JWT, API tokens, OAuth)
- Multi-auth guard switching (admin, api, web)
- Custom authentication mechanisms (header-based, cookie-based)
- Guard composition for layered authentication strategies

# Forbidden Misuse
- As a general-purpose request filter (use middleware instead)
- As a place to put authorization logic (use policies or voters)
- Guards that modify request data instead of verifying identity
- Guards with hidden side effects or unbounded I/O

# Ecosystem References
- https://laravel.com/docs/authentication#adding-custom-guards
- https://symfony.com/doc/current/security/authenticator_manager.html
- https://docs.microsoft.com/en-us/aspnet/core/fundamentals/middleware/ (guard-like behavior)

# Allowed Patterns
- SessionGuard
- TokenGuard
- JwtGuard
- ApiKeyGuard

# Forbidden Patterns
- RequestGuard (too vague — which authentication mechanism?)
- SecurityGuard (redundant — guard already implies security)
- Guard/Guard (recursive, meaningless)
