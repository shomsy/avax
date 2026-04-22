# Schema Operations

This document covers structural database modifications — creating tables, dropping databases, and managing schema
objects.

---

## Table of Contents

- [create](#create)
- [dropIfExists](#dropifexists)
- [truncate](#truncate)
- [createDatabase](#createdatabase)
- [dropDatabase](#dropdatabase)

---

## create

**Define and build a new database table.**

Uses a `Blueprint` object to define columns, indexes, and constraints.

```php
$builder->create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('name');
    $table->timestamps();
});
// Executes: CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255) UNIQUE, ...)
```

---

## dropIfExists

**Safely remove a table.**

Drops the table only if the database system reports that it exists.

```php
$builder->dropIfExists('temporary_logs');
// Executes: DROP TABLE IF EXISTS temporary_logs
```

---

## truncate

**Clear all data from a table.**

Resets the table to an empty state, usually resetting auto-increment counters. Much faster than `delete()`.

```php
$builder->from('cache_entries')->truncate();
// Executes: TRUNCATE TABLE cache_entries
```

---

## createDatabase

**Create a new database/schema container.**

```php
$builder->createDatabase('my_new_app');
// Executes: CREATE DATABASE my_new_app
```

---

## dropDatabase

**Delete an entire database.**

⚠️ **DANGER:** This permanently destroys the database and all its tables and data.

```php
$builder->dropDatabase('old_test_db');
// Executes: DROP DATABASE old_test_db
```

---

## See Also

- [Migrations](../Concepts/Migrations.md) (If available)
- [QueryBuilder Overview](QueryBuilder.md)
