# Raw Expressions

## What “raw” means here
`raw()` and `selectRaw()` let you inject SQL fragments without quoting or escaping. They are escape hatches for constructs the grammar doesn’t cover.

## Why it exists
- Allow database functions (`NOW()`, `JSON_EXTRACT`), complex CASE statements, or vendor-specific syntax that the builder can’t generate yet.

## When to use
- Trusted, static SQL fragments owned by the codebase.
- Vendor functions or complex expressions you can’t express via the DSL.

## When *not* to use
- **Never** for user-provided input (search terms, form fields, query params).
- Avoid for identifiers built from user input; prefer bound parameters and grammar helpers.

## Validation/guards
- The builder rejects fragments with statement terminators, comments, control chars, or non-ASCII.
- Fragments are included verbatim; bindings are not applied inside raw strings.

## Examples
```php
// Vendor function
$builder->selectRaw('NOW() as current_time');

// CASE expression
$builder->selectRaw(
    "CASE WHEN status = 'paid' THEN 1 ELSE 0 END AS is_paid"
);
```

## Common pitfalls
- Attempting to interpolate user data: use bindings instead.
- Assuming raw fragments are sanitized: they are not; only minimal allowlist checks run.
- Using multi-statement or comment syntax: rejected by the allowlist.
