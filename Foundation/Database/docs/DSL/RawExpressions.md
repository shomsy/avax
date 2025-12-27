# Raw Expressions

This document explains how to inject raw SQL fragments safely into your queries using the `Expression` value object.

---

## The Concept

The QueryBuilder is designed to protect you from SQL injection by automatically escaping values and quoting identifiers. However, sometimes you need to pass a specific SQL function (like `NOW()`) or a complex calculation that shouldn't be quoted like a string.

**Raw Expressions** act as an "escape hatch" — they tell the QueryBuilder: "Trust me, I know what I'm doing. Put this text directly into the SQL."

---

## Creating Raw Expressions

Use the `raw()` method on the QueryBuilder to create an `Expression` object.

```php
$builder->raw('NOW()');
$builder->raw('count(*) > 5');
$builder->raw('price * 1.20');
```

---

## Common Use Cases

### 1. Database Functions in WHERE clauses

```php
// WRONG: This searches for a string literal "NOW()"
$builder->where('created_at', '<', 'NOW()');
// SQL: WHERE created_at < 'NOW()'

// RIGHT: This uses the database function
$builder->where('created_at', '<', $builder->raw('NOW()'));
// SQL: WHERE created_at < NOW()
```

### 2. Complex Selections

```php
$builder->selectRaw('SUM(price * quantity) as total_revenue');
```

### 3. Incrementing Values

```php
$builder->update([
    'points' => $builder->raw('points + 10')
]);
```

### 4. Custom Ordering

```php
$builder->orderBy($builder->raw('FIELD(status, "active", "pending", "banned")'));
```

---

## Security Warning ⚠️

**NEVER** pass untrusted user input directly into a raw expression. This bypasses all security protections and opens you up to SQL injection.

```php
// ❌ DANGEROUS - Do NOT do this:
$userInput = $_GET['column'];
$builder->raw("DATEDIFF($userInput, NOW())");

// ✅ SAFE - Use bindings inside other methods, or validate strictly
$builder->selectRaw('DATEDIFF(?, NOW())', [$userInput]);
```

The `selectRaw`, `whereRaw` (via closures), and `havingRaw` methods often support binding arrays to safely handle parameters.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Expression Class Reference](QueryStates.md#bindingbag)
