# How to Runtime Composition

## Status

**MANDATORY** — This document defines non-negotiable runtime composition rules for the project.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

A rule without an explicit exception **MUST** be treated as mandatory.

Code review **MUST NOT** mark a scope GREEN when a mandatory rule is violated.

---

## 1. Runtime Composition Leak Law

### 1.1 Definition

```text
A Runtime Composition Leak occurs when code in a runtime execution context
discovers, assembles, instantiates, or conditionally wires infrastructure
dependencies that should have been assembled at registration time.
```

Runtime execution code must not know about builders, factories, middleware assembly, or conditional capability
detection. It receives ready-to-execute dependencies and executes.

### 1.2 The Core Principle

```text
Runtime execution must only execute.
Composition roots must assemble.
Configuration must register.
```

If a method handles a request, dispatches an event, processes a job, or serves a query, it is runtime execution. It must
not also decide which infrastructure classes exist, create them, wire them together, or conditionally include them.

### 1.3 Anatomy of a Leak

A runtime composition leak has three parts:

```text
1. Discovery:     class_exists(SomeBuilder::class)
2. Assembly:      $builder = new BuildSomething()
3. Wiring:        $middleware[] = new SomeMiddleware($builder->build())
```

All three parts belong in `System/Configuration/` or a `ServiceProvider`. None belong in runtime execution code.

---

## 2. Forbidden Patterns

### 2.1 class_exists() gating

**Status:** MANDATORY
**Scope:** Flows, Capabilities runtime execution, PublicSurface
**Severity:** BLOCKER

Runtime code **MUST NOT** use `class_exists()` to decide whether to include, instantiate, or wire a dependency.

**BAD:**

```php
if (class_exists(BuildFailureBoundary::class)
    && class_exists(HttpFailureBoundaryMiddleware::class)) {
    $middleware[] = new HttpFailureBoundaryMiddleware(...);
}
```

**GOOD:**

```php
// ServiceProvider registers the middleware unconditionally.
// If the component is not enabled, the ServiceProvider is not loaded.
$container->tag(HttpFailureBoundaryMiddleware::class, HttpMiddleware::class);
```

### 2.2 new Build* in runtime code

**Status:** MANDATORY
**Scope:** Flows, Capabilities, PublicSurface
**Severity:** BLOCKER

Builder classes **MUST NOT** be instantiated inside runtime execution code.

**BAD:**

```php
$fbBuilder = new BuildFailureBoundary();
$boundary = $fbBuilder->build();
```

**GOOD:**

```php
final readonly class HandleIncomingHttp
{
    public function __construct(
        private FailureBoundary $failureBoundary,
    ) {}
}
```

### 2.3 new *Middleware, *Handler, *Dispatcher, *Resolver

**Status:** MANDATORY
**Scope:** Flows, Capabilities, PublicSurface
**Severity:** HIGH

Infrastructure classes **MUST NOT** be directly instantiated inside runtime execution code.

**BAD:**

```php
return new SessionLifecycleMiddleware(new NullSession());
```

**GOOD:**

```php
final readonly class AppKernel
{
    public function __construct(
        private HttpMiddlewareStack $middlewareStack,
    ) {}
}
```

### 2.4 ->build() in runtime execution

**Status:** MANDATORY
**Scope:** Flows, Capabilities runtime, PublicSurface
**Severity:** HIGH

Builder `build()` calls **MUST NOT** occur inside runtime execution code.

**BAD:**

```php
$runtime = $this->builder->build($config);
```

**GOOD:**

```php
final readonly class RunConcurrentTasks
{
    public function __construct(
        private TaskRuntimeInterface $runtime,
    ) {}
}
```

### 2.5 $middleware[], $pipeline[] construction

**Status:** MANDATORY
**Scope:** Flows, Capabilities, PublicSurface
**Severity:** HIGH

Middleware stacks and pipelines **MUST NOT** be assembled at runtime by pushing new instances into arrays.

**BAD:**

