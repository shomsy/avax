# Term
ServiceProvider

# Classification
Framework Lifecycle Component

# Purpose
Registers, configures, and boots framework services into the application container so they are available for dependency injection and runtime use.

# Why Allowed
ServiceProvider is a foundational term in the PHP framework ecosystem. Laravel popularized it as the primary assembly mechanism — every package, component, or first-party feature registers itself through a service provider. Symfony achieves the same via bundles and extension classes, but Laravel's `ServiceProvider` is the canonical naming convention. Phalcon, Yii, and Slim also use provider-like registration patterns. The term describes a concrete lifecycle responsibility (register + boot), not a generic dumping ground.

# Allowed Contexts
- Framework bootstrapping and assembly
- Package/component registration into a DI container
- Deferred or lazy service registration
- Configuration binding and convention setup
- Event and middleware registration during boot phase

# Forbidden Misuse
- As a general-purpose "service holder" that accumulates unrelated logic
- As a substitute for proper class design (putting business logic inside register/boot methods)
- As a catch-all for code that does not fit elsewhere
- Creating providers that do not actually register or boot anything

# Ecosystem References
- https://laravel.com/docs/providers
- https://symfony.com/doc/current/bundles.html
- https://www.phalcon.io/docs

# Allowed Patterns
- CacheServiceProvider
- RouteServiceProvider
- EventServiceProvider
- AuthServiceProvider
- RepositoryServiceProvider

# Forbidden Patterns
- Services/ServiceProvider (generic bucket)
- Managers/ServiceProvider (concept dumping ground)
- CommonServiceProvider (unclear responsibility)
