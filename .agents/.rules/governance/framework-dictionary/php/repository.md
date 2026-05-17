# Term
Repository

# Classification
Data Access Pattern

# Purpose
Abstracts data persistence and retrieval behind a collection-like interface, decoupling business logic from the underlying storage mechanism (database, API, cache, file).

# Why Allowed
Repository is one of the most influential patterns in PHP, popularized by Domain-Driven Design and implemented in Doctrine ORM as `EntityManager` and repository classes. Laravel, Symfony, Yii, and many enterprise PHP applications use repositories to isolate data access from domain services. The pattern has a clear contract: it provides find, save, delete, and query operations against an aggregate or entity type. It is not a generic data bucket — it represents a specific architectural boundary between domain logic and persistence.

# Allowed Contexts
- Data access abstraction for a specific aggregate or entity type
- Encapsulating complex queries behind simple method names
- Switching persistence backends without changing domain logic
- Unit testing domain logic with in-memory or fake repositories
- Read/write separation (CQRS read repositories vs write repositories)

# Forbidden Misuse
- As a dumping ground for all database queries regardless of entity
- As a thin wrapper around raw SQL with no abstraction benefit
- As a place to put business logic that belongs in domain services or flows
- Repositories that expose raw query builders or leak persistence details

# Ecosystem References
- https://www.doctrine-project.org/projects/doctrine-orm/en/stable/reference/working-with-objects.html
- https://laravel.com/docs/eloquent
- https://martinfowler.com/eaaCatalog/repository.html

# Allowed Patterns
- UserRepository
- OrderRepository
- ProductCatalogRepository
- CachedSessionRepository

# Forbidden Patterns
- DataRepository (too vague — what data?)
- GenericRepository (anti-pattern — defeats purpose of typed repositories)
- Repository/Repository (recursive, meaningless name)