```php
$middleware = [];
$middleware[] = new SessionMiddleware();
$middleware[] = new AuthMiddleware();
```

**GOOD:**

```php
// MiddlewareStack is built once in Configuration and injected.
final readonly class AppKernel
{
    public function __construct(
        private HttpMiddlewareStack $middlewareStack,
    ) {}
}
```

### 2.6 ?? new fallback

**Status:** MANDATORY
**Scope:** All runtime code
**Severity:** BLOCKER

Null-coalescing fallback to `new` **MUST NOT** be used in runtime code.

**BAD:**

```php
$this->handleIncomingHttp ??= new HandleIncomingHttp(...);
```

**GOOD:**

```php
// Register a default in ServiceProvider.
$container->bind(HandleIncomingHttp::class, static fn () => new HandleIncomingHttp(...));
```

### 2.7 = new ClassName() default parameter

**Status:** MANDATORY
**Scope:** Flows, Capabilities, PublicSurface
**Severity:** HIGH

Constructor default parameter values that instantiate objects **MUST NOT** be used in runtime code.

**BAD:**

```php
public function __construct(
    private BuildConcurrencyRuntime $builder = new BuildConcurrencyRuntime(),
) {}
```

**GOOD:**

```php
public function __construct(
    private BuildConcurrencyRuntime $builder,
) {}
```

---

## 3. Forbidden Contexts

Runtime composition leaks are **FORBIDDEN** in:

```text
framework/System/Flows/
framework/System/Capabilities/ (runtime execution methods)
framework/System/PublicSurface/
components/**/System/Flows/
components/**/System/Capabilities/ (runtime execution methods)
components/**/System/PublicSurface/
```

Specifically forbidden execution paths:

```text
request handling
middleware execution
controller dispatch
event dispatch
queue job execution
database query execution
failure handling
worker loop iteration
```

---

## 4. Allowed Contexts

Direct instantiation, builder assembly, and conditional wiring are **ALLOWED** in:

```text
System/Configuration/
System/Configuration/Builders/
ServiceProvider classes
explicit Factory classes
test setup (tests/)
tooling scripts (tooling/)
Foundation value objects (named constructors)
event classes (data objects only)
exception classes
DTO classes
composition roots (Application::create, CreateApplication)
```

A composition root is the highest-level entry point that wires the entire application. It is the only place where the
full object graph is assembled.

---

## 5. Refactor Pattern

### 5.1 Runtime receives assembled dependencies

When a runtime class needs infrastructure, it declares the dependency in its constructor. It does not create it.

**Before (runtime assembly):**

```php
final readonly class HandleIncomingHttp
{
    public function handle(Request $request): Response
    {
        $boundary = (new BuildFailureBoundary())->build();
        $middleware = new HttpFailureBoundaryMiddleware($boundary);

        return $middleware->handle($request, $next);
    }
}
```

**After (DI):**

```php
final readonly class HandleIncomingHttp
{
    public function __construct(
        private HttpMiddlewareStack $middlewareStack,
    ) {}

    public function handle(Request $request): Response
    {
        return $this->middlewareStack->handle($request);
    }
}
```

### 5.2 Optional capability goes to registration time, not runtime time

If a capability is optional, it is registered or not at registration time. Runtime does not check.

**BAD (runtime detection):**

```php
if (class_exists(BuildFailureBoundary::class)) {
    $middleware[] = new HttpFailureBoundaryMiddleware(...);
}
```

**GOOD (registration-time decision):**

```php
final readonly class RegisterHttpFailureBoundaryMiddleware
{
    public function register(Container $container, ComponentRegistry $components): void
    {
        if (! $components->has('FailureBoundary')) {
            return;
        }

        $container->bind(
            HttpFailureBoundaryMiddleware::class,
            static fn (Container $container): HttpFailureBoundaryMiddleware => new HttpFailureBoundaryMiddleware(
                failureBoundary: $container->get(FailureBoundary::class),
            ),
        );

        $container->tag(
            id: HttpFailureBoundaryMiddleware::class,
            tag: HttpMiddleware::class,
            priority: 1000,
        );
    }
}
```

