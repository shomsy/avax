# Joins (Relational Queries)

The `HasJoins` trait provides methods for combining data from multiple tables. This document explains each join method in human-readable terms.

---

## Table of Contents

- [join (INNER JOIN)](#join)
- [leftJoin](#leftjoin)
- [rightJoin](#rightjoin)
- [crossJoin](#crossjoin)

---

## join

**Combine tables with INNER JOIN — only matching records from both sides.**

The most common join type. It links two tables together and returns only the rows where a match exists in BOTH tables.

Think of it as a Venn diagram overlay — you only get the overlapping section.

```php
// Simple join: users with their orders
$builder->from('users')
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.name', 'orders.total');
// SQL: SELECT users.name, orders.total FROM users 
//      INNER JOIN orders ON users.id = orders.user_id

// If a user has no orders, they WON'T appear in results
```

For complex join conditions, use a closure:

```php
$builder->from('products')
    ->join('categories', function ($join) {
        $join->on('products.category_id', '=', 'categories.id')
             ->where('categories.active', '=', true);
    });
```

---

## leftJoin

**Keep ALL records from the left table, even if no match exists on the right.**

Returns every row from the primary (left) table, and matching rows from the joined (right) table. If no match exists, the right side columns contain NULL.

You're asking for a roster of all employees, with their assigned projects. Employees without projects still appear, just with null project data.

```php
$builder->from('users')
    ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
    ->select('users.name', 'orders.total');
// SQL: SELECT users.name, orders.total FROM users 
//      LEFT JOIN orders ON users.id = orders.user_id

// Users with no orders will appear with orders.total = NULL
```

Common use cases:

- Finding records that DON'T have related data (orphans)
- Showing optional relationships
- Reports that need all primary records regardless of relationships

```php
// Find users WITHOUT any orders
$usersWithoutOrders = $builder->from('users')
    ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
    ->whereNull('orders.id')
    ->get();
```

---

## rightJoin

**Keep ALL records from the right table, even if no match exists on the left.**

The opposite of LEFT JOIN. Returns every row from the joined (right) table, and matching rows from the primary (left) table.

Less commonly used — you can usually restructure as a LEFT JOIN by swapping table order.

```php
$builder->from('orders')
    ->rightJoin('users', 'orders.user_id', '=', 'users.id')
    ->select('users.name', 'orders.total');
// SQL: SELECT users.name, orders.total FROM orders 
//      RIGHT JOIN users ON orders.user_id = users.id
```

---

## crossJoin

**Combine every row from table A with every row from table B (Cartesian product).**

No matching condition — creates all possible combinations. Use with extreme caution on large tables!

10 rows × 1000 rows = 10,000 result rows.

```php
$builder->from('sizes')
    ->crossJoin('colors');
// SQL: SELECT * FROM sizes CROSS JOIN colors

// If sizes has [S, M, L] and colors has [Red, Blue]
// Result: [S-Red, S-Blue, M-Red, M-Blue, L-Red, L-Blue]
```

Common use cases:

- Generating all possible combinations (product variants)
- Calendar matrices (all dates × all users)
- Test data generation

---

## Best Practices

1. **Always qualify column names** — When joining, use `table.column` format to avoid ambiguity.

2. **Be careful with NULL** — LEFT/RIGHT joins produce NULLs for non-matching rows. Account for this in your WHERE clauses.

3. **Watch performance** — Each join multiplies result set size. Index your join columns.

4. **Prefer LEFT JOIN over RIGHT JOIN** — It's more intuitive. Just swap your table order if needed.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Filtering](Filtering.md)
- [Ordering](Ordering.md)
