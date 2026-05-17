# Term
Normalizer

# Classification
Data Transformation Component

# Purpose
Converts complex objects or structured data into a simplified, flat, or standardized array representation suitable for encoding, and optionally reconstructs objects from that representation (denormalization).

# Why Allowed
Normalizer is a core concept in the Symfony Serializer component and is widely used in PHP data transformation pipelines. Unlike a serializer (which handles the full object-to-string conversion), a normalizer focuses on the structural transformation: object-to-array (normalize) and array-to-object (denormalize). This separation of concerns allows format-specific encoders (JSON, XML) to work with a common normalized representation. Doctrine, API Platform, and many validation libraries use normalizers to bridge between domain objects and transport formats.

# Allowed Contexts
- Object-to-array transformation for encoding pipelines
- Denormalization of arrays into typed objects
- API Platform data transformation
- Form data normalization and denormalization
- Validation input preparation
- Type coercion and property mapping during transformation

# Forbidden Misuse
- As a general data mapper between domain models
- As a place to put business logic or computed properties
- Normalizers that mutate objects instead of producing new representations
- Normalizers that silently drop required fields without validation

# Ecosystem References
- https://symfony.com/doc/current/components/serializer.html#normalizers
- https://api-platform.com/docs/core/serialization/
- https://laravel.com/docs/eloquent-resources (similar concept)

# Allowed Patterns
- DateTimeNormalizer
- ObjectNormalizer
- GetSetMethodNormalizer
- ArrayDenormalizer

# Forbidden Patterns
- DataNormalizer (too vague — what data?)
- EverythingNormalizer (unbounded scope)
- Normalizer/Normalizer (recursive, meaningless)
