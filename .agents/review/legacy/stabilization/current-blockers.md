# Current Blockers

Generated: 2026-04-30
Branch: refactor/component-suite-architecture
Commit: af2a7840 Avax refactor by master plan.

---

## 1. Git Status

```
M avax.txt
?? Code-Review-And-ToDo/stabilization/
```

**Status:** Working tree mostly clean. Modified `avax.txt` and newly created `Code-Review-And-ToDo/stabilization/`
directory.

---

## 2. Composer Validation

```
./composer.json is valid
```

**Status:** PASS - composer.json is valid. No blocking issues.

---

## 3. PHPUnit Failure

```
Fatal error: Declaration of components\Container\Tests\Flow\RegisterBindings\RegistrationFakeRouter::get(string $path, callable|array|string $action): Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy must be compatible with Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface::get(string $u, mixed $a): void in /home/shomsy/projects/avax/tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php on line 106
```

**Status:** FAIL - Fatal error. PHPUnit cannot start because `RegistrationFakeRouter::get()` signature is incompatible
with `RouterInterface::get()`. The test double uses:

- Method signature: `get(string $path, callable|array|string $action): RouteRegistrarProxy`
- Interface expects: `get(string $u, mixed $a): void`

This is a broken test double that no longer matches the actual interface. All tests are blocked.

---

## 4. Public Surface Check

```
FAIL
/home/shomsy/projects/avax/components/HTTP/Request/System/PublicSurface/Request.php: PublicSurface has excessive private state (5 properties)
/home/shomsy/projects/avax/components/HTTP/Response/System/PublicSurface/Response.php: PublicSurface has excessive private state (4 properties)
```

**Status:** FAIL - Two PublicSurface violations:

- `Request.php` has 5 private properties (exceeds allowed limit)
- `Response.php` has 4 private properties (exceeds allowed limit)

---

## Summary of Blockers

| # | Blocker                                                      | Severity | File(s) Affected                                                             |
|---|--------------------------------------------------------------|----------|------------------------------------------------------------------------------|
| 1 | FakeRouter test double incompatible with RouterInterface     | CRITICAL | `tests/Foundation/Container/Flows/RegisterBindings/RegisterBindingsTest.php` |
| 2 | Request.php PublicSurface excessive private state (5 props)  | HIGH     | `components/HTTP/Request/System/PublicSurface/Request.php`                   |
| 3 | Response.php PublicSurface excessive private state (4 props) | HIGH     | `components/HTTP/Response/System/PublicSurface/Response.php`                 |
| 4 | PHPUnit cannot start (fatal error on load)                   | CRITICAL | All test files                                                               |
