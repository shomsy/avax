# Term
Seeder

# Classification
Database Data Population Component

# Purpose
Populates the database with initial, test, or reference data so that applications have predictable data for development, testing, or first-run scenarios.

# Why Allowed
Seeder is a well-established term in PHP frameworks, most prominently in Laravel but also in Doctrine fixtures, Symfony, and CakePHP. It describes a specific responsibility: inserting data into already-migrated schema. Seeders are distinct from migrations — migrations change structure, seeders insert data. They are essential for reproducible test environments, demo data, and reference tables (roles, permissions, countries, etc.).

# Allowed Contexts
- Populating reference/lookup tables (roles, statuses, countries, currencies)
- Inserting demo or sample data for development environments
- Creating test fixtures for integration and functional tests
- Initial admin or system user creation on first run
- Data required by default configuration or feature flags

# Forbidden Misuse
- As a replacement for migrations (seeders should not alter schema)
- As a production data import tool for real user data
- As a place to put business logic or data transformation rules
- Seeders that depend on runtime state or external APIs without guards

# Ecosystem References
- https://laravel.com/docs/seeding
- https://www.doctrine-project.org/projects/doctrine-data-fixtures/en/latest/
- https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html

# Allowed Patterns
- RoleAndPermissionSeeder
- CountryReferenceSeeder
- DemoOrderDataSeeder
- DefaultAdminUserSeeder

# Forbidden Patterns
- DataSeeder (too vague — what data?)
- ProductionDataImporter (seeders are not production ETL)
- EverythingSeeder (unbounded scope)
