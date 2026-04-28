---
title: URI Parts
description: Immutable value objects for URI components
triggers:
  - Host validation and normalization
  - Path encoding and normalization
  - Query parameter management
  - Authority construction
owners:
  - Host (domain/IP validation)
  - Path (segment encoding)
  - Query (parameter storage)
  - Authority (user, host, port)
  - Scheme (protocol validation)
  - Port (default port handling)
  - UserInfo (credentials)
  - Fragment (anchor encoding)
---

# URI Parts

Each URI component is represented by an immutable value object that handles validation, normalization, and encoding.

## Design Principles

- **Immutable**: All objects are readonly, no setters
- **Validated**: Construction fails for invalid values
- **Normalized**: Consistent internal representation
- **Encoded**: Safe for URI contexts
- **Typed**: Strong typing prevents misuse

## Component Details

### Host

- Validates domain names and IP addresses
- Performs IDN conversion
- Case-insensitive normalization

### Path

- Encodes path segments
- Normalizes `.` and `..`
- Ensures leading `/`

### Query

- Immutable parameter map
- Supports repeated parameters
- RFC 3986 encoding

### Authority

- Combines user info, host, and port
- Handles default port suppression
- Validates component relationships