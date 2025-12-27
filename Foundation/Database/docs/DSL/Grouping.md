# Grouping (GROUP BY & HAVING)

The `HasGroups` trait provides methods for aggregating records into groups and filtering those groups.

---

## Table of Contents

- [groupBy](#groupby)
- [having](#having)

---

## groupBy

**Group records with identical values.**

Consolidates rows based on one or more columns, typically used with aggregate functions (`count`, `sum`, `avg`, etc.).

Think of sorting a deck of cards into piles by suit. You end up with 4 piles (Hearts, Diamonds, Clubs, Spades).

```php
// Group by category
$builder->from('products')
    ->selectRaw('category_id, COUNT(*) as total_products')
    ->groupBy('category_id')
    ->get();
// SQL: SELECT category_id, COUNT(*) as total_products FROM products GROUP BY category_id

// Group by multiple columns
$builder->from('orders')
    ->groupBy('year', 'month')
    ->get();
```

---

## having

**Filter groups using aggregate functions.**

Similar to `where()`, but works on **groups** instead of individual rows. The SQL `HAVING` clause runs *after* the `GROUP BY` operation.

You're telling the librarian: "Show me categories that have **more than 5 books**."

```php
$builder->from('products')
    ->selectRaw('category_id, COUNT(*) as count')
    ->groupBy('category_id')
    ->having('count', '>', 5)
    ->get();
// SQL: SELECT category_id, COUNT(*) as count FROM products 
//      GROUP BY category_id HAVING count > 5
```

⚠️ **Important:** You generally cannot use `having()` on columns that aren't in the `GROUP BY` clause or an aggregate function.

---

## Best Practices

1. **Use with Aggregates** — Grouping typically only makes sense when you're calculating something (`SUM`, `COUNT`, `AVG`).
2. **Filter rows first** — Use `where()` to filter raw data *before* grouping, and `having()` to filter the *results* of grouping. `where()` is faster.
3. **Select what you group** — In most strict SQL modes, you must select the columns you group by.

---

## See Also

- [Aggregates](Aggregates.md)
- [Filtering](Filtering.md)
