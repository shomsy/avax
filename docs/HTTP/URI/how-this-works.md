---
title: URI Component
description: Immutable URI parsing, state management, and rendering
triggers:
  - Parsing external URI strings into structured parts
  - Building URIs from components with validation
  - Immutable modifications to URI parts
  - PSR-7 compliant URI interface
owners:
  - Uri (canonical model)
  - ParseUriString (boundary parser)
  - Parts/* (immutable value objects)
---

# URI Component

The URI component provides a clean, immutable API for parsing, manipulating, and rendering URIs according to RFC 3986,
with full PSR-7 compatibility.

## Architecture

```mermaid
graph TD
    A[External String] --> B[ParseUriString]
    B --> C[Uri Model]
    C --> D[Scheme]
    C --> E[Authority]
    C --> F[Path]
    C --> G[Query]
    C --> H[Fragment]

    E --> I[Host]
    E --> J[Port]
    E --> K[UserInfo]

    C --> L[PSR-7 Interface]
    L --> M[with* Methods]
    M --> N[New Uri Instance]
```

## Key Design Decisions

- **Single Source of Truth**: All URI state is held in immutable `Uri` object with typed parts
- **Boundary Parsing**: External strings are parsed once at `ParseUriString`, never re-parsed
- **Immutable Operations**: All modifications return new instances, no in-place changes
- **PSR-7 Bridge**: External compatibility without internal complexity
- **Validation at Construction**: Invalid URIs fail fast with clear errors

## Usage Examples

### Basic Parsing

```php
$uri = Uri::fromString('https://user:pass@example.com:8080/path?query=value#fragment');

echo $uri->getScheme();   // 'https'
echo $uri->getHost();     // 'example.com'
echo $uri->getPath();     // '/path'
```

### Immutable Modifications

```php
$uri = Uri::fromString('https://example.com/old');
$newUri = $uri->withPath('/new')->withQuery('updated=true');

echo (string) $newUri; // https://example.com/new?updated=true
```

### Query Manipulation

```php
$uri = Uri::fromString('https://example.com?foo=bar');
$query = new Query('foo=bar&baz=qux');
$newQuery = $query->add('foo', 'new')->remove('baz');

$newUri = $uri->withQuery((string) $newQuery);
```