This is in `Configuration/`, not in a runtime flow.

### 5.3 Middleware stack is built once

The middleware stack is assembled once during boot, not per request.

```text
System/Configuration/Builders/BuildHttpMiddlewareStack.php
    → reads tagged middleware from container
    → builds HttpMiddlewareStack
    → HttpMiddlewareStack is bound as singleton
    → HandleIncomingHttp receives HttpMiddlewareStack
```

### 5.4 Constructor injection over default new

Every dependency a runtime class needs must be declared in its constructor. No defaults that instantiate.

**BAD:**

```php
public function __construct(
    private LoggerInterface $logger = new FileLogger(),
) {}
```

**GOOD:**

```php
public function __construct(
    private LoggerInterface $logger,
) {}
```

ServiceProvider registers the default:

```php
$container->bind(LoggerInterface::class, static fn () => new FileLogger());
```

---

## 6. Container Ownership Rule

### 6.1 Container is mandatory for runtime services

A class MUST be container-managed when it is:

- a framework/runtime service
- a capability with dependencies
- a class that performs I/O
- a class that owns lifecycle/state
- a class that must be configurable or replaceable
- a class that may need a fake/test implementation
- a class used by runtime execution paths
- a middleware, listener, controller, job, dispatcher, resolver, renderer, factory, provider, store, logger, client,
  repository, transport, cache store, queue broker, filesystem adapter, database connection, or clock
- a class whose implementation may differ by environment
- a class that must be reset/scoped in long-lived runtimes

Examples that MUST come from DI/container/ServiceProvider:

```text
Filesystem
ResponseFactory / CreateHttpResponse
RunDoctor
HandleIncomingHttp
EventDispatcher
ResolveCallable
QueryOrchestrator
EntityPersister
Logger
Clock
HttpMiddlewareStack
HttpFailureBoundaryMiddleware
FailureBoundary
CacheStore
QueueBroker
DatabaseConnection
ControllerResolver
```

These must not be manually created inside runtime methods.

**BAD:**

```php
public function handle(Request $request): Response
{
    $responseFactory = new ResponseFactory();

    return $responseFactory->json(['ok' => true]);
}
```

**GOOD:**

```php
final readonly class HandleIncomingHttp
{
    public function __construct(
        private CreateHttpResponse $createHttpResponse,
    ) {
    }

    public function handle(Request $request): Response
    {
        return $this->createHttpResponse->json(['ok' => true]);
    }
}
```

---

### 6.2 Container is NOT required for data/result objects

A class does NOT need to be container-managed when it is:

- value object
- DTO
- event
- exception
- enum
- simple immutable config object
- message/command/query object
- result object
- collection object
- specification object without I/O
- temporary local object with no dependencies
- concrete output produced by a service

Examples that may be created directly:

```php
new UserRegistered(...)
new ProjectPath(...)
new EnvironmentName(...)
new Response(...)
new HeaderBag(...)
new CacheKey(...)
new RuntimeException(...)
new ValidationError(...)
new AuthOptions(...)
new RegisteredUserView(...)
```

**GOOD:**

```php
return new Response(
    status: 200,
    headers: ['Content-Type' => 'application/json'],
    body: $json,
);
```

This is allowed because `Response` is the result, not the service.

---

### 6.3 Direct `new` is allowed in composition roots

Direct instantiation is allowed inside:

- `ServiceProvider`
- `System/Configuration`
- `System/Configuration/Builders`
- explicit factory classes
- test setup
- tooling scripts
- examples (for config/value objects and top-level app assembly only)

**GOOD:**

```php
final readonly class ResponseServiceProvider
{
    public function register(Container $container): void
    {
        $container->bind(
            CreateHttpResponse::class,
            static fn (): CreateHttpResponse => new CreateHttpResponse(),
        );
    }
}
```

This is allowed because the provider is an assembly boundary.

But this is still **BAD** even in a provider:

