# DataModeling

Comprehensive array manipulation and data transfer component library.

## Purpose

This component provides ergonomic primitives for working with arrays and collections. It follows a screaming architecture where ownership is explicit and operations are organized by capability.

## Structure

```
DataModeling/
├── Arrhae.php              # Raw array facade entry
├── Collection.php          # Fluent state owner
├── CollectionInterface.php  # Contract boundary
├── Collections/           # Capability zones
│   ├── Internal/         # Shared machinery
│   ├── Create/          # Factory operations
│   ├── Read/            # Getter operations
│   ├── Write/          # Setter operations
│   ├── Transform/      # Map, filter, reduce
│   ├── Aggregate/      # Sum, avg, min, max
│   ├── Search/         # Contains, fuzzy match
│   ├── Order/         # Sort, reverse
│   ├── Convert/       # toJson, toArray
│   └── Strings/      # String operations
├── Foundation/         # Exceptions
└── docs/            # Documentation mirror
```

## Design Rules

- `Arrhae` and `Collection` are separate root public surface units
- No inheritance between `Arrhae` and `Collection`
- Shared logic lives in `Collections/*` capability zones
- `DTO` capability is Phase 2 (separate from collections)

## Quick Reference

```php
use Avax\DataModeling\Arrhae;

// Raw entry
$arrh = Arrhae::make(['name' => 'Alice']);

// Dot notation
$arrh->get('user.address.city');

// Immutable operations
$filtered = $arrh->filter(fn($item) => $item['active']);
```

## Ownership

- `Arrhae.php`: Raw array facade (Laravel-style entry)
- `Collection.php`: Fluent state owner
- `Collections/*`: Operation owner zones