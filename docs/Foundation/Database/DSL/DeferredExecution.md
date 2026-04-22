# Deferred Execution

Deferred execution implements the [Unit of Work](https://martinfowler.com/eaaCatalog/unitOfWork.html) pattern, allowing
you to queue database operations and execute them all at once.

---

## The Concept

Instead of hitting the database immediately for every INSERT, UPDATE, or DELETE, deferred mode schedules these
operations in an [Identity Map](../Concepts/IdentityMap.md).

**Benefits:**

- **Performance**: Reduces database round-trips.
- **Atomicity**: All changes can be committed in a single transaction.
- **Order Optimization**: The Identity Map can potentially optimize the order of operations (though the current
  implementation respects insertion order).
- **Cancellation**: You can discard pending changes before flushing.

---

## Usage

Use the `deferred()` method to switch a builder into deferred mode. You must provide (or have access to) an
`IdentityMap`.

```php
$map = new IdentityMap($connection);

// 1. Queue an INSERT
$builder->from('users')
    ->deferred($map)
    ->insert(['name' => 'Alice']); 
// -> Returns true immediately, but nothing is in the DB yet!

// 2. Queue an UPDATE
$builder->from('products')
    ->deferred($map)
    ->where('id', 10)
    ->update(['stock' => 50]);

// 3. Queue a DELETE
$builder->from('logs')
    ->deferred($map)
    ->where('date', '<', '2020-01-01')
    ->delete();

// ... time passes ...

// 4. Execute ALL pending operations
$map->flush();
```

---

## Integration with Transactions

It is highly recommended to wrap `flush()` in a transaction block to ensure data integrity.

```php
$builder->transaction(function() use ($map) {
    $map->flush();
});
```

---

## See Also

- [Identity Map Pattern](../Concepts/IdentityMap.md)
- [QueryBuilder::deferred()](QueryBuilder.md#deferred)
- [Mutations](Mutations.md)
