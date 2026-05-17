# Term
Facade

# Classification
Static Access Proxy Pattern

# Purpose
Provides a static-like interface to an underlying class or service resolved from the container, enabling concise, expressive syntax while maintaining testability through container-backed resolution.

# Why Allowed
Facade is a prominent pattern in Laravel, where it provides static-access proxies to container-resolved services (e.g., `Cache::get()`, `Mail::send()`). While it shares a name with the GoF Facade structural pattern, the Laravel Facade is distinct — it is a static proxy, not a simplified interface to a subsystem. Symfony avoids facades in favor of explicit dependency injection, but the Laravel facade is a well-documented, widely-used ecosystem pattern. It provides developer experience benefits while remaining testable via mocking and container swaps.

# Allowed Contexts
- Static-access proxies to container-resolved services (Laravel style)
- Simplified interfaces to complex subsystems (GoF Facade pattern)
- Developer experience improvements for frequently-used services
- Testing-friendly static access (mockable via container)
- Public API entrypoints that hide complex internal wiring

# Forbidden Misuse
- As a substitute for proper dependency injection in testable code
- As a way to bypass container resolution and create hidden dependencies
- Facades that do not proxy to a real underlying service
- Facades that add business logic instead of delegating to the real service

# Ecosystem References
- https://laravel.com/docs/facades
- https://refactoring.guru/design-patterns/facade
- https://symfony.com/doc/current/service_container.html (DI alternative)

# Allowed Patterns
- Cache (proxy to cache service)
- Mail (proxy to mailer service)
- Log (proxy to logger service)
- ConfigSubsystemFacade (GoF-style facade to a complex subsystem)

# Forbidden Patterns
- ServicesFacade (too vague — which service?)
- Facade/Facade (recursive, meaningless)
- HelperFacade (helpers are not facades)
