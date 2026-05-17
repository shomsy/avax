# V4-01 Runtime App Layer — Evidence Report

Date: 2026-05-09
Branch: main
Author: Qoder CLI

## 1. Stage

V4-01 Runtime App Layer

## 2. Objective

Implement a zero-config runnable App API for AvaX V4:

```php
$app = Avax::create();
$app->get('/', fn () => 'Hello AvaX');
$app->post('/register', RegisterUser::class);
$app->run();
```

## 3. Scope Delivered

### 3.1 Core Implementation

| File | Responsibility |
|------|---------------|
| `framework/System/PublicSurface/Avax.php` (modified) | Added `Avax::create()` static factory |
| `framework/System/PublicSurface/App.php` | V4 zero-config App with route registration, dispatch, error handling |
| `framework/System/Flows/CreateApplication/CreateApplication.php` | Zero-config app factory (Runtime, ComponentRegistry, RequestScope, StateResetRegistry) |
| `framework/System/Flows/RunApplication/RunApplication.php` | V4 dispatch flow with response normalization |
| `framework/System/Capabilities/ResponseNormalization/NormalizeControllerResult.php` | Normalizes controller returns to HTTP responses |
| `framework/System/Capabilities/ErrorHandling/ClassifyApplicationException.php` | Classifies exceptions into categories with status codes |
| `framework/System/Capabilities/ErrorHandling/RenderApplicationError.php` | Renders classified exceptions to safe HTTP responses |
| `framework/System/Capabilities/HealthCheck/CheckApplicationHealth.php` | Minimal health endpoint |

### 3.2 Tests

| File | Tests | Assertions |
|------|-------|------------|
| `AppTest.php` | 17 | Behavioral |
| `AvaxCreateTest.php` | 6 | Behavioral |
| `CreateApplicationTest.php` | 5 | Behavioral |
| `ClassifyApplicationExceptionTest.php` | 8 | 8 |
| `RenderApplicationErrorTest.php` | 7 | 7 |
| `NormalizeControllerResultTest.php` | 10 | 10 |
| `CheckApplicationHealthTest.php` | 2 | 4 |
| **Total** | **56** | **84** |

## 4. Validation Evidence

### 4.1 PHPStan

```
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
Result: 0 errors
```

### 4.2 V4-01 Tests

```
vendor/bin/phpunit tests/Unit/Framework/V4RuntimeApp --no-coverage
Result: 56 tests, 84 assertions — GREEN
```

### 4.3 Full Test Suite

```
vendor/bin/phpunit --no-coverage
Result: 3122 tests, 12436 assertions, 30 failures (pre-existing, unchanged)
```

### 4.4 Composer

```
composer validate --no-check-publish → GREEN
composer dump-autoload -o → GREEN, 8737 classes
```

## 5. Architecture Compliance

### 5.1 Screaming Architecture

- Folders say flow or capability: YES
  - `Flows/CreateApplication/` — flow
  - `Flows/RunApplication/` — flow
  - `Capabilities/ResponseNormalization/` — capability
  - `Capabilities/ErrorHandling/` — capability
  - `Capabilities/HealthCheck/` — capability
  - `PublicSurface/App.php` — public surface

### 5.2 Canonical Component Shape

App follows canonical shape:
- `PublicSurface/Avax.php`, `PublicSurface/App.php` — entry points
- `Flows/CreateApplication/`, `Flows/RunApplication/` — flows
- `Capabilities/ResponseNormalization/`, `Capabilities/ErrorHandling/`, `Capabilities/HealthCheck/` — capabilities

### 5.3 Forbidden Folders

No forbidden folders used (Services, Helpers, Utils, etc.).

### 5.4 Naming

- Classes say responsibility: YES
- Methods say exact action: YES

## 6. Security

- Error rendering uses production mode to hide sensitive details
- Exception classification separates user errors (422, 403, 404, 405) from system errors (500)
- No secrets logged or returned

## 7. Performance

- No full DI Container initialization in V4-01 (deferred)
- RouteFacadeContainer used for minimal PSR-11 resolution
- No hidden I/O, no unbounded operations

## 8. Integration with Existing Framework

V4-01 layers over existing components without duplicating:
- Router: uses existing RouteCollection, MatchRoute
- Request: uses existing ReadIncomingHttpRequest (RuntimeRequest -> ServerRequest)
- Controller resolution: uses existing ControllerResolver, ArgumentResolver
- Response: uses existing ResponseFactory
- Scope: uses existing OpenHttpRequestScope, CloseHttpRequestScope
- State reset: uses existing ResetApplicationState

## 9. Known Limitations

1. **No middleware pipeline**: `App::use()` registers closures but middleware execution is not yet implemented.
2. **No full DI Container**: V4-01 uses RouteFacadeContainer. Full container integration deferred.
3. **SecureRequest autowiring**: Not yet tested through V4 App API (existing SecureRequest tests cover the component).
4. **Pre-existing test failures**: 30 failures in golden path, integration, and parallelism tests — not caused by V4-01.

## 10. Next Allowed Action

V4-02 (ReactPHP Runtime) or next V4 stage from main.

## 11. Files Changed

### Created (13)
- `framework/System/PublicSurface/App.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`
- `framework/System/Flows/RunApplication/RunApplication.php`
- `framework/System/Capabilities/ResponseNormalization/NormalizeControllerResult.php`
- `framework/System/Capabilities/ErrorHandling/ClassifyApplicationException.php`
- `framework/System/Capabilities/ErrorHandling/RenderApplicationError.php`
- `framework/System/Capabilities/HealthCheck/CheckApplicationHealth.php`
- `tests/Unit/Framework/V4RuntimeApp/AppTest.php`
- `tests/Unit/Framework/V4RuntimeApp/AvaxCreateTest.php`
- `tests/Unit/Framework/V4RuntimeApp/CreateApplicationTest.php`
- `tests/Unit/Framework/V4RuntimeApp/ClassifyApplicationExceptionTest.php`
- `tests/Unit/Framework/V4RuntimeApp/RenderApplicationErrorTest.php`
- `tests/Unit/Framework/V4RuntimeApp/NormalizeControllerResultTest.php`
- `tests/Unit/Framework/V4RuntimeApp/CheckApplicationHealthTest.php`

### Modified (4)
- `framework/System/PublicSurface/Avax.php`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.phpunit.cache/test-results`
