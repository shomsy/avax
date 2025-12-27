# Ordering (Sorting Results)

The `HasOrders` trait provides methods for sorting query results. This document explains each ordering method in
human-readable terms.

---

## Table of Contents

- [orderBy](#orderby)
- [orderByDesc](#orderbydesc)
- [inRandomOrder](#inrandomorder)
- [latest](#latest)
- [oldest](#oldest)

---

## orderBy

**Sort results by a column in ascending or descending order.**

The fundamental sorting method. Adds an `ORDER BY` clause to arrange results in a specific sequence.

Think of it as telling a librarian: "Arrange these books by title, A to Z."

```php
// Ascending (default) - A-Z, 1-100, oldest-newest
$builder->from('products')->orderBy('name');
// SQL: ORDER BY name ASC

// Descending - Z-A, 100-1, newest-oldest
$builder->from('products')->orderBy('price', 'DESC');
// SQL: ORDER BY price DESC

// Multiple columns - first by category, then by price within each category
$builder->from('products')
    ->orderBy('category')
    ->orderBy('price', 'DESC');
// SQL: ORDER BY category ASC, price DESC
```

---

## orderByDesc

**Shorthand for descending order.**

Instead of `orderBy('column', 'DESC')`, you can write `orderByDesc('column')`. More readable for common descending
sorts.

```php
// These are equivalent:
$builder->from('posts')->orderBy('created_at', 'DESC');
$builder->from('posts')->orderByDesc('created_at');
// SQL: ORDER BY created_at DESC
```

---

## inRandomOrder

**Shuffle results into a random sequence.**

Each query execution returns results in a different, unpredictable order. The database handles randomization
server-side.

Useful for "featured" sections, quizzes, or any time you need variety.

```php
$builder->from('products')->where('featured', true)->inRandomOrder()->limit(5);
// SQL: ORDER BY RAND()  (MySQL)
// SQL: ORDER BY RANDOM()  (PostgreSQL/SQLite)

// Get 3 random quotes for the homepage
$quotes = $builder->from('quotes')->inRandomOrder()->limit(3)->get();
```

⚠️ **Performance note:** Random ordering can be slow on large tables because the database must scan all rows before
shuffling.

---

## latest

**Sort by newest first (descending by timestamp).**

A semantic shorthand for `orderByDesc('created_at')`. Makes your code more expressive when dealing with chronological
data.

```php
// These are equivalent:
$builder->from('posts')->orderByDesc('created_at');
$builder->from('posts')->latest();

// Custom timestamp column
$builder->from('orders')->latest('placed_at');
// SQL: ORDER BY placed_at DESC

// Common pattern: get the 10 most recent posts
$recentPosts = $builder->from('posts')
    ->where('published', true)
    ->latest()
    ->limit(10)
    ->get();
```

---

## oldest

**Sort by oldest first (ascending by timestamp).**

The opposite of `latest()`. Useful for processing records in chronological order or showing historical data.

```php
// These are equivalent:
$builder->from('posts')->orderBy('created_at', 'ASC');
$builder->from('posts')->oldest();

// Process pending jobs oldest first (FIFO queue)
$pendingJobs = $builder->from('jobs')
    ->where('status', 'pending')
    ->oldest()
    ->get();
```

---

## Best Practices

1. **Order matters** — When chaining multiple `orderBy()` calls, the first one is primary, subsequent ones are secondary
   tiebreakers.

2. **Index your sort columns** — Sorting on non-indexed columns requires full table scans.

3. **Use semantic methods** — `latest()` and `oldest()` make code self-documenting compared to raw
   `orderBy('created_at', 'DESC')`.

4. **Combine with LIMIT** — Sorting is often paired with `limit()` for "top N" queries.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Filtering](Filtering.md)
- [Aggregates](Aggregates.md)
