# Term
DTO

# Classification
Data Transfer Object

# Purpose
Carries data between processes, layers, or boundaries without behavior, providing a typed, immutable, and validated container for structured data exchange.

# Why Allowed
DTO (Data Transfer Object) is a well-established pattern in PHP, especially in enterprise applications, API development, and DDD-influenced architectures. PHP 8's constructor promotion, readonly properties, and typed declarations make DTOs particularly elegant. DTOs are used in API request/response objects, message payloads, form data carriers, and inter-service communication. They differ from entities (which have identity and lifecycle) and value objects (which emphasize equality by value, not just data carriage). Frameworks like Symfony, Laravel, and API Platform all embrace DTOs for clean data boundaries.

# Allowed Contexts
- API request and response data carriers
- Form input data objects
- Inter-service or inter-process message payloads
- Command bus command objects (CQRS)
- Event payload carriers
- Data validation boundaries (typed input before domain logic)

# Forbidden Misuse
- As a substitute for domain entities with identity and lifecycle
- As a place to put business logic (DTOs should be anemic)
- As a generic "data bag" without type safety or validation
- DTOs that are mutable after construction when immutability is expected

# Ecosystem References
- https://symfony.com/doc/current/messenger.html#creating-a-message-handler
- https://laravel.com/docs/requests (form request DTOs)
- https://api-platform.com/docs/core/dto/

# Allowed Patterns
- CreateUserDto
- OrderResponseDto
- PaymentIntentDto
- SearchFilterDto

# Forbidden Patterns
- DataDto (redundant — DTO already implies data)
- GenericDto (too vague — what data?)
- Dto/Dto (recursive, meaningless)