```php
$container->bind(
    SomeRuntimeService::class,
    static fn (Container $container): SomeRuntimeService => new SomeRuntimeService(
        filesystem: $container->has(Filesystem::class)
            ? $container->get(Filesystem::class)
            : new Filesystem(),
    ),
);
```

**BETTER:**

```php
$container->bind(
    Filesystem::class,
    static fn (): Filesystem => new Filesystem(),
);

$container->bind(
    SomeRuntimeService::class,
    static fn (Container $container): SomeRuntimeService => new SomeRuntimeService(
        filesystem: $container->get(Filesystem::class),
    ),
);
```

Even in a provider, avoid hidden fallback construction. Register defaults explicitly.

---

### 6.4 Runtime execution must not assemble dependencies

Runtime execution code must not:

- call `class_exists()` to decide runtime wiring
- call `new Service()`
- call `new Middleware()`
- call `new Builder()->build()`
- push `new Middleware()` into arrays
- use `$dependency ?? new Dependency()`
- use constructor defaults like `private Foo $foo = new Foo()`

**BAD:**

```php
if (class_exists(BuildFailureBoundary::class)) {
    $builder = new BuildFailureBoundary();

    $middleware[] = new HttpFailureBoundaryMiddleware(
        $builder->build(),
    );
}
```

**GOOD:**

```php
final readonly class HandleIncomingHttp
{
    public function __construct(
        private HttpMiddlewareStack $middlewareStack,
    ) {
    }

    public function handle(Request $request): Response
    {
        return $this->middlewareStack->handle($request);
    }
}
```

The middleware stack must be assembled before runtime execution.

---

### 6.5 Optional dependencies must be registered, not guessed

**BAD:**

```php
$this->clock ?? new SystemClock()
```

**GOOD:**

```php
$container->bind(
    Clock::class,
    static fn (): Clock => new SystemClock(),
);
```

Then all runtime services use:

```php
$container->get(Clock::class)
```

Rule:

```text
Optional default does not mean hidden fallback.
Optional default means explicit default binding.
```

---

### 6.6 Examples must show canonical style

Examples may instantiate:

```php
new ApplicationBuilder(...)
new ProjectPath(...)
new EnvironmentName(...)
```

Examples should not manually instantiate runtime services:

```php
new RunDoctor()
new HandleIncomingHttp(...)
new ResponseFactory()
new Filesystem()
```

**BETTER:**

```php
return ApplicationBuilder::forProject(
    projectPath: $projectPath,
    environmentName: $environmentName,
)
    ->withDefaultRuntimeServices()
    ->withHttpRoutes(__DIR__ . '/config/routes.php');
```

Examples teach future AI and humans. They must not show cheap assembly patterns.

---

### 6.7 Decision test

When unsure, ask this:

```text
Can this class be safely created with `new` anywhere, without configuration,
lifecycle, I/O, state, environment, overrides, mocks, reset, or policy?
```

If yes, direct `new` is fine.

If no, container/DI/ServiceProvider owns it.

Another test:

```text
Is this object the thing being produced, or the machine that produces things?
```

Produced thing (allowed):

```php
new Response(...)
new UserRegistered(...)
new CacheKey(...)
```

Machine (DI/container):

```php
new ResponseFactory()
new EventDispatcher()
new Filesystem()
new Logger()
new QueryOrchestrator()
```

---

### 6.8 Short version

```text
Value objects can be new.
Services must be injected.
Composition roots may assemble.
Runtime execution must only execute.
```

---

## 7. Static Facade Law

### 7.1 When static facades are acceptable

Static facades **MAY** exist as convenience entry points, but they must not perform assembly at runtime.

**YELLOW (acceptable with documentation):**

```php
final class FailureBoundary
{
    private static ?RunProtectedAction $instance = null;

    public static function getInstance(): RunProtectedAction
    {
        if (self::$instance === null) {
            self::$instance = (new BuildFailureBoundary())->build();
        }
        return self::$instance;
    }

    public static function setInstance(RunProtectedAction $instance): void
    {
        self::$instance = $instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
```

