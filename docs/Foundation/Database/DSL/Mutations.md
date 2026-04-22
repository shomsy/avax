# Mutations (INSERT / UPDATE / DELETE)

This document covers data modification operations — how to create, update, and delete records.

---

## Table of Contents

- [insert](#insert)
- [update](#update)
- [delete](#delete)
- [upsert](#upsert)
- [increment / decrement](#increment--decrement)
- [insertOrIgnore](#insertorignore)

---

## insert

**Create a new record in the database.**

Adds a row to the target table with the specified column/value pairs.

```php
// Single insert
$builder->from('users')->insert([
    'name' => 'Alice',
    'email' => 'alice@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);
// SQL: INSERT INTO users (name, email, created_at) VALUES (?, ?, ?)

// Returns: bool (true on success)
```

**Batch insert** (multiple rows at once):

```php
$builder->from('users')->insert([
    ['name' => 'Alice', 'email' => 'alice@example.com'],
    ['name' => 'Bob', 'email' => 'bob@example.com'],
    ['name' => 'Charlie', 'email' => 'charlie@example.com'],
]);
// SQL: INSERT INTO users (name, email) VALUES (?, ?), (?, ?), (?, ?)
```

---

## update

**Modify existing records that match the query.**

Changes column values for all rows matching your WHERE conditions.

⚠️ **DANGER:** Without WHERE conditions, this updates EVERY row in the table!

```php
// Update specific record
$builder->from('users')
    ->where('id', 42)
    ->update(['name' => 'Alice Smith']);
// SQL: UPDATE users SET name = ? WHERE id = ?

// Update multiple records
$builder->from('users')
    ->where('status', 'trial')
    ->where('created_at', '<', '2024-01-01')
    ->update(['status' => 'expired']);
// SQL: UPDATE users SET status = ? WHERE status = ? AND created_at < ?

// Returns: bool (true on success)
```

**Update with expressions:**

```php
$builder->from('products')
    ->where('id', 42)
    ->update([
        'price' => $builder->raw('price * 1.1'),  // 10% increase
        'updated_at' => $builder->raw('NOW()')
    ]);
```

---

## delete

**Remove matching records from the database.**

Permanently deletes all rows matching your WHERE conditions.

⚠️ **DANGER:** Without WHERE conditions, this deletes EVERYTHING in the table!

```php
// Delete specific record
$builder->from('users')
    ->where('id', 42)
    ->delete();
// SQL: DELETE FROM users WHERE id = ?

// Delete matching records
$builder->from('sessions')
    ->where('expires_at', '<', date('Y-m-d H:i:s'))
    ->delete();
// SQL: DELETE FROM sessions WHERE expires_at < ?

// Returns: bool (true on success)
```

---

## upsert

**Insert or update — "upsert" operation.**

Attempts to INSERT a record. If a duplicate key violation occurs, it UPDATEs instead.

Useful for "create or update" scenarios.

```php
$builder->from('user_preferences')->upsert(
    values: [
        'user_id' => 42,
        'theme' => 'dark',
        'language' => 'en'
    ],
    uniqueBy: ['user_id'],  // Column(s) that define uniqueness
    update: ['theme', 'language']  // Columns to update if exists
);

// MySQL: INSERT INTO user_preferences (...) VALUES (...)
//        ON DUPLICATE KEY UPDATE theme = VALUES(theme), language = VALUES(language)

// PostgreSQL: INSERT INTO user_preferences (...) VALUES (...)
//             ON CONFLICT (user_id) DO UPDATE SET theme = EXCLUDED.theme, ...
```

---

## increment / decrement

**Atomically increase or decrease a numeric column.**

Safer than fetching, calculating, and updating — avoids race conditions.

```php
// Increment
$builder->from('products')
    ->where('id', 42)
    ->increment('views');  // +1
// SQL: UPDATE products SET views = views + 1 WHERE id = ?

// Increment by specific amount
$builder->from('wallets')
    ->where('user_id', 42)
    ->increment('balance', 100);  // +100
// SQL: UPDATE wallets SET balance = balance + 100 WHERE user_id = ?

// Decrement
$builder->from('products')
    ->where('id', 42)
    ->decrement('stock', 5);  // -5
// SQL: UPDATE products SET stock = stock - 5 WHERE id = ?

// Increment with additional updates
$builder->from('posts')
    ->where('id', 42)
    ->increment('views', 1, [
        'last_viewed_at' => date('Y-m-d H:i:s')
    ]);
```

---

## insertorignore

**Insert but silently skip if duplicate key exists.**

Unlike upsert, this doesn't update — it just ignores the duplicate.

```php
$builder->from('subscriptions')->insertOrIgnore([
    'user_id' => 42,
    'newsletter' => 'weekly'
]);

// MySQL: INSERT IGNORE INTO subscriptions (...) VALUES (...)
// If user_id 42 already has a subscription, nothing happens
```

---

## Common Patterns

### 1. Create with Timestamps

```php
$builder->from('posts')->insert([
    'title' => 'My Post',
    'content' => 'Hello world',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);
```

### 2. Soft Delete

```php
// Instead of actually deleting...
$builder->from('users')
    ->where('id', 42)
    ->update([
        'deleted_at' => date('Y-m-d H:i:s')
    ]);
```

### 3. Bulk Update with Condition

```php
// Deactivate all expired trials
$builder->from('users')
    ->where('status', 'trial')
    ->where('trial_ends_at', '<', date('Y-m-d'))
    ->update(['status' => 'expired']);
```

### 4. Conditional Insert (Race-Safe)

```php
// Only insert if email doesn't exist
// (Better handled by upsert or database constraints)
if (!$builder->from('users')->where('email', $email)->exists()) {
    $builder->from('users')->insert(['email' => $email, ...]);
}
```

### 5. Transfer Money (Atomic)

```php
$builder->transaction(function () use ($builder, $from, $to, $amount) {
    $builder->from('accounts')
        ->where('id', $from)
        ->decrement('balance', $amount);
    
    $builder->from('accounts')
        ->where('id', $to)
        ->increment('balance', $amount);
});
```

---

## Mutation Results

Mutation methods return a boolean indicating success. For more details about affected rows, use the orchestrator
directly or check `PDO::rowCount()`.

```php
$success = $builder->from('users')->where('id', 42)->delete();

if ($success) {
    echo "Record deleted";
} else {
    echo "Delete failed";
}
```

---

## Best Practices

1. **Always use WHERE for UPDATE/DELETE** — Or you'll modify the entire table.

2. **Use transactions for related changes** — Keep data consistent.

3. **Use increment/decrement for counters** — Avoids race conditions.

4. **Prefer upsert over check-then-insert** — Atomic and race-safe.

5. **Validate before inserting** — The database will reject invalid data, but validating early gives better errors.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Transactions](Transactions.md)
- [Filtering](Filtering.md)
