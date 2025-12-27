# Pretend Mode (Dry Run)

## What it does
`pretend()` clones the builder and switches the orchestrator into dry-run mode. SQL is logged; no statements are sent to the database.

## Why it exists
- Inspect generated SQL without touching data.
- Validate DSL logic in development/tests without side effects.

## When to use
- Developing or debugging query shapes.
- Verifying migration/DDL statements before running them for real.
- Writing tests that assert SQL strings instead of executing them.

## When *not* to use
- Any flow that depends on database state changes or returned data. Pretend mode never persists or queries.
- Production monitoring: prefer structured telemetry instead of pretend runs.

## Behavior notes
- Applies to the returned clone only; the original builder is unchanged.
- Mutations return success in pretend mode, but nothing is written.
- Queries return empty arrays in pretend mode.

## Example
```php
$pretend = $builder->pretend();
$pretend->table('users')->where('active', true)->get(); // logs SQL, returns []
```

## Common pitfalls
- Forgetting you’re in pretend mode: always use the returned clone.
- Assuming the database validates the SQL: it doesn’t run, so typos won’t be caught.
