# Soft Deletes

Soft deleting allows you to "delete" a record without actually removing it from the database. Instead, a `deleted_at` timestamp is set.

---

## Enabling Soft Deletes

To use soft deletes, your table must have a `deleted_at` (TIMESTAMP/DATETIME) column.

---

## Deleting Records

When you call `delete()` on a soft-delete capable query, it updates the `deleted_at` column instead of removing the row.

```php
// Soft delete
$builder->from('users')->where('id', 1)->delete();
// SQL: UPDATE users SET deleted_at = NOW() WHERE id = 1
```

To permanently remove records (hard delete), use `forceDelete()`:

```php
// Hard delete
$builder->from('users')->where('id', 1)->forceDelete();
// SQL: DELETE FROM users WHERE id = 1
```

---

## Querying Soft Deleted Records

By default, soft-deleted records are **excluded** from query results.

```php
// Only returns active users (deleted_at IS NULL)
$users = $builder->from('users')->get();
```

### Including Deleted Records

To include soft-deleted records in your results:

```php
$allUsers = $builder->from('users')->withTrashed()->get();
```

### Only Deleted Records

To retrieve *only* the soft-deleted records:

```php
$trashedUsers = $builder->from('users')->onlyTrashed()->get();
```

---

## Restoring Records

To "undelete" a soft-deleted record, use `restore()`:

```php
$builder->from('users')
    ->withTrashed()
    ->where('id', 1)
    ->restore();
// SQL: UPDATE users SET deleted_at = NULL WHERE id = 1
```

---

## See Also

- [Mutations](Mutations.md)
- [Filtering](Filtering.md)