This is YELLOW because:

- `setInstance()` exists for test injection
- `reset()` exists for long-lived runtime safety
- Assembly is a single deterministic call
- Long-term goal: eliminate facade in favor of DI

### 7.2 Static mutable state rules

Static state **MUST**:

- be documented with PHPDoc explaining lifecycle
- provide `reset()` for worker safety
- provide `setInstance()` for test injection
- never be used as a hidden global cache for runtime behavior

### 7.3 Static facade assembly MUST go through Configuration

The assembly that a static facade performs **SHOULD** be moved to `Configuration/Builders/`. The facade becomes a thin
proxy.

### 7.4 DI-preferred path

Static facades are a compatibility layer. New code **SHOULD** use DI. Existing facades **SHOULD** be gradually replaced
with injected dependencies.

### 7.5 Component Registration Model

#### 7.5.1 ServiceProvider Per Real Component Boundary

Every real component boundary has a dedicated ServiceProvider.

The ServiceProvider does not execute behavior. It registers and wires.

#### 7.5.2 Register* Composition Actions

Each subsystem and capability has its own `Register*` class in `Configuration/`.

```text
Component ServiceProvider
  -> RegisterStorageRuntime
  -> RegisterStorageDrivers
  -> VerifyStorageConfiguration
  -> StorageRuntime
  -> StorageDriverCatalog
```

For Identity:

```text
IdentityServiceProvider
  -> RegisterAuthRuntime
  -> RegisterAccessRuntime
  -> RegisterCredentialsRuntime
  -> RegisterExternalIdentityRuntime
  -> RegisterTokensRuntime
  -> RegisterTenancyRuntime
```

#### 7.5.3 Catalog/Registry for String Selectors

String selectors (driver names, store names, channel names) are owned by an explicit Catalog or Registry class.

The Catalog:
- holds the map of selector name to registered descriptor/driver
- validates selectors at runtime and fails fast on unknown names
- provides clear error messages listing available options
- is built during registration, not discovered at runtime

The Container is **NOT** the semantic selector registry.
The Container builds and resolves the object graph.
The Catalog owns selector names and validation.

#### 7.5.4 Container as Object Graph Owner

```text
Container builds and verifies the object graph.
Catalog/Registry owns selector names.
Runtime object executes behavior.
PublicSurface/facade receives and delegates.
```

The Container must not be used as a string-key lookup for runtime selectors.
Binding `storage.driver.s3` in the container is not the same as registering `s3` in the StorageDriverCatalog.
The former is container wiring; the latter is semantic selector validation.

#### 7.5.5 PublicSurface/Facade Boundary Must Not Perform Service Location

PublicSurface classes and static facades **MUST NOT** call `app()`, `container()`, `resolve()`, `make()`, or `$container->get()`.

Allowed delegation targets:
- injected runtime object (preferred, instance-based)
- official boot-configured facade bridge (set during registration, not via lookup)
- generated/compiled runtime accessor approved by runtime composition rules

See also:
```text
.agents/how-to/modeling/how-to-model-flows.md — §12.1 Anti-Service-Locator DX Rule
```

---

## 8. Ledger Classification System

Every identified runtime composition leak is classified:

```text
FIXED_NOW             — fixed in current pass
PROVEN_SAFE           — documented exception with justification
CONFIGURATION_ALLOWED — occurs in allowed Configuration/ context
FACTORY_ALLOWED       — occurs in explicit Factory class
TEST_ONLY             — occurs only in test setup
TOOLING_ALLOWED       — occurs only in tooling scripts
ROADMAP_NOT_ACTIVE    — component is not yet active
BLOCKER_FOR_V5_9      — must be fixed before V5.9 GREEN
```

### Ledger table format

