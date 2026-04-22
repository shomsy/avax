# Query Execution

This document explains how the QueryBuilder executes queries against the database and how to fetch results.

---

## Retrieval Methods

These methods run a `SELECT` query and return data.

- **[get()](QueryBuilder.md#get)**: Returns an array of all matching records.
- **[first()](QueryBuilder.md#first)**: Returns the first matching record (or null/default).
- **[find()](QueryBuilder.md#find)**: Find a record by its primary key.
- **[value()](QueryBuilder.md#value)**: Get a single column's value from the first row.
- **[pluck()](QueryBuilder.md#pluck)**: Get a list of values for a specific column.
- **[exists()](QueryBuilder.md#exists)**: Check if any records match (returns boolean).
- **[count()](Aggregates.md#count)**: Count the number of matching records.

## Modification Methods

These methods modify data and return a boolean or count.

- **[insert()](Mutations.md#insert)**: Add new records.
- **[update()](Mutations.md#update)**: Modify existing records.
- **[delete()](Mutations.md#delete)**: Remove records.
- **[upsert()](Mutations.md#upsert)**: Insert or update if exists.

## The Execution Flow

1. **Build**: You define the query state (table, wheres, joins) using the builder methods.
2. **Compile**: When you call an execution method (like `get()`), the `Grammar` converts the state into a SQL string.
3. **Execute**: The `Orchestrator` prepares the statement, binds parameters, and runs it via the `Connection`.
4. **Fetch**: Results are fetched (typically as associative arrays) and returned.

---

## Debugging Execution

You can inspect the generated SQL before execution:

```php
$query = $builder->from('users')->where('active', true);

// Get SQL string
echo $query->toSql(); 
// "SELECT * FROM users WHERE active = ?"

// Get Bindings
print_r($query->getBindings()); 
// ["active" => true]

// Dry Run
$query->pretend()->get();
```

---

## See Also

- [QueryBuilder Overview](QueryBuilder.md)
- [Mutations](Mutations.md)
- [Aggregates](Aggregates.md)
