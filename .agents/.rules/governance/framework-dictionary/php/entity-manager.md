# Term
EntityManager

# Classification
Data Access / ORM Component

# Purpose
Manages the lifecycle, persistence, and retrieval of entity objects, acting as the primary interface between domain objects and the database in an ORM context.

# Why Allowed
EntityManager is the central class in Doctrine ORM, the most widely used PHP object-relational mapper. It is responsible for persisting, removing, finding, and tracking entities within a unit of work. Doctrine's EntityManager implements the Unit of Work pattern, providing transactional consistency, change tracking, and lazy loading. It is also referenced in Hibernate (Java) and other ORM ecosystems. The term describes a specific ORM responsibility — managing entity state transitions — not a generic "entity manager" bucket for unrelated code.

# Allowed Contexts
- Doctrine ORM entity persistence and retrieval
- Unit of work and transaction management
- Entity lifecycle management (persist, merge, remove, refresh)
- DQL and native query execution through the ORM
- Entity manager proxies for lazy loading
- Second-level cache integration with entity manager

# Forbidden Misuse
- As a repository replacement (entities should be queried through repositories)
- As a place to put business logic that belongs in domain services
- As a generic data access class unrelated to ORM entity management
- EntityManagers that bypass the unit of work for raw SQL operations

# Ecosystem References
- https://www.doctrine-project.org/projects/doctrine-orm/en/stable/reference/working-with-objects.html
- https://www.doctrine-project.org/projects/doctrine-orm/en/stable/reference/unitofwork.html
- https://hibernate.org/ (Java EntityManager reference)

# Allowed Patterns
- EntityManager (Doctrine's canonical class)
- EntityManagerDecorator (custom decoration)
- EntityManagerProxy (lazy-loading proxy)

# Forbidden Patterns
- DataEntityManager (too vague — which entity set?)
- UserManager (should be UserRepository instead)
- EntityManager/EntityManager (recursive, meaningless)