| File               | Pattern                                     | Runtime or Configuration? | Classification   | Fix                        | Gate catches it? |
|--------------------|---------------------------------------------|---------------------------|------------------|----------------------------|------------------|
| `AppKernel.php:83` | `class_exists(BuildFailureBoundary::class)` | Runtime                   | BLOCKER_FOR_V5_9 | Extract to ServiceProvider | Yes              |

---

## 9. Tooling Gate Requirements

### 9.0 Related Governance

The Container Ownership Rule, which defines when classes must be DI-managed versus when direct `new` is allowed, is
defined in:

```text
.agents/how-to/implementation/how-to-dependency-injection.md — Section 6: Container Ownership Rule
```

That document includes:

- Builder placement rule (Configuration/Builders vs Capabilities)
- Factory class precision rule (result vs graph assembly)
- Clock default binding rule
- Path/context-based enforcement (not class-name-based)
- Examples canonical style rule

This document focuses on runtime composition leaks specifically.
The DI document defines the broader ownership law.

### 9.1 Detection tool

```bash
run the configured runtime-composition leak checker
```

The tool scans production runtime folders and detects:

```text
class_exists(
new Build
new .*Middleware
new .*Handler
new .*Dispatcher
new .*Resolver
new .*Factory
->build()
$middleware[]
$pipeline[]
?? new
= new ClassName()
```

The tool is context-aware:

- Ignores `Configuration/`, `Builders/`, `ServiceProvider`, `Factory` classes
- Ignores `tests/`, `tooling/`, `Foundation/` VOs
- Ignores events, exceptions, DTOs
- Reports each violation with file, line, and pattern description

### 9.2 Integration with existing gates

This tool runs alongside existing gates:

```bash
run the configured runtime-leak checker
run the configured component-shape checker
run the configured public-boundary checker
```

### 9.3 CI enforcement

The tool **MUST** exit with code 0 on PASS, 1 on FAIL. CI **MUST** fail on any runtime composition leak.

---

## 10. Review Checklist

When reviewing code that touches runtime execution, verify:

- [ ] No `class_exists()` for runtime wiring decisions
- [ ] No `new Builder()` or `->build()` in runtime methods
- [ ] No `new Middleware()`, `new Handler()`, `new Dispatcher()` in runtime methods
- [ ] No `$middleware[]` or `$pipeline[]` construction in runtime methods
- [ ] No `?? new` fallback in runtime methods
- [ ] No `= new ClassName()` constructor defaults in runtime classes
- [ ] All infrastructure dependencies are injected via constructor
- [ ] Optional capabilities are registered in ServiceProvider, not detected at runtime
- [ ] Static facades (if any) have `reset()` and `setInstance()`
- [ ] No `app()`, `container()`, `resolve()`, `make()`, or `$container->get()` in PublicSurface
- [ ] Static facades do not perform ad-hoc runtime service lookup
- [ ] String selectors are validated through Catalog/Registry, not container keys
- [ ] Each component has a ServiceProvider with Register* composition actions

---

## Completion Language

### Stage Completion Language

A runtime composition cleanup **MUST NOT** be marked GREEN unless:

- [ ] `check-runtime-composition-leaks.php` returns PASS
- [ ] No `class_exists()` remains in runtime execution paths
- [ ] No `new Builder()` or `->build()` remains in runtime execution paths
- [ ] No middleware is instantiated in runtime execution paths
- [ ] No `$middleware[]` or `$pipeline[]` construction remains in runtime execution paths
- [ ] All constructor defaults with `= new` are replaced with required parameters
- [ ] All `?? new` fallbacks are replaced with ServiceProvider defaults
- [ ] Static facades have `reset()` and `setInstance()` if they retain lazy assembly

A stage **MUST** be YELLOW if:

- [ ] Static facades retain lazy assembly but have `reset()` and `setInstance()`
- [ ] Minor documentation gaps remain in ledger

A stage **MUST** be RED if:

- [ ] Any runtime composition leak remains unclassified
- [ ] Gate is missing or passes only by broad allowlist
- [ ] Middleware is instantiated in request handling path

---

## 11. Runtime Service Locator Prohibition

### 11.1 Definition

