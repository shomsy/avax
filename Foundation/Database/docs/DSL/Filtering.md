# Filtering (WHERE Clauses)

The `HasConditions` trait provides all WHERE clause methods for the QueryBuilder. This document explains each filtering method in human-readable terms.

---

## Table of Contents

- [where](#where)
- [orWhere](#orwhere)
- [whereIn](#wherein)
- [orWhereIn](#orwherein)
- [whereBetween](#wherebetween)
- [whereNested](#wherenested)

---

## where

**Add a basic filtering condition to your query.**

This is the workhorse of filtering. It adds a `WHERE` clause that compares a column to a value using an operator (=, >, <, LIKE, etc.).

Think of it as telling a librarian: "I only want books where the **author** is **'Stephen King'**."

The method is smart about arguments:

- 2 arguments: assumes `=` operator → `where('status', 'active')` becomes `WHERE status = 'active'`
- 3 arguments: explicit operator → `where('price', '>', 100)` becomes `WHERE price > 100`

The builder is **immutable** — each call returns a fresh copy with the new condition added.

```php
// Equality (shorthand)
$builder->from('users')->where('status', 'active');
// SQL: WHERE status = ?

// Comparison operators
$builder->from('products')->where('price', '>', 100);
// SQL: WHERE price > ?

// LIKE for pattern matching
$builder->from('users')->where('email', 'LIKE', '%@gmail.com');
// SQL: WHERE email LIKE ?

// Chaining multiple conditions (AND logic)
$builder->from('orders')
    ->where('status', 'shipped')
    ->where('total', '>', 50);
// SQL: WHERE status = ? AND total > ?
```

---

## orWhere

**Add an alternative condition with OR logic.**

While `where()` chains conditions with AND, `orWhere()` uses OR. This means "match if THIS condition is true OR if the PREVIOUS conditions are true."

You're at a restaurant asking: "Bring me a dish that's vegetarian **OR** costs less than $10."

```php
$builder->from('products')
    ->where('category', 'electronics')
    ->orWhere('on_sale', true);
// SQL: WHERE category = ? OR on_sale = ?

// Multiple OR conditions
$builder->from('users')
    ->where('role', 'admin')
    ->orWhere('role', 'moderator')
    ->orWhere('role', 'superuser');
// SQL: WHERE role = ? OR role = ? OR role = ?
```

---

## whereIn

**Filter records where a column matches any value in a list.**

The SQL `IN` operator — checks if a value exists in a provided set. You're checking if someone's name is on the guest list.

Much more efficient than chaining multiple `orWhere()` calls for the same column.

```php
$builder->from('users')->whereIn('status', ['active', 'pending', 'trial']);
// SQL: WHERE status IN (?, ?, ?)

$builder->from('orders')->whereIn('customer_id', [101, 202, 303]);
// SQL: WHERE customer_id IN (?, ?, ?)
```

For the opposite (NOT IN), the underlying method supports a `$not` parameter:

```php
// Values to EXCLUDE
$builder->from('users')->whereIn('status', ['banned', 'deleted'], not: true);
// SQL: WHERE status NOT IN (?, ?)
```

---

## orWhereIn

**Same as whereIn, but with OR logic.**

Adds an alternative membership filter. "Match if this column is in this list **OR** if the previous conditions match."

```php
$builder->from('products')
    ->where('featured', true)
    ->orWhereIn('category_id', [5, 10, 15]);
// SQL: WHERE featured = ? OR category_id IN (?, ?, ?)
```

---

## whereBetween

**Filter records where a column falls within a range.**

The SQL `BETWEEN` operator — checks if a value is between two bounds (inclusive on both ends).

You're asking: "Show me all orders placed between January 1st and January 31st."

Requires **exactly 2 values** — the lower and upper bounds.

```php
$builder->from('orders')->whereBetween('total', [100, 500]);
// SQL: WHERE total BETWEEN ? AND ?

$builder->from('events')->whereBetween('date', ['2024-01-01', '2024-12-31']);
// SQL: WHERE date BETWEEN ? AND ?

// Combined with other conditions
$builder->from('products')
    ->where('active', true)
    ->whereBetween('price', [10, 100]);
// SQL: WHERE active = ? AND price BETWEEN ? AND ?
```

For excluding a range (NOT BETWEEN):

```php
$builder->from('products')->whereBetween('price', [0, 10], not: true);
// SQL: WHERE price NOT BETWEEN ? AND ?
```

---

## whereNested

**Group multiple conditions inside parentheses.**

This is how you control order of operations in complex queries. Without nesting, `A AND B OR C` might not mean what you think. With nesting, you can explicitly say `A AND (B OR C)`.

You're telling the librarian: "I want books that are (either mysteries OR thrillers) AND published after 2020."

Pass a closure that receives a fresh query builder — any conditions you add inside become grouped.

```php
// Without nesting (ambiguous):
// WHERE role = 'user' AND status = 'active' OR status = 'trial'
// Does this mean (role = user AND status = active) OR status = trial?
// Or role = user AND (status = active OR status = trial)?

// With nesting (explicit):
$builder->from('users')
    ->where('role', 'user')
    ->where(function ($query) {
        $query->where('status', 'active')
              ->orWhere('status', 'trial');
    });
// SQL: WHERE role = ? AND (status = ? OR status = ?)

// Complex example with multiple nested groups
$builder->from('products')
    ->where(function ($q) {
        $q->where('category', 'electronics')
          ->where('price', '>', 100);
    })
    ->orWhere(function ($q) {
        $q->where('category', 'clothing')
          ->where('on_sale', true);
    });
// SQL: WHERE (category = ? AND price > ?) OR (category = ? AND on_sale = ?)
```

---

## Best Practices

1. **Always use parameter binding** — The QueryBuilder automatically binds values as parameters to prevent SQL injection. Never concatenate user input directly.

2. **Use `whereIn` for lists** — Instead of chaining 10 `orWhere()` calls, use `whereIn()` with an array.

3. **Use nesting for complex logic** — When you have mixed AND/OR conditions, use closures to make the logic explicit.

4. **Remember immutability** — Each method returns a NEW builder instance. The original is unchanged.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Query States](QueryStates.md)
- [Mutations (INSERT/UPDATE/DELETE)](Mutations.md)
