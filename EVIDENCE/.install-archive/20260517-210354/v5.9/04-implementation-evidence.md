# EVIDENCE/v5.9/04-implementation-evidence.md

## V5.9 Boot DSL — Implementation Evidence

**Date:** 2026-05-16
**Branch:** main
**Commit:** ec6a0c076 (base)

---

## 1. Files Created

| File                                                          | Responsibility                                                                      |
|---------------------------------------------------------------|-------------------------------------------------------------------------------------|
| `framework/System/Configuration/BootDsl/BootPhase.php`        | Enum: lifecycle stages (Create → Register → Compile → Verify → Freeze → Boot → Run) |
| `framework/System/Configuration/BootDsl/ProviderRegistry.php` | Collects and orders ServiceProviders (framework first, then user)                   |
| `framework/System/Configuration/BootDsl/BootDslEngine.php`    | Internal engine: owns container lifecycle, creates Runtime and App                  |
| `framework/System/Configuration/BootDsl/BootDslBuilder.php`   | Public fluent DSL builder (`from()`, `withProvider()`, `withRoutes()`, `create()`)  |
| `framework/System/Flows/BootApplication/BootWithDsl.php`      | Flow: executes Boot DSL lifecycle from declarative inputs                           |
| `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php`  | 6 tests proving Boot DSL lifecycle                                                  |

## 2. Files Modified

| File                                      | Change                                                                                    |
|-------------------------------------------|-------------------------------------------------------------------------------------------|
| `framework/System/PublicSurface/Avax.php` | Added `Avax::dsl()` method returning `BootDslBuilder`. Existing `Avax::boot()` unchanged. |

---

## 3. Validation Output

### PHPUnit

```
Tests: 8425, Assertions: 24211, OK (0 failures, 0 errors)
New tests: 6 (BootDslTest)
```

### PHPStan

```
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
Result: CLEAN (0 errors)
```

### Gate Checks

```
php tooling/refactor/check-component-suite-structure.php → PASS
php tooling/refactor/check-duplicate-owners.php           → PASS
php tooling/refactor/check-namespace-drift.php            → PASS
php tooling/refactor/check-public-surface.php             → PASS
php tooling/refactor/check-runtime-leaks.php              → PASS
```

---

## 4. What Was Delivered

### Public API

```php
$app = Avax::dsl()
    ->from(projectPath: __DIR__, environmentName: 'production')
    ->withProvider(AppServiceProvider::class)
    ->withRoutes(__DIR__ . '/routes/web.php')
    ->create();
```

### Container Lifecycle (internal)

1. **Create** — instantiate SimpleContainer, bind primitives (ProjectPath, EnvironmentName, Clock, ContainerInterface)
2. **Register** — instantiate each ServiceProvider, call `register($container)`
3. **Compile** — prove core bindings resolve
4. **Verify** — check required bindings exist
5. **Freeze** — mark container immutable
6. **Boot** — call `boot($container)` on each provider
7. **Run** — create Runtime, boot ComponentRegistry, return App

### Backward Compatibility

- `Avax::boot(ApplicationBuilder)` unchanged — all existing call sites work.
- `Avax::create()` unchanged — zero-config factory still works.
- No existing tests broken.

---

## 5. Scope Compliance

### In scope (delivered)

- [x] Boot DSL fluent API
- [x] Container creation and lifecycle
- [x] Provider registration and boot ordering
- [x] Runtime creation from container
- [x] App returned as product
- [x] Tests proving the full path
- [x] Evidence documents

### Out of scope (not touched)

- [x] No auto-scanning of component directories
- [x] No config file loading
- [x] No container compile optimization
- [x] No provider priority/sorting beyond declaration order
- [x] No child container / request scope containers
- [x] No runtime adapter integration
- [x] No AuthBuilder split
- [x] No Response refactor
- [x] No Fix-This cleanup
- [x] No modification to Runtime execution path
- [x] No modification to individual ServiceProviders

---

## 6. Architecture Compliance

| Rule                           | Compliance                                                                                                                         |
|--------------------------------|------------------------------------------------------------------------------------------------------------------------------------|
| Folder says flow or capability | BootDsl/ = configuration capability, BootWithDsl.php = flow                                                                        |
| Unit says responsibility       | BootPhase = lifecycle stage, ProviderRegistry = provider collection, BootDslEngine = lifecycle engine, BootDslBuilder = fluent API |
| Function says exact action     | `createContainer()`, `registerProviders()`, `compileContainer()`, etc.                                                             |
| No forbidden folders           | No Services/, Helpers/, Utils/, etc.                                                                                               |
| Canonical component shape      | BootDsl lives in framework/System/Configuration/ (correct for framework assembly)                                                  |
| DI law                         | ServiceProviders receive ContainerInterface in approved composition contexts                                                       |
| Runtime neutrality             | Boot DSL knows nothing about ReactPHP, Swoole, RoadRunner, FrankenPHP                                                              |
| Long-lived worker safety       | Container is per-boot; Runtime receives ready graph                                                                                |

---

## 7. Status

**IMPLEMENTATION COMPLETE — All validation GREEN.**
