# Pretend Mode

Pretend mode allows you to "dry run" your queries. It generates the SQL that *would* be executed without actually
running it against the database.

---

## How It Works

When you call `pretend()`, the builder enters a simulation mode. Any subsequent execution method (like `get()`,
`update()`, `delete()`) will:

1. Compile the QueryState into a SQL string
2. Gather the execution bindings
3. **Log** the intended query (via the Logger or debug output)
4. **Return** an empty result set (or `true`)
5. **NOT** execute the query on the database connection

---

## Usage

```php
// Simulate a destructive query
$builder->from('users')
    ->where('last_login', '<', '2020-01-01')
    ->pretend()
    ->delete();

// Output (in logs):
// Pretend: DELETE FROM users WHERE last_login < ? ["2020-01-01"]
```

## When to Use It

1. **Debugging**: See exactly what SQL your builder is producing.
2. **Testing**: verifying query structure without needing a real database.
3. **Auditing**: Logging what cleanup operations *would* do before enabling them.

---

## See Also

- [QueryBuilder::pretend()](QueryBuilder.md#pretend)
- [Introduction to Query Builder](QueryBuilder.md)
