# Term
Serializer

# Classification
Data Transformation Component

# Purpose
Converts complex objects, arrays, or data structures into a transportable format (JSON, XML, YAML, binary) for storage, transmission, or interchange, and optionally reconstructs objects from that format.

# Why Allowed
Serializer is a standard term across PHP frameworks and libraries. Symfony provides a Serializer component with normalization, denormalization, and encoding capabilities. Laravel uses serialization for queue payloads, session data, and API responses. JMS Serializer, Doctrine, and many API platforms rely on serializers to transform objects into wire formats. A serializer is not a generic transformer — it has a clear contract: object-to-string (serialize) and string-to-object (deserialize), with support for type mapping, circular reference handling, and format negotiation.

# Allowed Contexts
- API response encoding (object to JSON/XML)
- Queue payload serialization for job transport
- Session data serialization and restoration
- Cache value serialization
- Event/message payload encoding for distributed systems
- Configuration file serialization/deserialization

# Forbidden Misuse
- As a general data transformation utility unrelated to format conversion
- As a place to put business logic for data manipulation
- Serializers that leak internal state or secrets into output
- Serializers that do not support round-trip (serialize then deserialize loses data)

# Ecosystem References
- https://symfony.com/doc/current/components/serializer.html
- https://laravel.com/docs/queues#job-serialization
- https://github.com/schmittjoh/serializer (JMS Serializer)

# Allowed Patterns
- JsonApiSerializer
- QueuePayloadSerializer
- EventEnvelopeSerializer
- ConfigSerializer

# Forbidden Patterns
- DataSerializer (too vague — what data, what format?)
- GenericSerializer (anti-pattern — serializers should be format-aware)
- Serializer/Serializer (recursive, meaningless)
