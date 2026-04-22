# Arrhae

Raw array facade for simple array manipulation.

## Purpose

`Arrhae` is the minimal entry point for working with arrays. It provides Laravel-style ergonomics without the full fluent API.

## When to Use Arrhae

- Quick array operations
- Simple data transformation
- Minimal footprint needed

## When to Use Collection

- Fluent chainable operations
- Complex transformations
- Full API surface needed

## Quick Examples

```php
use Avax\DataModeling\Arrhae;

// Create from array
$arrh = Arrhae::make(['name' => 'Alice', 'age' => 30]);

// Get with dot notation
$arrh->get('user.address.city');

// Immutable operations
$filtered = $arrh->filter(fn($item) => $item['active']);

// Bridge to Collection
$collection = $arrh->collect();
```

## Public API

| Method | Description |
|--------|-------------|
| `make()` | Static factory |
| `wrap()` | Wrap single value |
| `get()` | Get value by key |
| `has()` | Check key exists |
| `set()` | Set value |
| `forget()` | Remove value |
| `all()` | Get all items |

## Design Rules

- Small public surface only
- No trait-sprawl
- Delegates to capability owners