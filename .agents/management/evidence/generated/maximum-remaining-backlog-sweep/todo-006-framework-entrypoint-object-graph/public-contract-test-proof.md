# Public Contract Test Proof

## Contract Tests

Focused command:

```bash
vendor/bin/phpunit --filter "AvaxCreateTest|CreateApplicationTest|AppTest|BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
```

Proof:

- `AvaxCreateTest` proves the public zero-configuration creation path still returns a working app.
- `CreateApplicationTest` proves the internal creation flow still assembles behavior.
- `AppTest` proves route registration/handling behavior still works.
- `BootDslTest` proves BootDsl creation remains stable.
- `V4AppDoesNotDuplicateComponentsTest` proves architecture boundary expectations.

## Status

GREEN for Slice A.

Result:

```text
OK (122 tests, 234 assertions)
```

No public method on `Avax::create()`, `Avax::dsl()`, route registration, `App::handle()`, or `BootDsl::create()` changed.
