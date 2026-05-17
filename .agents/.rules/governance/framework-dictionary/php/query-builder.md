# Term
QueryBuilder

# Classification
Data Access / Query Construction Component

# Purpose
Provides a fluent, type-safe API for constructing database queries programmatically, enabling dynamic query building with method chaining while abstracting SQL dialect differences.

# Why Allowed
QueryBuilder is a standard pattern in PHP frameworks and ORMs. Doctrine provides a QueryBuilder for DQL (Doctrine Query Language), Laravel offers a fluent QueryBuilder on top of Eloquent and the database layer, and Symfony uses it for repository queries. The pattern enables dynamic query construction without raw SQL strings, supports parameter binding for security, and allows method chaining for readable query composition. It is a well-defined contract with `select`, `where`, `join`, `orderBy`, and similar methods — not a generic query bucket.

# Allowed Contexts
- Dynamic query construction with method chaining
- Repository internal query building
- Filter, sort, and pagination query composition
- Cross-database compatibility abstraction
- Parameterized query construction for SQL injection prevention
- Complex join and subquery composition

# Forbidden Misuse
- As a replacement for simple find-by-id or basic CRUD operations (use repositories)
- As a place to put business logic that should be in domain services
- QueryBuilders that leak raw SQL into application code
- QueryBuilders that do not use parameter binding for user input

# Ecosystem References
- https://www.doctrine-project.org/projects/doctrine-orm/en/stable/reference/query-builder.html
- https://laravel.com/docs/queries
- https://symfony.com/doc/current/doctrine/repository.html

# Allowed Patterns
- OrderQueryBuilder
- ReportQueryBuilder
- UserSearchQueryBuilder
- ProductFilterQueryBuilder

# Forbidden Patterns
- GenericQueryBuilder (anti-pattern — should be scoped to a domain)
- DataQueryBuilder (too vague — what data?)
- QueryBuilder/QueryBuilder (recursive, meaningless)
