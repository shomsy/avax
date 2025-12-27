# Aggregates (Statistical Functions)

The `HasAggregates` trait provides methods for performing database-level calculations. This document explains each aggregate method in human-readable terms.

---

## Table of Contents

- [count](#count)
- [max](#max)
- [min](#min)
- [avg](#avg)
- [sum](#sum)

---

## count

**Count how many records match your query.**

Returns an integer representing the total number of matching rows. The database does the counting — much faster than fetching all records and counting in PHP.

```php
// Count all users
$totalUsers = $builder->from('users')->count();
// SQL: SELECT COUNT(*) as aggregate FROM users

// Count with conditions
$activeUsers = $builder->from('users')->where('status', 'active')->count();
// SQL: SELECT COUNT(*) as aggregate FROM users WHERE status = ?

// Count specific column (excludes NULLs)
$usersWithEmail = $builder->from('users')->count('email');
// SQL: SELECT COUNT(email) as aggregate FROM users
```

---

## max

**Find the highest value in a column.**

Returns the maximum value from the specified field. Works with numbers, dates, and strings (alphabetical maximum).

```php
// Highest price
$mostExpensive = $builder->from('products')->max('price');
// SQL: SELECT MAX(price) as aggregate FROM products

// Most recent order date
$latestOrder = $builder->from('orders')->max('created_at');

// With conditions
$maxPriceInCategory = $builder->from('products')
    ->where('category_id', 5)
    ->max('price');
```

---

## min

**Find the lowest value in a column.**

Returns the minimum value from the specified field. The opposite of `max()`.

```php
// Cheapest product
$cheapest = $builder->from('products')->min('price');
// SQL: SELECT MIN(price) as aggregate FROM products

// Oldest user registration
$firstSignup = $builder->from('users')->min('created_at');

// Lowest score in a category
$lowestScore = $builder->from('scores')
    ->where('game_id', 42)
    ->min('points');
```

---

## avg

**Calculate the average (mean) of a numeric column.**

Returns the arithmetic mean of all values in the specified field. Useful for analytics and reporting.

```php
// Average order value
$avgOrderValue = $builder->from('orders')->avg('total');
// SQL: SELECT AVG(total) as aggregate FROM orders

// Average rating for a product
$avgRating = $builder->from('reviews')
    ->where('product_id', 123)
    ->avg('rating');

// Average salary by department
$avgSalary = $builder->from('employees')
    ->where('department', 'Engineering')
    ->avg('salary');
```

---

## sum

**Calculate the total of a numeric column.**

Adds up all values in the specified field. Essential for financial reports, inventory, and analytics.

```php
// Total revenue
$totalRevenue = $builder->from('orders')->sum('total');
// SQL: SELECT SUM(total) as aggregate FROM orders

// Total inventory count
$totalStock = $builder->from('products')->sum('quantity');

// Revenue for a specific customer
$customerSpending = $builder->from('orders')
    ->where('customer_id', 42)
    ->sum('total');

// Revenue this month
$monthlyRevenue = $builder->from('orders')
    ->where('created_at', '>=', '2024-01-01')
    ->where('created_at', '<', '2024-02-01')
    ->sum('total');
```

---

## Combining Aggregates with GROUP BY

Aggregates become even more powerful with grouping. See the [Groups documentation](Groups.md) for details.

```php
// Revenue per category (requires GROUP BY)
$builder->from('products')
    ->selectRaw('category_id, SUM(price * quantity) as revenue')
    ->groupBy('category_id')
    ->get();
```

---

## Best Practices

1. **Use database-level aggregates** — Always prefer `count()` over `count($builder->get())`. The database is optimized for this.

2. **Watch for NULLs** — Aggregates ignore NULL values. `COUNT(column)` counts non-NULL values; `COUNT(*)` counts all rows.

3. **Use with conditions** — Aggregates respect WHERE clauses, so you can calculate for specific subsets.

4. **Consider decimal precision** — `AVG()` may return many decimal places. Cast or round as needed.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Filtering](Filtering.md)
- [Grouping](Groups.md)
