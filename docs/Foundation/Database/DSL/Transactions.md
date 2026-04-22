# Transactions

This document covers database transaction management, explaining how to ensure atomic operations and data consistency.

---

## Table of Contents

- [transaction() Method](#transaction)
- [Transaction Class](#transactionclass)
- [TransactionScope](#transactionscope)
- [Nested Transactions](#nested-transactions)
- [Manual Transaction Control](#manual-transaction-control)

---

## transaction

**Execute a closure within an atomic database transaction.**

The simplest way to use transactions. Wrap your code in a closure, and the database will automatically:

- **COMMIT** if the closure succeeds
- **ROLLBACK** if the closure throws an exception

Think of it as a "Save Game" feature. You can make changes, and if something goes wrong, everything reverts to the last
save point.

```php
$builder->transaction(function () use ($builder) {
    // Deduct from sender
    $builder->from('accounts')
        ->where('id', 1)
        ->update(['balance' => 900]);
    
    // Credit to receiver
    $builder->from('accounts')
        ->where('id', 2)
        ->update(['balance' => 1100]);
    
    // If EITHER update fails, BOTH are rolled back
});
```

**Return Values:**

The transaction returns whatever your closure returns:

```php
$newUser = $builder->transaction(function () use ($builder) {
    $builder->from('users')->insert([
        'name' => 'Alice',
        'email' => 'alice@example.com'
    ]);
    
    return $builder->from('users')
        ->where('email', 'alice@example.com')
        ->first();
});

// $newUser contains the newly inserted user
```

**Exception Handling:**

```php
try {
    $builder->transaction(function () use ($builder) {
        $builder->from('orders')->insert(['total' => 100]);
        
        // This will trigger a rollback
        throw new \Exception('Something went wrong');
    });
} catch (\Exception $e) {
    // Transaction was rolled back
    // Original exception is re-thrown
}
```

---

## transactionclass

**Object-oriented transaction management.**

For more control than closures provide, use the `Transaction` class directly. This gives you explicit methods for begin,
commit, and rollback.

```php
$transaction = new Transaction($connection);

$transaction->begin();

try {
    // Perform operations
    $builder->from('products')->update(['stock' => 99]);
    $builder->from('orders')->insert(['product_id' => 1, 'quantity' => 1]);
    
    $transaction->commit();
} catch (\Throwable $e) {
    $transaction->rollback();
    throw $e;
}
```

**Key Methods:**

| Method       | Purpose                               |
|--------------|---------------------------------------|
| `begin()`    | Start a new transaction               |
| `commit()`   | Save all changes permanently          |
| `rollback()` | Discard all changes since begin       |
| `isActive()` | Check if a transaction is in progress |

---

## transactionscope

**RAII-style transaction that auto-rolls back on failure.**

Similar to `BorrowedConnection` — if the scope object is destroyed without an explicit commit, it automatically rolls
back.

Useful when you want transaction safety without try/catch blocks.

```php
$scope = new TransactionScope($connection);

// Operations inside the scope
$builder->from('users')->insert(['name' => 'Bob']);

// If we reach here without errors, commit
$scope->commit();

// If an exception occurred before commit(), the destructor rolls back
```

---

## Nested Transactions

**Savepoints for partial rollback.**

When you nest transaction calls, the inner transaction uses a SAVEPOINT instead of a full transaction. This allows
partial rollback.

```php
$builder->transaction(function () use ($builder) {
    $builder->from('orders')->insert(['id' => 1, 'total' => 100]);
    
    try {
        // Nested transaction (uses SAVEPOINT)
        $builder->transaction(function () use ($builder) {
            $builder->from('order_items')->insert(['order_id' => 1, 'product' => 'Widget']);
            
            throw new \Exception('Item failed');
        });
    } catch (\Exception $e) {
        // Inner transaction rolled back to savepoint
        // Outer transaction still active!
    }
    
    // This insert still happens
    $builder->from('order_notes')->insert(['order_id' => 1, 'note' => 'Partial order']);
    
    // Outer transaction commits
});
```

**How it works:**

```
OUTER transaction BEGIN
    INSERT INTO orders...
    
    SAVEPOINT sp1
        INSERT INTO order_items...
        Exception thrown!
    ROLLBACK TO SAVEPOINT sp1
    
    INSERT INTO order_notes... (still happens!)
OUTER transaction COMMIT
```

---

## Manual Transaction Control

**When you need full control.**

For complex scenarios, you can control transactions manually through the orchestrator or PDO directly.

```php
$pdo = $connection->getPdo();

$pdo->beginTransaction();

try {
    $pdo->exec("INSERT INTO users (name) VALUES ('Alice')");
    $pdo->exec("INSERT INTO profiles (user_id) VALUES (LAST_INSERT_ID())");
    
    $pdo->commit();
} catch (\PDOException $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

## Transaction Isolation Levels

**Controlling visibility of concurrent changes.**

Different isolation levels control what data a transaction can see from other concurrent transactions.

| Level            | Description                                           |
|------------------|-------------------------------------------------------|
| READ UNCOMMITTED | Can see uncommitted changes from others (dirty reads) |
| READ COMMITTED   | Only sees committed changes (default for many DBs)    |
| REPEATABLE READ  | Same query returns same results within transaction    |
| SERIALIZABLE     | Full isolation, as if transactions ran one at a time  |

```php
// Set isolation level before starting transaction
$pdo->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');

$builder->transaction(function () use ($builder) {
    // All operations here use SERIALIZABLE isolation
});
```

---

## Common Patterns

### 1. Transfer Money (Classic Example)

```php
$builder->transaction(function () use ($builder, $from, $to, $amount) {
    // Check balance first
    $balance = $builder->from('accounts')
        ->where('id', $from)
        ->value('balance');
    
    if ($balance < $amount) {
        throw new InsufficientFundsException();
    }
    
    // Deduct from sender
    $builder->from('accounts')
        ->where('id', $from)
        ->update(['balance' => $balance - $amount]);
    
    // Credit to receiver
    $builder->from('accounts')
        ->where('id', $to)
        ->increment('balance', $amount);
});
```

### 2. Create Related Records

```php
$orderId = $builder->transaction(function () use ($builder, $cart) {
    // Create order
    $builder->from('orders')->insert([
        'customer_id' => $cart->customerId,
        'total' => $cart->total,
        'status' => 'pending'
    ]);
    
    $orderId = $builder->from('orders')->max('id');
    
    // Create order items
    foreach ($cart->items as $item) {
        $builder->from('order_items')->insert([
            'order_id' => $orderId,
            'product_id' => $item->productId,
            'quantity' => $item->quantity,
            'price' => $item->price
        ]);
        
        // Decrement stock
        $builder->from('products')
            ->where('id', $item->productId)
            ->decrement('stock', $item->quantity);
    }
    
    return $orderId;
});
```

### 3. Retry on Deadlock

```php
$maxRetries = 3;
$attempt = 0;

while ($attempt < $maxRetries) {
    try {
        $builder->transaction(function () use ($builder) {
            // Operations that might deadlock
        });
        break; // Success!
    } catch (DeadlockException $e) {
        $attempt++;
        if ($attempt >= $maxRetries) {
            throw $e;
        }
        usleep(100000 * $attempt); // Exponential backoff
    }
}
```

---

## Best Practices

1. **Keep transactions short** — Long transactions hold locks and block other queries.

2. **Don't do I/O inside transactions** — HTTP requests, file operations, etc. should happen before or after, not
   during.

3. **Always handle rollback** — Either use closures (automatic) or wrap manual transactions in try/catch.

4. **Use appropriate isolation levels** — Don't use SERIALIZABLE everywhere; it's slower.

5. **Watch for deadlocks** — When multiple transactions access the same rows in different orders, deadlocks can occur.

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Mutations (INSERT/UPDATE/DELETE)](Mutations.md)
- [Deferred Execution](DeferredExecution.md)
