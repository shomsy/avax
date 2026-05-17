# Term
Domain

# Classification
DDD Bounded Context

# Purpose
Represents a bounded context or domain layer in Domain-Driven Design architecture. A domain encapsulates a specific area of business knowledge, containing the models, rules, language, and behavior that define how that area of the business operates.

# Why Allowed
Domain is a core DDD (Domain-Driven Design) concept originating from Eric Evans's seminal work. It represents a specific area of business knowledge with its own ubiquitous language, models, and rules. In PHP, DDD implementations use domain layers to separate business logic from infrastructure concerns. Symfony DDD bundles and enterprise architecture patterns organize code around domain boundaries rather than technical layers. A domain has clear characteristics: it owns its own language (ubiquitous language), it defines its own models and invariants, it encapsulates business rules, it has explicit boundaries with other domains, and it is not a technical layer but a business concept. Domains communicate through well-defined interfaces — events, contracts, or published APIs — rather than direct coupling. The term "Domain" is not a generic bucket — it represents a deliberate architectural decision to organize around business capabilities rather than technical concerns.

# Allowed Contexts
- DDD bounded contexts with clear business boundaries
- Domain model layer containing aggregates, entities, and value objects
- Business logic encapsulation that belongs to a specific domain area
- Aggregate roots and domain services within a bounded context
- Domain events that represent significant business occurrences
- Domain repositories that abstract persistence for aggregate roots

# Forbidden Misuse
- As a generic business logic dumping ground for anything vaguely business-related
- As a catch-all for code that does not fit in technical layers
- Creating a Domain/ folder that contains everything without bounded context boundaries
- Using "Domain" to describe infrastructure concerns (database, caching, HTTP)
- Mixing multiple unrelated business areas in a single domain without clear boundaries

# Ecosystem References
- https://domainlanguage.com/ddd/
- https://www.amazon.com/Domain-Driven-Design-Tackling-Complexity-Software/dp/0321125215
- https://symfony.com/doc/current/best_practices/domain-entities.html
- https://github.com/ddd-by-examples

# Allowed Patterns
- OrderDomain
- UserManagementDomain
- BillingDomain
- InventoryDomain
- PaymentProcessingDomain

# Forbidden Patterns
- Domain/ (as folder for everything business-related without bounded context)
- DomainManager (managers are forbidden; domains are bounded contexts, not managers)
- CommonDomain (too vague — which domain? what is "common"?)
- Domain/Domain (recursive, meaningless)
