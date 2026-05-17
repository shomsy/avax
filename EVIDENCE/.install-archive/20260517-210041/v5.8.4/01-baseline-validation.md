# V5.8.4 Baseline Validation

Date: 2026-05-13
Branch: main
Commit: 5cbd0b2d9 (V5.8.3: harden components enterprise maturity)

## Validation Results

### composer validate

```
./composer.json is valid
```

Status: GREEN

### composer dump-autoload

```
Generated optimized autoload files containing 9272 classes
```

Status: GREEN

### PHPUnit

```
Tests: 8289, Assertions: 19041, Errors: 233
```

Status: RED

### PHPStan

```
Found 40 errors
```

Status: RED

## Error Classification

### PHPUnit 233 errors — Two root causes:

**Root Cause A: Router constructor injection (54 errors)**
Router constructor requires 3 params (ResolveCallable, RouteCollection, MatchRoute) but tests call `new Router()` with 0
args.

- `tests/Unit/Components/HTTP/Router/RouterTest.php` line 444: `new Router()`
- All 54 RouterTest methods fail with ArgumentCountError

**Root Cause B: Named parameter mismatch in RunApplication (179 errors)**
RunApplication.php line 67 passes `container:` named argument to ControllerResolver, but ControllerResolver constructor
expects `resolver:`.

- `framework/System/Flows/RunApplication/RunApplication.php:67` — `new ControllerResolver(container: clone $container)`
- `components/HTTP/Dispatcher/System/Capabilities/ActionResolution/ControllerResolver.php:17` —
  `__construct(private ResolveCallable $resolver)`
- Affects all V4RuntimeApp tests that use `App::create()` and dispatch routes

### PHPStan 40 errors — Two root causes:

**Root Cause A: Router constructor in tests (4 errors)**

- `tests/Integration/RouterHardeningTest.php` lines 25, 118 — `new Router()` with 0 args
- `tests/Integration/RouterIntegrationTest.php` line 109 — `new Router()` with 0 args
- `tests/Unit/Components/HTTP/Router/RouterTest.php` line 444 — `new Router()` with 0 args

**Root Cause B: 36 additional errors from the same patterns**
Need full analysis to classify remaining PHPStan errors.

## Baseline Status

| Metric            | Result               |
|-------------------|----------------------|
| Composer validate | GREEN                |
| Autoload          | GREEN (9272 classes) |
| PHPUnit           | RED (233 errors)     |
| PHPStan           | RED (40 errors)      |

## Fix Plan

1. **Fix RunApplication.php line 67**: Change `container:` to `resolver:` for ControllerResolver
2. **Fix RouterTest.php**: Provide proper dependencies in setUp — create RouteCollection, ResolveCallable, MatchRoute
3. **Fix RouterHardeningTest.php**: Same
4. **Fix RouterIntegrationTest.php**: Same
5. **Re-run PHPStan**: Many errors will resolve after Router test fixes
