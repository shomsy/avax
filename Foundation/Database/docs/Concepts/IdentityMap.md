# Identity Map

This document covers the Identity Map pattern implementation, explaining how it provides object identity and deferred
persistence.

---

## Table of Contents

- [What is an Identity Map?](#what-is-an-identity-map)
- [IdentityMap Class](#identitymap)
- [Deferred Execution](#deferred-execution)
- [Unit of Work Pattern](#unit-of-work-pattern)

---

## What is an Identity Map?

**A cache that ensures each database row maps to exactly one object instance.**

Without an Identity Map, fetching the same user twice gives you two separate objects:

```php
// Without Identity Map - TWO different objects!
$user1 = $builder->from('users')->find(42);  // Object A
$user2 = $builder->from('users')->find(42);  // Object B (different instance!)

$user1['name'] = 'Changed';
// $user2['name'] is still unchanged! Confusing.
```

With an Identity Map, you always get the SAME object:

```php
// With Identity Map - ONE shared object
$user1 = $identityMap->get('users', 42);  // Object A
$user2 = $identityMap->get('users', 42);  // Object A (same instance!)

$user1['name'] = 'Changed';
// $user2['name'] is also 'Changed' - they're the same object
```

Think of it as a "guest registry" at a hotel. When someone checks in, you write down their room number. If they come
back later, you don't give them a new room — you look up their existing one.

---

## identitymap

**The in-memory cache for tracked entities.**

The `IdentityMap` class stores entities by their table and primary key. It's the foundation for both object identity and
the Unit of Work pattern.

**Key Methods:**

| Method                               | Purpose                        |
|--------------------------------------|--------------------------------|
| `register($table, $id, $entity)`     | Add an entity to the map       |
| `get($table, $id)`                   | Retrieve an entity (or null)   |
| `has($table, $id)`                   | Check if an entity exists      |
| `remove($table, $id)`                | Remove an entity from tracking |
| `clear()`                            | Reset the entire map           |
| `scheduleInsert($table, $data)`      | Queue an INSERT for later      |
| `scheduleUpdate($table, $id, $data)` | Queue an UPDATE for later      |
| `scheduleDelete($table, $id)`        | Queue a DELETE for later       |
| `flush()`                            | Execute all pending operations |

```php
$map = new IdentityMap($connection);

// Register a fetched entity
$user = $builder->from('users')->find(42);
$map->register('users', 42, $user);

// Later retrieval returns the same instance
$sameUser = $map->get('users', 42);
var_dump($user === $sameUser); // true
```

---

## deferred-execution

**Batch operations instead of executing immediately.**

Instead of running INSERT/UPDATE/DELETE queries one by one, you can "schedule" them and execute all at once with
`flush()`.

This is more efficient (fewer round-trips to the database) and allows you to cancel everything if something goes wrong.

```php
$map = new IdentityMap($connection);

// Schedule operations (nothing hits the database yet!)
$map->scheduleInsert('users', ['name' => 'Alice', 'email' => 'alice@example.com']);
$map->scheduleInsert('users', ['name' => 'Bob', 'email' => 'bob@example.com']);
$map->scheduleUpdate('products', 42, ['stock' => 99]);
$map->scheduleDelete('sessions', 'expired_session_id');

// All operations are pending...

// Execute everything at once
$map->flush();

// Now the database has been updated
```

**Using with QueryBuilder:**

```php
$builder->from('products')
    ->deferred($identityMap)      // Enable deferred mode
    ->insert(['name' => 'Widget']);  // Scheduled, not executed

$builder->from('products')
    ->deferred($identityMap)
    ->where('id', 42)
    ->update(['price' => 29.99]);    // Scheduled, not executed

// Execute all pending operations
$identityMap->flush();
```

---

## unit-of-work-pattern

**Track all changes and commit them atomically.**

The Unit of Work pattern combines the Identity Map with deferred execution to provide transactional consistency. All
changes within a "unit of work" either succeed together or fail together.

```php
// Start a unit of work
$unitOfWork = new IdentityMap($connection);

// Make changes
$user = ['id' => 1, 'name' => 'Updated Name'];
$unitOfWork->scheduleUpdate('users', 1, $user);

$orderItem = ['order_id' => 100, 'product_id' => 5, 'quantity' => 2];
$unitOfWork->scheduleInsert('order_items', $orderItem);

$unitOfWork->scheduleDelete('cart_items', 999);

// Commit all changes atomically
$connection->getPdo()->beginTransaction();
try {
    $unitOfWork->flush();
    $connection->getPdo()->commit();
} catch (\Throwable $e) {
    $connection->getPdo()->rollBack();
    throw $e;
}
```

**Why use this pattern?**

1. **Object Identity** — Prevents the "same row, different objects" problem
2. **Change Tracking** — The map knows which entities have been modified
3. **Batch Efficiency** — Fewer database round-trips
4. **Atomic Commits** — All or nothing persistence
5. **Optimistic Locking** — Can detect concurrent modifications

---

## Practical Example: Shopping Cart Checkout

```php
class CheckoutService
{
    public function process(Cart $cart, IdentityMap $unitOfWork): Order
    {
        // Schedule order creation
        $orderData = [
            'customer_id' => $cart->customerId,
            'total' => $cart->total,
            'status' => 'pending'
        ];
        $unitOfWork->scheduleInsert('orders', $orderData);
        
        // Schedule order items
        foreach ($cart->items as $item) {
            $unitOfWork->scheduleInsert('order_items', [
                'product_id' => $item->productId,
                'quantity' => $item->quantity,
                'price' => $item->price
            ]);
            
            // Schedule stock reduction
            $unitOfWork->scheduleUpdate('products', $item->productId, [
                'stock' => $item->currentStock - $item->quantity
            ]);
        }
        
        // Schedule cart cleanup
        foreach ($cart->items as $item) {
            $unitOfWork->scheduleDelete('cart_items', $item->id);
        }
        
        // Nothing has happened yet!
        // All operations are queued.
        
        // Now execute everything in a transaction
        $this->connection->transaction(function () use ($unitOfWork) {
            $unitOfWork->flush();
        });
        
        // All done atomically!
        return $this->fetchCreatedOrder();
    }
}
```

---

## Integration with QueryBuilder

The QueryBuilder's `deferred()` method integrates with the Identity Map:

```php
$identityMap = new IdentityMap($connection);

// These schedule operations without executing
$builder->from('users')->deferred($identityMap)->insert(['name' => 'Alice']);
$builder->from('logs')->deferred($identityMap)->insert(['action' => 'user_created']);

// Execute all at once
$identityMap->flush();
```

---

## Entity Lifecycle

```
┌─────────────────┐
│  Entity Created │ (new, not in map)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│    register()   │ (now tracked in map)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Modifications │ (changes tracked)
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│ scheduleUpdate()    │ (pending changes)
└────────┬────────────┘
         │
         ▼
┌─────────────────┐
│     flush()     │ (written to DB)
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│   Persisted!    │
└─────────────────┘
```

---

## Best Practices

1. **Use per-request maps** — Create a new Identity Map for each request to avoid stale data.

2. **Flush at boundaries** — Flush at the end of a use case, not after every change.

3. **Combine with transactions** — Wrap `flush()` in a transaction for atomicity.

4. **Clear after flush** — Consider clearing the map after flushing to release memory.

5. **Don't share across threads** — The Identity Map is not thread-safe.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Transactions](Transactions.md)
- [Deferred Execution](DeferredExecution.md)
