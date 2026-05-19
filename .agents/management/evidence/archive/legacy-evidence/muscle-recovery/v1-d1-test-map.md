# V1-D1 Test Map

## HTTP/Router Tests

| Test Path                                   | Target      | Status                        |
|---------------------------------------------|-------------|-------------------------------|
| tests/Integration/RouterIntegrationTest.php | HTTP/Router | 8 errors (missing AppFactory) |
| tests/Integration/RouterHardeningTest.php   | HTTP/Router | -                             |

## HTTP/Middleware Tests

| Test Path | Target          | Status    |
|-----------|-----------------|-----------|
| -         | HTTP/Middleware | NOT FOUND |

---

## Test Gaps

1. HTTP/Router integration tests fail due to missing test infrastructure (AppFactory)
2. HTTP/Middleware has no dedicated test files

---

## Recommended Test Additions

For HTTP/Router:

- Route registration test
- Route matching test (static)
- Route matching test (dynamic/parameters)
- URL generation test
- Middleware integration test
- Route groups test

For HTTP/Middleware:

- Pipeline execution test
- Call next behavior test
- Exception handling test
- Middleware stack ordering test

---

## Current Test Status

Tests exist but fail due to infrastructure issues, not production code issues.