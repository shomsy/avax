# Query States & AST

This document covers the internal query representation — the immutable state objects and AST (Abstract Syntax Tree) nodes that the QueryBuilder uses to represent SQL queries before compilation.

---

## Table of Contents

### Core State

- [QueryState](#querystate)

### AST Nodes

- [WhereNode](#wherenode)
- [JoinNode](#joinnode)
- [OrderNode](#ordernode)
- [NestedWhereNode](#nestedwherenode)

### Value Objects

- [BindingBag](#bindingbag)
- [TableIdentifier](#tableidentifier)
- [ColumnIdentifier](#columnidentifier)
- [QuotedIdentifier](#quotedidentifier)
- [Condition](#condition)
- [PaginationOptions](#paginationoptions)

---

# Core State

## querystate

**The "Working Memory" of a query being built.**

`QueryState` is an immutable object that holds all the accumulated information about a query: the table, columns, WHERE conditions, JOINs, ORDER BY clauses, LIMIT/OFFSET, and parameter bindings.

Think of it as a "recipe card" that gets passed through each building step. Each method on QueryBuilder creates a NEW QueryState with the new information added, leaving the original unchanged.

**Why immutable?**

1. **Safe forking** — You can branch off from a base query without modifying the original
2. **Thread safety** — Immutable objects can be safely shared
3. **Debugging** — Each state is a snapshot; you can inspect any point in the chain

**Properties stored:**

| Property | Type | Purpose |
|----------|------|---------|
| `from` | string\|null | Target table name |
| `columns` | array | SELECT columns (defaults to `*`) |
| `wheres` | array | WHERE conditions (WhereNode instances) |
| `joins` | array | JOIN definitions (JoinNode instances) |
| `orders` | array | ORDER BY rules (OrderNode instances) |
| `groups` | array | GROUP BY columns |
| `having` | array | HAVING conditions |
| `distinct` | bool | Whether to use DISTINCT |
| `limit` | int\|null | Maximum rows to return |
| `offset` | int\|null | Rows to skip |
| `bindings` | array | Parameter values for prepared statements |

**Key Methods (all return new instances):**

| Method | Purpose |
|--------|---------|
| `withFrom($table)` | Set the target table |
| `withColumns($columns)` | Set SELECT columns |
| `addWhere($whereNode)` | Add a WHERE condition |
| `addJoin($joinNode)` | Add a JOIN clause |
| `addOrder($orderNode)` | Add an ORDER BY rule |
| `withLimit($limit)` | Set LIMIT |
| `withOffset($offset)` | Set OFFSET |
| `addBinding($value)` | Add a parameter binding |
| `mergeBindings($values)` | Add multiple bindings |
| `getBindings()` | Get all current bindings |

```php
// QueryState is never modified — new instances are created
$state1 = new QueryState();
$state2 = $state1->withFrom('users');          // new instance
$state3 = $state2->withColumns(['id', 'name']); // another new instance

// $state1 is unchanged — still empty
// $state3 has table='users' and columns=['id', 'name']
```

---

# AST Nodes

## wherenode

**Represents a single WHERE condition.**

Each call to `where()` creates a WhereNode that captures the column, operator, value, and boolean connector.

```php
// This call:
$builder->where('status', '=', 'active');

// Creates this node:
new WhereNode(
    column: 'status',
    operator: '=',
    value: 'active',
    boolean: 'AND',
    type: 'Basic'
);
```

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `column` | string | The column name (or raw SQL) |
| `operator` | string | Comparison operator (=, >, <, LIKE, IN, etc.) |
| `value` | mixed | The comparison value |
| `boolean` | string | 'AND' or 'OR' connector |
| `type` | string | Node type ('Basic', 'Null', 'In', 'Raw', etc.) |

---

## joinnode

**Represents a JOIN clause.**

Captures the target table, join type, and matching conditions.

```php
// This call:
$builder->leftJoin('orders', 'users.id', '=', 'orders.user_id');

// Creates this node:
new JoinNode(
    table: 'orders',
    type: 'left',
    first: 'users.id',
    operator: '=',
    second: 'orders.user_id',
    clause: null  // Used for complex closures
);
```

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `table` | string | Table to join |
| `type` | string | 'inner', 'left', 'right', 'cross' |
| `first` | string\|null | Left-hand column |
| `operator` | string\|null | Comparison operator |
| `second` | string\|null | Right-hand column |
| `clause` | JoinClause\|null | For complex multi-condition joins |

---

## ordernode

**Represents an ORDER BY instruction.**

```php
// This call:
$builder->orderBy('created_at', 'DESC');

// Creates this node:
new OrderNode(
    column: 'created_at',
    direction: 'DESC',
    sql: null,
    type: 'Basic'
);
```

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `column` | string\|null | Column to sort by |
| `direction` | string | 'ASC' or 'DESC' |
| `sql` | string\|null | Raw SQL for special cases (e.g., RAND()) |
| `type` | string | 'Basic' or 'Raw' |

---

## nestedwherenode

**Represents a grouped WHERE clause (parentheses).**

When you use a closure for complex conditions, it creates this node.

```php
// This call:
$builder->where(function ($q) {
    $q->where('a', 1)->orWhere('b', 2);
});

// Creates this node:
new NestedWhereNode(
    query: $innerQueryBuilder,  // Contains the nested conditions
    boolean: 'AND'
);
```

This compiles to: `WHERE (a = ? OR b = ?)`

---

# Value Objects

## bindingbag

**Immutable container for parameter bindings.**

Holds all the `?` placeholder values in order. Each modification returns a new instance.

```php
$bag1 = new BindingBag();
$bag2 = $bag1->with('active');          // ['active']
$bag3 = $bag2->with(42);                // ['active', 42]
$bag4 = $bag3->merge([1, 2, 3]);        // ['active', 42, 1, 2, 3]

$values = $bag4->all();  // Get all bindings as array
$empty = $bag4->isEmpty();  // false
```

---

## tableidentifier

**Represents a table name with optional alias.**

```php
$table = new TableIdentifier(
    name: 'users',
    alias: 'u'
);

echo $table;  // "users AS u"
```

---

## columnidentifier

**Represents a column name with optional alias.**

```php
$column = new ColumnIdentifier(
    name: 'first_name',
    alias: 'name'
);

echo $column;  // "first_name AS name"
```

---

## quotedidentifier

**Represents an already-quoted SQL identifier.**

Used internally to prevent double-quoting. When the Grammar sees this, it knows not to add quotes.

```php
$quoted = new QuotedIdentifier('`users`');

// Grammar won't add additional quotes
echo $quoted;  // "`users`"
```

---

## condition

**Represents a single logical comparison.**

Similar to WhereNode but used in different contexts.

```php
$condition = new Condition(
    column: 'price',
    operator: '>',
    value: 100,
    boolean: 'AND'
);
```

---

## paginationoptions

**Encapsulates pagination parameters.**

Handles the math of converting page numbers to OFFSET/LIMIT.

```php
$pagination = new PaginationOptions(
    page: 3,
    perPage: 20,
    total: 150
);

echo $pagination->getOffset();  // 40 (page 3 skips 2*20 = 40 rows)

// Can be used directly:
$builder->limit($pagination->perPage)->offset($pagination->getOffset());
```

**Properties:**

| Property | Type | Purpose |
|----------|------|---------|
| `page` | int | Current page (1-indexed) |
| `perPage` | int | Items per page |
| `total` | int\|null | Total record count (optional) |

**The math:**

```
offset = (page - 1) × perPage

Page 1: (1-1) × 20 = 0   → OFFSET 0
Page 2: (2-1) × 20 = 20  → OFFSET 20
Page 3: (3-1) × 20 = 40  → OFFSET 40
```

---

## AST to SQL Compilation

The Grammar takes the QueryState and its AST nodes and compiles them to SQL:

```
QueryState
    ├── from: 'users'
    ├── columns: ['id', 'name']
    ├── wheres: [WhereNode(status = active)]
    ├── orders: [OrderNode(created_at DESC)]
    └── limit: 10
    
           ↓ Grammar::compileSelect()
           
SELECT id, name 
FROM users 
WHERE status = ? 
ORDER BY created_at DESC 
LIMIT 10
```

---

## Why This Architecture?

1. **Separation of Concerns** — Building ≠ Compiling. The builder assembles state; the Grammar compiles it.

2. **Dialect Support** — The same QueryState can be compiled by MySQLGrammar, PostgresGrammar, or SQLiteGrammar.

3. **Testability** — You can inspect QueryState without ever touching a database.

4. **Immutability** — Safe to share, fork, and debug.

5. **Extensibility** — Add new node types without changing existing code.

---

## See Also

- [QueryBuilder Overview](../DSL/QueryBuilder.md)
- [Filtering](../DSL/Filtering.md)
- [Joins](../DSL/Joins.md)
