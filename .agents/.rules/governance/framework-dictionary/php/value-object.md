# Term
ValueObject

# Classification
Domain Building Block

# Purpose
Represents a concept whose equality is determined by its attribute values rather than identity, providing immutable, self-validating domain primitives that encapsulate both data and behavior.

# Why Allowed
ValueObject is a foundational Domain-Driven Design pattern, widely used in PHP enterprise applications. Doctrine ORM supports embedded value objects. Money libraries (e.g., `moneyphp/money`) implement value objects for currency and amount. Email, Address, UserId, and similar domain primitives are classic value objects. Unlike DTOs (which are passive data carriers), value objects encapsulate domain behavior: validation on construction, computed properties, and equality comparison by value. They are immutable by design — any modification produces a new instance rather than mutating state.

# Allowed Contexts
- Domain primitives (Email, Money, UserId, Address, DateRange)
- Embedded value objects in Doctrine entities
- Typed identifiers that carry domain meaning
- Currency, measurement units, and quantity representations
- Any concept where equality is structural, not identity-based

# Forbidden Misuse
- As a mutable data container (value objects must be immutable)
- As a substitute for entities when identity and lifecycle matter
- As a generic typed wrapper without domain behavior or validation
- Value objects that expose setters or allow state mutation

# Ecosystem References
- https://www.doctrine-project.org/projects/doctrine-orm/en/stable/tutorials/embeddables.html
- https://github.com/moneyphp/money
- https://martinfowler.com/bliki/ValueObject.html

# Allowed Patterns
- Email
- Money
- UserId
- DateRange
- Address
- CurrencyCode

# Forbidden Patterns
- ValueObject (too generic — should be named after the concept)
- GenericValueObject (anti-pattern)
- DataValueObject (redundant, vague)
