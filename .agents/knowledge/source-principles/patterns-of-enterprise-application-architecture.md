# Patterns of Enterprise Application Architecture (PoEAA)

This document operationalizes PoEAA patterns for the AvaX framework to control agent implementation and reviewer decisions.

---

## 1. Transaction Boundary

### Operational Rules
- Transaction boundaries must live at the Application Service layer (Service Layer), not within Domain Models, Repositories, or Data Mappers.
- Nested transaction calls must be explicitly avoided. Use a single coordinator to open, commit, or rollback.

### Traceability Mapping
- **Rule**: All state mutations affecting multiple entities must be wrapped in a transaction managed by the Service Layer.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Transaction Boundary`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Does the Repository attempt to commit a transaction, or is it properly delegated to the Service Layer / Unit of Work?
- **Failure Mode**: Database deadlock or partial commits when an exception occurs midway through a service method.
- **Stop Condition**: Transaction `commit()` or `beginTransaction()` called inside a repository, entity, or domain model.
- **Severity**: BLOCKER (risks database corruption).
- **Automation Possibility**: AST analysis checking for transaction calls outside class files ending in `Service` or `Controller`.

---

## 2. Service Layer Abuse

### Operational Rules
- Services must only coordinate flows. They must not contain domain logic, complex state mutations, or business rules.
- Services that act as mere "pass-throughs" (simply forwarding requests to repositories without transaction coordination, security, or validation) are forbidden.

### Traceability Mapping
- **Rule**: Service Layer must remain thin, delegating business rule execution to Domain Entities/Value Objects.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Service Layer Abuse`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Is domain validation logic implemented in the Service class instead of the Entity/Value Object?
- **Failure Mode**: Anemic Domain Model where entities are simple data bags and services contain duplicate, copy-pasted validation checks.
- **Stop Condition**: Service class containing `if` statements validating business invariant conditions (e.g., balance > limit).
- **Severity**: HIGH.
- **Automation Possibility**: High cyclomatic complexity in Service classes or presence of conditional logic checking model properties.

---

## 3. Repository Misuse

### Operational Rules
- Repositories must behave like in-memory collections of aggregate roots. They must not execute business logic or return raw database connections.
- Querying repositories must not return arrays of raw rows; they must return Domain Entities or typed Value Objects.

### Traceability Mapping
- **Rule**: Repositories must hide persistence details and return complete aggregates.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Repository Misuse`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Does the repository return un-mapped PDO query results or raw arrays to the controller?
- **Failure Mode**: Leakage of SQL schemas into controllers and views, bypass of domain invariant enforcement.
- **Stop Condition**: Repository methods returning `stdClass`, `array` containing raw DB column keys, or executing raw SQL string concatenation.
- **Severity**: HIGH.
- **Automation Possibility**: Scanning repository return types or checking for lack of mapper invocation before returning.

---

## 4. Mapper Discipline

### Operational Rules
- Data Mappers must isolate Domain Entities from DB schemas.
- Entities must not have properties or structures that mirror DB column names simply to ease database mapping (no direct ActiveRecord properties in Domain objects).

### Traceability Mapping
- **Rule**: Separate entity definition from mapping/persistence layer schema.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Data Mapping Strategy`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Are entity properties public, or annotated with ORM schemas that force structural coupling to SQL?
- **Failure Mode**: Database schema refactorings breaking domain API signatures, causing cascading changes.
- **Stop Condition**: Entity properties containing ORM annotations that directly map to database columns, with public getters/setters for every column.
- **Severity**: HIGH.
- **Automation Possibility**: Checking for DB-specific attributes/annotations on Domain Entity classes.

---

## 5. Identity Map

### Operational Rules
- Every active database transaction session must use an Identity Map to ensure that an entity is loaded only once per request scope.

### Traceability Mapping
- **Rule**: Prevent duplicate object instantiation for the same database row in a single thread/request.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `State Ownership`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: If the same aggregate ID is queried twice in the same request, do we return the identical object instance?
- **Failure Mode**: "Lost updates" or stale state when two different object instances representing the same row are modified and saved sequentially.
- **Stop Condition**: Repository instantiated without access to a shared identity map/registry.
- **Severity**: HIGH.
- **Automation Possibility**: Verification of identity map registry lookups in repository retrieve methods.

---

## 6. Unit of Work

### Operational Rules
- Use a Unit of Work to keep track of all modified, created, and deleted entities during a transaction, committing all changes in a single operation.

### Traceability Mapping
- **Rule**: Individual repositories must not invoke `save()` or `update()` calls inside business loops. The Unit of Work must flush all dirty entities at transaction commit.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Persistence Boundary`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Does the code call `$repo->save()` multiple times in a loop, or is it coordinated via Unit of Work?
- **Failure Mode**: High database round-trip latency and partial updates on multi-step flows.
- **Stop Condition**: Immediate write/persist calls inside domain flows instead of registering changes with Unit of Work.
- **Severity**: HIGH.
- **Automation Possibility**: Warning or error on direct Repository mutation calls outside transaction commit blocks.

---

## 7. Session State

### Operational Rules
- AvaX is run in stateless environment adapters (e.g. workers). Session state must not be stored in static variables or local file-based session stores.
- Client state must be explicitly serialized and carried via session tokens or stateless JWTs.

### Traceability Mapping
- **Rule**: No stateful session variables or PHP superglobal `$_SESSION` usage.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `State Ownership`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Does the component rely on local PHP session state?
- **Failure Mode**: Request cross-talk, memory leaks, and routing failure in long-lived concurrent workers.
- **Stop Condition**: Usage of `$_SESSION` or global session storage helpers.
- **Severity**: BLOCKER.
- **Automation Possibility**: Scanning for `$_SESSION` or native session functions.

---

## 8. Persistence Ignorance vs Pragmatic Persistence

### Operational Rules
- Strive for persistence ignorance in domain objects. Domain models should have no knowledge of databases, SQL, filesystems, or networks.
- When strict persistence ignorance is impractical (e.g., lazy loading collection proxies), use clean interface abstractions at the boundary.

### Traceability Mapping
- **Rule**: Domain classes must not import database namespace classes or execute persistence libraries directly.
- **Evidence**: `enterprise-application-boundary.md` -> Heading: `Persistence Ignorance vs Pragmatic Persistence`
- **Checker**: `check-enterprise-application-boundaries.php`
- **Review Question**: Does the domain package import SQL connection adapters or file utilities?
- **Failure Mode**: Inability to unit test the domain without database mock/setup, tight coupling to database driver versions.
- **Stop Condition**: Domain layer classes importing PDO, DB connections, or DB driver namespaces.
- **Severity**: HIGH.
- **Automation Possibility**: Imports validation (coupling/boundary check) in Domain namespace files.
