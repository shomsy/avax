# Filtering (WHERE clauses)

## What it does

Filtering methods (`where()`, `orWhere()`, `whereIn()`, `whereBetween()`) add logical constraints to your query to restrict the result set.

## Why it exists

- **DSL for Logic**: Provides a readable way to build complex SQL conditions (`AND`, `OR`, nesting).
- **Security**: Automatically uses prepared statements (bindings) for all values, preventing SQL injection.
- **Normalization**: Handles different data types (arrays for `IN`, scalars for `=`) and translates them into safe SQL.

## When to use

- `where($col, $val)`: Simple equality.
- `where($col, $operator, $val)`: Comparisons like `>`, `<`, `LIKE`.
- `where(fn($query) => ...)`: Nested logical groups (parentheses).

## When *not* to use

- Avoid using `whereRaw()` (if available) unless absolutely necessary, as it bypasses standard safety checks. Use [Raw Expressions](RawExpressions.md) instead.
- Don't use `where()` for column-to-column comparisons; use `whereColumn()` (if implemented) or raw fragments.

## Examples

```php
// Basic chain (AND)
$builder->where('status', 'active')
        ->where('votes', '>', 100);

// Nested logic: WHERE status = 'active' AND (votes > 100 OR type = 'admin')
$builder->where('status', 'active')
        ->where(fn($q) => $q->where('votes', '>', 100)->orWhere('type', 'admin'));
```

## Common pitfalls

- **Logical precedence**: Mixing `AND` and `OR` without closures for nesting often leads to unexpected results because SQL evaluates `AND` before `OR`. Always use closures for clear grouping.
- **Binding limits**: Extremely large `whereIn()` arrays (thousands of items) can exceed database packet limits or significantly slow down compilation.