A Service Locator is any pattern where a runtime class requests its own dependencies from a container or registry at
runtime.

### 11.2 Mandatory DI

Runtime classes MUST use constructor injection.

- **FORBIDDEN**: `$container->get(...)` inside a Flow or Capability.
- **FORBIDDEN**: `Application::get(...)` inside a Flow or Capability.
- **FORBIDDEN**: Passing the container itself as a dependency to a runtime class.

**Status:** MANDATORY  
**Severity:** BLOCKER

---

## 12. Non-Negotiable Composition Evidence

### 12.1 Composition Report

Every component commit MUST prove that all runtime dependencies are correctly bound.

### 12.2 Zero-Scan Gate Rule

A composition scan that reports 0 findings is UNPROVEN unless the scanner confirms the count of files scanned.

**Status:** MANDATORY  
**Severity:** BLOCKER

---

## 13. Builder Standards and Prohibitions

### 13.1 Builder Responsibility

A Builder in `Configuration/Builders/` MUST own exactly one assembly graph.

### 13.2 Builder Dumping Ground Prohibition

Builders MUST NOT become generic technical categories.

- **FORBIDDEN**: `CommonBuilder.php`
- **FORBIDDEN**: `InternalBuilder.php`
- **FORBIDDEN**: `GeneralConfiguration.php`

### 13.3 Static Construction in Builders

Builders SHOULD use static methods for construction to avoid state leaks in warm runtimes.

**Status:** MANDATORY  
**Severity:** HIGH

---

## Final Runtime Composition Law

```text
Runtime execution must only execute.
Composition roots must assemble.
Configuration must register.

Value objects can be new.
Services must be injected.
Optional default means explicit default binding.
Runtime must not guess.

No class_exists in runtime.
No new Builder in runtime.
No new Middleware in runtime.
No ->build() in runtime.
No $middleware[] in runtime.
No ?? new in runtime.
No = new ClassName() in runtime.

If it is a machine, it comes from DI.
If it is a product, it may be new.
If it is optional, it is registered, not detected.

Runtime receives ready pipeline.
```

---

## 14. Root Application Container Rule

The framework MUST use one canonical root Application Container as the runtime object graph owner.

---

## 13.5 Separation of Concern in Runtime Composition

Runtime composition must respect separation of concerns.

Runtime code must not know about:

- other concerns it does not own
- cross-cutting logic that should be explicit dependencies
- sibling concerns that create hidden coupling

Composition concerns belong in Configuration/Assembly.
Execution concerns belong in Flows/Capabilities.
Mixing them in runtime code is a SoC violation.

For Separation of Concern governance, see:

```text
.agents/how-to/architecture/how-to-architecture.md — Section 57
.agents/how-to/components/how-to-design-components.md — Section 32
```

---

## 14. Root Application Container Rule

```text
.agents/how-to/implementation/how-to-dependency-injection.md
```

**Short version:**

```text
Component is not a container.
Component owns registrations.
Application container owns the graph.
Runtime scope owns lifecycle state.
```

---

## 15. Universal Enterprise Codecraft Cross-Reference

**Status:** MANDATORY  
**Severity:** BLOCKER

Runtime composition must serve the universal enterprise codecraft philosophy.

See:

- `how-to-design-components.md` — Section 30: Universal Enterprise Codecraft Rule
- `how-to-architecture.md` — Section 55: Universal Enterprise Codecraft Rule

Relevant principles for runtime composition:

- **No Technical Theater:** Runtime must not introduce unnecessary builders, factories, or wiring layers. Composition belongs in Configuration.
- **Structural Honesty:** Runtime composition must reveal real dependency boundaries. Never hide machinery behind `app()` shortcuts or global state.
- **Cognitive Load:** Runtime code should execute, not assemble. If a runtime flow assembles its own dependencies, the design is suspicious.
- **Fluent API:** Runtime entrypoints should feel fluent. `$runtime->handle($request)` not `$runtime->execute(Factory::create(Builder::build(...)))`.
