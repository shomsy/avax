# Query Builder DSL

The `QueryBuilder` class provides a fluent, immutable interface for constructing and executing SQL queries. This
document explains each method in human-readable terms.

---

## Table of Contents

- [Constructor](#constructor)
- [newQuery](#newquery)
- [from](#from)
- [select](#select)
- [selectRaw](#selectraw)
- [distinct](#distinct)
- [limit](#limit)
- [offset](#offset)
- [raw](#raw)
- [get](#get)
- [first](#first)
- [find](#find)
- [value](#value)
- [pluck](#pluck)
- [count](#count)
- [exists](#exists)
- [insert](#insert)
- [update](#update)
- [delete](#delete)
- [pretend](#pretend)
- [deferred](#deferred)
- [transaction](#transaction)
- [statement](#statement)

---

## constructor

**Set up the builder with its two essential helpers.**

The constructor wires together the two main dependencies: a **Grammar** (the "Translator" that converts your PHP method
calls into the specific SQL dialect for your database) and an **Orchestrator** (the "Conductor" that actually runs your
queries against the database).

Think of it like setting up a phone call. The Grammar is your translator (they know the language of the person you're
calling), and the Orchestrator is the telephone itself (the tool that actually makes the connection).

---

## newQuery

**Start a brand new, empty query using the same database setup.**

Creates a fresh QueryBuilder instance that shares the same Grammar and Orchestrator, but has no filters, tables, or
columns set. Imagine you're filling out a form — `newQuery()` is like grabbing a blank form from the same stack.

```php
$freshQuery = $builder->newQuery()->from('users')->where('active', true);
```

---

## from

**Set the target table for your query.**

This tells the QueryBuilder which database table to work with — the equivalent of the SQL `FROM` clause. Think of
walking into a library and telling the librarian: "I want to browse the **Science Fiction** shelf."

The QueryBuilder is **immutable** — calling `from()` returns a *new* builder with the table set, leaving the original
unchanged.

```php
$userQuery = $builder->from('users');
$orderQuery = $builder->from('orders');  // Completely separate
```

---

## select

**Choose which columns to include in your results.**

Specifies exactly which fields you want back from the database, instead of retrieving everything (`*`). When ordering
coffee, you don't say "give me everything" — you say "I want a latte." This method is you saying: "I only want `name`
and `email`, not all 50 columns."

Benefits:

- **Performance** — Fetching only what you need reduces database load
- **Clarity** — Your code documents exactly what data it uses
- **Security** — You don't accidentally expose sensitive columns

```php
$builder->from('users')->select('id', 'name', 'email');
// SQL: SELECT id, name, email FROM users
```

---

## selectRaw

**Inject raw SQL expressions into your column selection.**

Allows you to add SQL snippets that the QueryBuilder can't express naturally — things like `COUNT(*)`, `CONCAT()`,
`DATE_FORMAT()`, or `CASE WHEN` expressions.

You're writing a letter to the database, and most of the time the QueryBuilder helps you write proper grammar. But
sometimes you need to scribble a note in the margins in your own words.

⚠️ **Warning:** Only use with **trusted** SQL fragments! Never pass user input directly.

```php
$builder->from('orders')
    ->select('customer_id')
    ->selectRaw('SUM(total) as revenue', 'COUNT(*) as order_count');
```

---

## distinct

**Remove duplicate rows from your results.**

Adds the `DISTINCT` keyword to your query, ensuring identical rows only appear once. You have a guest list with names
written multiple times — `distinct()` is like a bouncer who crosses out the duplicates.

```php
$builder->from('orders')->select('customer_id')->distinct();
// SQL: SELECT DISTINCT customer_id FROM orders
```

---

## limit

**Cap the maximum number of rows returned.**

Tells the database: "No matter how many matches exist, only give me this many." You're at an all-you-can-eat buffet, but
you tell yourself: "I'm only taking 10 items maximum."

Common uses:

- Pagination (show 20 items per page)
- "Top 10" lists
- Existence checks with `limit(1)`

```php
$builder->from('products')->orderBy('price', 'desc')->limit(5);
// SQL: SELECT * FROM products ORDER BY price DESC LIMIT 5
```

---

## offset

**Skip the first X rows before starting to return results.**

Used together with `limit()` for pagination. You're reading a book and want to continue from page 100 — you *skip* pages
1-99.

The Pagination Formula:

```text
offset = (page_number - 1) × items_per_page
```

Page 3 with 20 items per page → `offset(40)->limit(20)`

```php
$builder->from('users')->limit(20)->offset(40);  // Page 3
// SQL: SELECT * FROM users LIMIT 20 OFFSET 40
```

---

## raw

**Create a raw SQL expression that won't be quoted or escaped.**

Returns an `Expression` value object that tells the Grammar: "Don't touch this — inject it exactly as written."

Most of the time, the QueryBuilder puts quotes around your values to keep them safe. `raw()` is like handing the postman
a note that says: "Deliver this message as-is, don't seal it."

⚠️ **Security Warning:** NEVER use this with user input! Only use for trusted SQL fragments like:

- Built-in functions: `NOW()`, `UUID()`, `CURRENT_TIMESTAMP`
- Mathematical expressions: `price * quantity`
- Database defaults: `DEFAULT`

```php
$builder->from('users')->where('created_at', '>', $builder->raw('NOW() - INTERVAL 1 DAY'));
```

---

## get

**Execute the query and retrieve all matching records.**

The "Go button." This actually runs the SQL query against the database and returns an array of results.

You've been carefully writing your shopping list (building the query). `get()` is when you actually walk into the store
and collect all the items.

Returns an array of associative arrays, where each inner array represents one row.

```php
$users = $builder->from('users')->where('active', true)->get();
// Returns: [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob'], ...]
```

---

## first

**Get only the first matching record (or a default value).**

Executes the query with an implicit `LIMIT 1` and returns just one record. Instead of asking "show me ALL the red cars,"
you ask "show me A red car."

Parameters:

- `$key` — Optionally extract a single column or transform the result
- `$default` — What to return if nothing matches

```php
$user = $builder->from('users')->where('email', 'alice@example.com')->first();
// Returns: ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com']

$name = $builder->from('users')->where('id', 42)->first('name', 'Unknown');
// Returns: "Alice" (just the name, or "Unknown" if not found)
```

---

## find

**Retrieve a single record by its primary key.**

A shortcut for finding one record by ID. Instead of writing `where('id', 42)->first()`, you write `find(42)`.

You're in a library with numbered shelves. `find(42)` is: "Take me directly to shelf #42."

```php
$user = $builder->from('users')->find(42);
// Equivalent to: where('id', 42)->first()

$product = $builder->from('products')->find('SKU-ABC', 'sku');
// Uses 'sku' column instead of 'id'
```

---

## value

**Extract a single scalar value from the first matching record.**

When you only need ONE piece of data (not a whole row), this is the most efficient method. You don't need the whole
profile card, you just need the phone number.

```php
$email = $builder->from('users')->where('id', 42)->value('email');
// Returns: "alice@example.com" (just the string, not an array)
```

---

## pluck

**Get a flat array of values from a single column.**

Retrieves all matching records but returns only the values of one specific column. You have a class of students —
instead of getting everyone's full profiles, you ask: "Give me a list of all the names."

Optional Keying — you can use a second column to key the array:

```php
$emails = $builder->from('users')->pluck('email');
// Returns: ['alice@example.com', 'bob@example.com', ...]

$emailsById = $builder->from('users')->pluck('email', 'id');
// Returns: [1 => 'alice@example.com', 2 => 'bob@example.com', ...]
```

---

## count

**Count how many records match your query.**

Returns an integer representing the total number of matching rows. You're a librarian who needs to know: "How many
mystery novels do we have?" You don't need the books, just the count.

```php
$total = $builder->from('users')->where('active', true)->count();
// Returns: 42 (integer)
```

---

## exists

**Check if ANY matching records exist.**

Returns `true` if at least one record matches, `false` if none do. More efficient than `count() > 0` because it stops
after finding the first match.

You're checking if a restaurant has ANY tables available — you don't need to count all empty tables.

```php
if ($builder->from('users')->where('email', $email)->exists()) {
    throw new EmailAlreadyTakenException();
}
```

---

## insert

**Create a new record in the database.**

Persists a new row to the target table using the provided column/value pairs. You're adding a new entry to a phone book.

```php
$builder->from('users')->insert([
    'name' => 'Charlie',
    'email' => 'charlie@example.com',
    'created_at' => now()
]);
```

---

## update

**Modify existing records that match the query.**

Changes the values of specific columns for all rows that match your WHERE conditions. An announcement goes out: "
Everyone wearing a red shirt, please change to a blue shirt."

⚠️ **Warning:** Without `where()` conditions, this updates EVERY row!

```php
$builder->from('users')
    ->where('last_login', '<', now()->subYear())
    ->update(['status' => 'inactive']);
```

---

## delete

**Remove matching records from the database permanently.**

Deletes all rows that match the current WHERE conditions. A paper shredder — once deleted, the data is gone.

⚠️ **Warning:** Without WHERE conditions, this deletes EVERYTHING in the table!

```php
$builder->from('sessions')
    ->where('expires_at', '<', now())
    ->delete();
```

---

## pretend

**Enable "dry run" mode — generates SQL without executing it.**

Turns on simulation mode. The QueryBuilder will compile your SQL and pass it to the orchestrator's pretend handler (
usually logging), but won't touch the database.

A dress rehearsal for a play — the actors go through all the motions, but there's no audience. Perfect for debugging.

```php
$sql = $builder->from('users')->where('active', false)->pretend()->delete();
// Logs the SQL but DOES NOT actually delete anything!
```

---

## deferred

**Batch changes through an IdentityMap instead of executing immediately.**

Enables the Unit of Work pattern. Instead of running INSERT/UPDATE/DELETE immediately, changes are collected and flushed
together later.

Instead of sending 100 individual letters, you collect all the mail into one bag and send it in a single trip.

```php
$builder->from('products')
    ->deferred($identityMap)
    ->insert(['name' => 'Widget']);  // Not executed yet!

$identityMap->flush();  // NOW all deferred operations run
```

---

## transaction

**Execute code within a database transaction.**

Wraps your code in a BEGIN/COMMIT/ROLLBACK block. If anything throws an exception, all changes are automatically rolled
back.

A "Save Game" feature — you can make changes, and if something goes wrong, you reload from the last save point.

```php
$builder->transaction(function () use ($builder) {
    $builder->from('accounts')->where('id', 1)->update(['balance' => 900]);
    $builder->from('accounts')->where('id', 2)->update(['balance' => 1100]);
    // If either fails, BOTH are rolled back
});
```

---

## statement

**Execute a raw SQL command that doesn't return data.**

Runs a one-off SQL statement like `TRUNCATE`, `CREATE TABLE`, `ALTER`, or admin commands. Sending a command to a
robot: "Clean the floor." The robot does the job and gives a thumbs up, but doesn't bring anything back.

```php
$builder->statement('TRUNCATE TABLE logs');
$builder->statement('ALTER TABLE users ADD COLUMN phone VARCHAR(20)');
```

---

## See Also

- [Filtering (WHERE clauses)](Filtering.md)
- [Transactions](Transactions.md)
- [Mutations (INSERT/UPDATE/DELETE)](Mutations.md)
- [Raw Expressions](RawExpressions.md)
- [Pretend Mode](PretendMode.md)
- [Deferred Execution](DeferredExecution.md)
