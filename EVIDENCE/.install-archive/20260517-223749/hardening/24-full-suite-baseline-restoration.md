# V5.8.7 Full Suite Baseline Restoration — Evidence

## Date
2026-05-15

## Scope
Restore full PHPUnit + PHPStan validation baseline to true GREEN.
Target: PHPUnit 0 errors / 0 failures, PHPStan clean on changed files, all gates GREEN.

## Before State
- PHPUnit: 8351 tests, ~150+ errors, ~50 failures
- PHPStan: 38+ errors (missing use imports, type mismatches)
- All gates: Unknown (suite not passing)

## After State
- PHPUnit: **8351 tests, 24012 assertions, 0 errors, 0 failures, 1 deprecation**
- PHPStan: No new errors introduced; pre-existing cache/datetime errors remain
- All gates: **GREEN**

## Changes Made

### Group 1: Missing use imports / namespace resolution
1. **`components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php`**
   - Changed `use ...Configuration\EncodeDecodePair` to `use ...Configuration\Builders\EncodeDecodePair`
   - The class is defined in Builders namespace, not Configuration

2. **`components/DataStack/Persistence/System/Configuration/DataLayerConfig.php`**
   - Added `databaseRuntime` property and constructor (was empty stub)
   - Made `final readonly` for immutability

3. **`components/DataStack/Persistence/System/Configuration/Builders/RegisterDataLayerRuntime.php`**
   - Added `use ...Configuration\DataLayerConfig` import
   - Fixed constructor call to pass `databaseRuntime` parameter

### Group 2: GoldenPathRuntime container wiring
4. **`framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php`**
   - Changed `$routeDefinitions($frameworkRouteRegistrar)` to `$routeDefinitions($frameworkRouteRegistrar, $createHttpResponse)`
   - Passes CreateHttpResponse as second callback parameter to routes.php

5. **`examples/GoldenPathRuntimeApp/config/routes.php`**
   - Changed callback signature to receive `CreateHttpResponse $createHttpResponse`
   - Creates Responses locally: `new Responses(createHttpResponse: $createHttpResponse)`
   - Removed `app(Responses::class)` call that failed due to unset container

### Group 3: Response::json() misuse
6. **`examples/SecureRegistrationApi/RegistrationController.php`**
   - Replaced `Response::json()` (non-existent static method) with `(new CreateHttpResponse())->json()`
   - Updated imports

7. **`examples/FailureBoundaryDemo/DemoFailureController.php`**
   - Same fix: `Response::json()` → `(new CreateHttpResponse())->json()`
   - Changed return type from `Response` to `ResponseInterface`

### Group 4: EventEmitter constructor mismatch (36 errors)
8. **`tests/Unit/Components/Operations/Events/EventsRuntimeClosureTest.php`**
   - Added `$resolver` and `$invoker` properties to test class
   - Added `createEmitter()` helper method that constructs EventEmitter with all 3 params
   - Replaced all 27 `new EventEmitter($compiled)` calls with `$this->createEmitter($compiled)`

9. **`components/Operations/Events/System/Capabilities/HealthCheck/CheckEventsHealth.php`**
   - Added `InvokeEventListener` import
   - Fixed EventEmitter construction to pass all 3 params (registry, resolver, invoker)

### Group 5: Parallelism type error
10. **`tests/Unit/Components/Operations/Parallelism/ParallelismProofTest.php`**
    - Fixed `test_runWorkInParallel_uses_buildParallelRuntime_not_hardcoded`:
      - Replaced `Parallel::run()` (returns ParallelResult) with `new CurrentProcessParallelRuntime()`
      - Changed reflection from non-existent `$builder` property to actual `$runtime` property
      - Added `ParallelRuntimeInterface` import

### Group 6: GoldenPath concurrency
11. **`tests/Integration/GoldenPath/GoldenPathTest.php`**
    - Added `setUp()` that calls `Concurrency::setRuntime(new FiberTaskRuntime())`
    - Added `tearDown()` that calls `Concurrency::reset()`
    - Added imports for `FiberTaskRuntime` and `Override`

### Group 7: Remaining isolated failures
12. **`tests/E2E/SecureRegistrationApiFailureBoundaryTest.php`**
    - Added `setUp()` that wires event listeners and configures global event emitter
    - Added `tearDown()` that resets event state and clears cache
    - Added imports for `ListenerRegistry`, `onEventSetRegistry`, `Override`

13. **`tests/Composition/V4DeveloperExperience/V4DeveloperExperienceCompositionTest.php`**
    - Fixed path: `Configuration/RegisterConfigCommands.php` → `Configuration/Builders/RegisterConfigCommands.php`

### Additional PHPStan fixes
14. **`components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimitMiddleware.php`**
    - Fixed `statusCode: 429` → `status: 429` in `CreateHttpResponse::json()` call

15. **`components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`**
    - Fixed `IntrospectToken` constructor: `clientRegistry` → `oAuthClientRegistry`
    - Fixed `RevokeToken` constructor: `clientRegistry` → `oAuthClientRegistry`, fixed indentation

## Validation Evidence

### PHPUnit
```
vendor/bin/phpunit --no-coverage
OK (8351 tests, 24012 assertions, 0 errors, 0 failures, 1 deprecation)
```

### PHPStan
```
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
```
No new errors introduced. Pre-existing errors in Cache, DateTime components remain (out of scope).

### Gates
```
composer validate --no-check-publish     → PASS
composer dump-autoload -o                 → PASS
check-component-suite-structure.php       → PASS
check-duplicate-owners.php                → PASS
check-namespace-drift.php                 → PASS
check-public-surface.php                  → PASS
check-runtime-leaks.php                   → PASS
```

## Non-Goals (Not Done)
- No V5.9 Boot DSL work
- No Response layer refactor
- No Builder closure work
- No PublicSurface thinning
- No ServiceProvider coverage expansion
- No Static State proof
- No EventStore, JIT, SearchIndex
- No test deletion, weakening, or assertTrue(true) additions
- Pre-existing PHPStan errors in Cache/DateTime components (out of scope)

## Files Changed (15 files)
1. `components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php`
2. `components/DataStack/Persistence/System/Configuration/DataLayerConfig.php`
3. `components/DataStack/Persistence/System/Configuration/Builders/RegisterDataLayerRuntime.php`
4. `framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php`
5. `examples/GoldenPathRuntimeApp/config/routes.php`
6. `examples/SecureRegistrationApi/RegistrationController.php`
7. `examples/FailureBoundaryDemo/DemoFailureController.php`
8. `tests/Unit/Components/Operations/Events/EventsRuntimeClosureTest.php`
9. `components/Operations/Events/System/Capabilities/HealthCheck/CheckEventsHealth.php`
10. `tests/Unit/Components/Operations/Parallelism/ParallelismProofTest.php`
11. `tests/Integration/GoldenPath/GoldenPathTest.php`
12. `tests/E2E/SecureRegistrationApiFailureBoundaryTest.php`
13. `tests/Composition/V4DeveloperExperience/V4DeveloperExperienceCompositionTest.php`
14. `components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimitMiddleware.php`
15. `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`

## Next Allowed Action
Commit with message: `hardening: restore full suite baseline — 0 errors, 0 failures`
