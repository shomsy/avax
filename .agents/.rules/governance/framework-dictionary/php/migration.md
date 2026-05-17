# Term
Migration

# Classification
Database Schema Versioning Component

# Purpose
Defines a reversible, versioned change to the database schema (tables, columns, indexes, constraints) so that schema evolution is tracked, repeatable, and deployable across environments.

# Why Allowed
Migration is a standard term in PHP frameworks and ORMs. Laravel, Doctrine, Symfony, Phinx, CakePHP, and Yii all use migrations as the primary mechanism for schema version control. A migration encodes `up` (apply) and `down` (rollback) operations, enabling teams to move databases forward and backward safely. The term describes a concrete, bounded responsibility — one schema change per class — not a generic utility.

# Allowed Contexts
- Creating, altering, or dropping database tables and columns
- Adding or removing indexes, foreign keys, and constraints
- Seeding initial schema-required data that is structural (not business data)
- Rolling back schema changes in development or controlled deployments
- Schema version tracking and migration ordering

# Forbidden Misuse
- As a place to put data manipulation logic that belongs in seeders or commands
- As a mechanism for runtime schema changes (schema should be stable at runtime)
- Migrations that are not reversible (missing down method)
- Migrations that depend on application models instead of raw schema operations

# Ecosystem References
- https://laravel.com/docs/migrations
- https://www.doctrine-project.org/projects/doctrine-migrations/en/latest/
- https://phinx.org/

# Allowed Patterns
- CreateUsersTable
- AddStatusColumnToOrders
- DropDeprecatedLogsTable
- RenameUserIdToAuthorIdInPosts

# Forbidden Patterns
- MigrateUserData (data manipulation belongs in seeders/commands)
- FixEverythingMigration (unclear, unbounded scope)
- RuntimeMigration (schema changes at runtime are dangerous)
