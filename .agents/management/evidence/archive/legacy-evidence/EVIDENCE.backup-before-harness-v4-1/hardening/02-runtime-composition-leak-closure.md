# Pass 1: Runtime Composition Leak Closure — Evidence

## Summary

Fixed 6 runtime composition leaks in hot runtime paths. Strengthened the runtime composition gate with severity
classification and context-aware scanning.

## Files Changed (14 files, 287 insertions, 91 deletions)

### Framework (2 files)

#### `framework/System/PublicSurface/App.php`

- **Leak**: Created 8 new objects per request in `handleRequest()` (CreateRequestFromGlobals + 7 dependencies)
- **Fix**: Inject `CreateRequestFromGlobals` through constructor; assembled once in composition root
- **Severity**: HIGH — hot-path service instantiation per request
- **Validation**: 31/31 tests GREEN, PHPStan GREEN

#### `framework/System/Flows/CreateApplication/CreateApplication.php`

- **Leak**: N/A — this is the composition root that must assemble
- **Change**: Added `CreateRequestFromGlobals` to constructor and both `make()` / `fromBuilder()` factory methods
- **Severity**: N/A — correct location for assembly

### Components (7 files)

#### `components/Operations/Events/System/Foundation/EventEmitter.php`

- **Leak**: Constructor defaults `= new ResolveEventListeners()` and `= new InvokeEventListener()` — services
  instantiated as defaults
- **Fix**: Required injection for both dependencies
- **Severity**: HIGH — service instantiation as constructor default

#### `components/Operations/Events/System/Foundation/GlobalEventListenerState.php`

- **Leak**: `emitter()` method lazily composed `CompiledListenerRegistry`, `ResolveEventListeners`,
  `InvokeEventListener` when no emitter was set
- **Fix**: Throws `RuntimeException` if emitter not configured — boot-time error instead of runtime composition
- **Severity**: HIGH — lazy service composition at runtime

#### `components/Operations/Concurrency/System/PublicSurface/Concurrency.php`

- **Leak**: `runtime()` method used `??= (new BuildConcurrencyRuntime())->build()` — lazy builder pattern in hot path
- **Fix**: Throws `RuntimeException` if runtime not configured; requires `ConcurrencyServiceProvider` boot wiring
- **Also**: `race()` now delegates to `self::runtime()->race()` instead of `new RaceTasks()`
- **Severity**: HIGH — lazy builder composition + direct Flow instantiation in hot path

#### `components/Operations/Concurrency/System/Configuration/ConcurrencyServiceProvider.php`

- **Change**: Added `boot()` method that builds and sets the concurrency runtime via `Concurrency::setRuntime()`
- **Severity**: N/A — correct location for boot-time assembly

#### `components/Operations/Concurrency/System/Flows/RaceTasks/RaceTasks.php`

- **Leak**: Constructor default `= new FiberTaskRuntime()` — service instantiation as default
- **Fix**: Required injection for `FiberTaskRuntime`
- **Severity**: HIGH — service instantiation as constructor default

### Tests (3 files)

#### `tests/Unit/Components/Operations/Concurrency/ConcurrencyPublicSurfaceTest.php`

- **Change**: Added `setUp()` / `tearDown()` to wire `BuildConcurrencyRuntime` via `Concurrency::setRuntime()` and reset
  after each test
- **Reason**: Tests relied on old lazy `??= new Build()->build()` pattern which is now a boot-time error

#### `tests/Unit/Framework/V4HealthEndpoints/V4HealthEndpointsTest.php`

#### `tests/Unit/Framework/V4RuntimeApp/CreateApplicationTest.php`

#### `tests/Unit/Framework/V4RuntimeApp/AppTest.php`

- **Change**: Updated `createFactory()` helpers to construct and pass `CreateRequestFromGlobals` with its 8 dependencies

### Tooling (1 file)

#### `tooling/refactor/check-runtime-composition-leaks.php`

- **Rewritten** with:
    - **Severity classification**: HIGH (service leaks) vs MEDIUM (suspicious patterns)
    - **Constructor default detection**: `= new Dependency` in constructor params
    - **Lazy composition detection**: `??= new`, `?? new` patterns
    - **Narrowed Foundation exclude**: Removed broad `/Foundation/` exclude
    - **Per-file known allowances**: Explicit allowances instead of broad excludes
    - **Line text capture**: Records the actual offending line for debugging

## Validation Evidence

### Gate: Runtime Composition Leaks

```
Changed files: 0 violations
Pre-existing violations in other components: 150+ (out of scope for Pass 1)
```

### PHPUnit

```
V4RuntimeApp + V4HealthEndpoints: 31/31 GREEN
Concurrency: 20/20 GREEN (after test wiring fix)
```

### PHPStan (changed framework/component files)

```
framework/System/PublicSurface/App.php: GREEN
framework/System/PublicSurface/Avax.php: GREEN
framework/System/Flows/CreateApplication/CreateApplication.php: GREEN
framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php: GREEN
components/Operations/Events/System/Foundation/EventEmitter.php: GREEN
components/Operations/Events/System/Foundation/GlobalEventListenerState.php: GREEN
components/Operations/Concurrency/System/PublicSurface/Concurrency.php: GREEN
components/Operations/Concurrency/System/Configuration/ConcurrencyServiceProvider.php: GREEN
components/Operations/Concurrency/System/Flows/RaceTasks/RaceTasks.php: GREEN
```

### Autoload

```
composer dump-autoload -o: GREEN (9325 classes)
```

## Risk Assessment

| Change                          | Risk                                                        | Mitigation                         |
|---------------------------------|-------------------------------------------------------------|------------------------------------|
| App constructor change          | LOW — CreateApplication is the only caller, both updated    | Tests pass                         |
| EventEmitter constructor        | LOW — only GlobalEventListenerState and tests call directly | ServiceProvider wires correctly    |
| GlobalEventListenerState throws | MEDIUM — if emitter not wired at boot, error at runtime     | Clear error message, SPI must wire |
| Concurrency runtime throws      | MEDIUM — if SPI not registered, error at runtime            | Clear error message, SPI must wire |
| RaceTasks constructor           | LOW — only Concurrency facade calls                         | Updated in same change             |
| Gate rewrite                    | LOW — tooling only, no production behavior change           | 0 violations in changed files      |

## Remaining Known Leaks (Out of Scope for Pass 1)

These are in `App::handle()` and related paths but are gray-area (value objects / thin wrappers around injected state):

- `App::handle()` — `new OpenHttpRequestScope` / `new CloseHttpRequestScope` (receive data from `RuntimeInterface`, thin
  wrappers)
- `HandleIncomingHttp::handleInCurrentScope()` — same scope objects (delegated from App)
- `MatchHttpRoute::match()` — `new RouteCollection()` (value object, no constructor deps)

These are noted in the gate's `knownAllowances` for explicit tracking.

## Next Actions

Pass 2 (later): Router PublicSurface, Response layer, Builder responsibility, ServiceProvider coverage, Static State
proof.
