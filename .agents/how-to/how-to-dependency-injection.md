# how-to-dependency-injection.md

# AvaX Dependency Injection, ServiceProvider & Fluent API Governance

## 1. Status

This document is mandatory governance for AvaX framework development.

It defines how AvaX uses dependency injection, service providers, autowiring, and fluent API design.

This is not a suggestion.
This is not a style preference.
This is a correctness, maintainability, and architecture rule.

---

## 2. Core Philosophy

```text
Framework = DI.
Every dependency flows through the container.
No cheap code. No hidden instantiation. No scattered responsibilities.
```

AvaX is a dependency injection framework at its core.

Every component, every flow, every capability receives its dependencies through the container.

No class instantiates another class unless it is a composition root, factory, or test.

No method masks missing dependencies with `?? new Fallback()`.

No static call replaces what DI should resolve.

---

## 3. Dependency Injection Law

### 3.1 Constructor Injection — The Default

Constructor injection is the primary mechanism for stable, long-lived dependencies.

```php
final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
        private HashPassword $hashPassword,
        private PublishDomainEvent $publishEvent,
    ) {}
}
```

Rules:

```text
Constructor promotion is mandatory for DI parameters.
final readonly class is the default.
0-4 dependencies: normal.
5-7 dependencies: check responsibility.
8+ dependencies: architecture warning, consider splitting.
```

### 3.2 Method Injection / Autowiring — Runtime Context

Method injection is allowed for request-scoped or runtime-scoped context:

```text
request-scoped dependencies
controller action parameters
command handler context
message consumer context
policy context
current user / session / request
transaction scope
```

```php
public function __invoke(
    #[Body] RegisterRequest $request,
    #[CurrentUser] User|null $user,
    #[Config('security.registration_enabled')] bool $enabled,
): Response {
    // ...
}
```

### 3.3 Method-Level Autowiring — Mandatory for Route Handlers

Route handlers and CLI commands MUST support method-level autowiring.

The container resolves method parameters by type at invocation time.

This is not optional.
This is not "nice to have."
This is how AvaX eliminates `new Class()` in runtime code.

### 3.4 Forbidden

```text
new Class() outside composition root, factory, or test
$this->dependency ?? new Fallback()
static::someMethod() to bypass DI
Container::get() in business logic (allowed in bootstrap only)
service locator pattern masked as DI
nullable dependencies that are not truly optional
hidden static dependencies
god constructors with 8+ dependencies
autowiring that silently resolves to null without diagnostics
```

---

## 4. ServiceProvider Pattern — Mandatory Per Component

### 4.1 The Rule

Every component MUST have exactly one ServiceProvider.

The ServiceProvider is the composition root for that component.

It lives in `System/Configuration/<ComponentName>ServiceProvider.php`.

### 4.2 ServiceProvider Contract

```php
interface ServiceProvider
{
    /**
     * Register all component dependencies in the container.
     *
     * This is the ONLY place where new Class() is allowed for DI registration.
     */
    public function register(Container $container): void;

    /**
     * Execute startup logic after all dependencies are registered.
     *
     * Use this for: event subscriptions, middleware registration,
     * route compilation, cache warming, health checks.
     */
    public function boot(Container $container): void;
}
```

### 4.3 Registration Rules

```text
register() MUST declare all public APIs of the component.
register() MUST use container->singleton() for long-lived services.
register() MUST use container->bind() for request-scoped services.
register() MUST use container->alias() for interface -> concrete mapping.
register() MUST NOT instantiate classes outside container registration.
register() MUST NOT contain business logic.
```

### 4.4 Boot Rules

```text
boot() runs AFTER all ServiceProviders have registered.
boot() MUST NOT register new dependencies.
boot() MAY subscribe to events, register middleware, compile routes.
boot() MUST be idempotent — calling boot() twice must not double-register.
```

### 4.5 Example ServiceProvider

```php
final class HttpServiceProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(ResponseFactoryInterface::class, static fn (Container $c): ResponseFactory =>
            new ResponseFactory(
                defaultCharset: $c->get('http.charset') ?? 'utf-8',
            ),
        );

        $container->singleton(RenderApplicationError::class, static fn (Container $c): RenderApplicationError =>
            new RenderApplicationError(
                responseFactory: $c->get(ResponseFactoryInterface::class),
            ),
        );

        $container->alias(ResponseFactoryInterface::class, ResponseFactory::class);
    }

    public function boot(Container $container): void
    {
        // Subscribe error handler to runtime error events
        // Register middleware pipeline
    }
}
```

### 4.6 ServiceProvider Discovery

The framework boot process MUST:

```text
1. Scan all components for *ServiceProvider.php in System/Configuration/
2. Sort by explicit priority if declared
3. Call register() on each in order
4. Call boot() on each in order
5. Validate all registered bindings resolve
```

### 4.7 ServiceProvider Builder Delegation

A ServiceProvider should stay small.

If a ServiceProvider becomes too large, it may delegate assembly work to classes in `Configuration/Builders/`.

Good:

```php
final readonly class AuthServiceProvider
{
    public function register(Container $container): void
    {
        $this->registerAuthDefaults->register($container);
        $this->buildAuthRuntime->register($container);
    }
}
```

Bad:

```php
final readonly class AuthServiceProvider
{
    public function register(Container $container): void
    {
        // 300 lines of object graph assembly here
    }
}
```

Builder classes may use `new` only when they are creating configuration-time defaults or assembling the object graph.

Allowed:

```php
final readonly class RegisterAuthDefaults
{
    public function register(Container $container): void
    {
        $container->bind(
            PasswordHasher::class,
            static fn (): PasswordHasher => new PasswordHasher(),
        );
    }
}
```

Forbidden:

```php
final readonly class AuthenticateUser
{
    public function authenticate(Credentials $credentials): User
    {
        $hasher = new PasswordHasher(); // runtime DI bypass
    }
}
```

Every non-trivial builder must have behavior tests proving:

* expected bindings are registered
* default dependencies resolve
* user overrides win over defaults
* no runtime execution happens during registration
* no hidden `?? new` fallback remains in runtime code

---

## 5. "Cheap Code" Elimination Law

### 5.1 Definition of Cheap Code

Cheap code is code that bypasses the framework's DI system and manually instantiates dependencies.

Examples of cheap code:

```php
// BAD — direct instantiation in method
public function handle(Request $request): Response
{
    $renderer = new RenderApplicationError(
        responseFactory: $this->responseFactory ?? new ResponseFactory(),
    );
    return $renderer->render($e);
}

// BAD — ?? new Fallback pattern
private function getFactory(): ResponseFactory
{
    return $this->responseFactory ?? new ResponseFactory();
}

// BAD — static call to bypass DI
public function process(): void
{
    $result = CacheHelper::get('key');
}

// BAD — hardcoded dependency
public function handle(): void
{
    $db = new DatabaseConnection($dsn, $user, $password);
    $db->query('...');
}
```

### 5.2 Correct Patterns

```php
// GOOD — DI through constructor
final readonly class ErrorHandler
{
    public function __construct(
        private RenderApplicationError $renderer,
    ) {}

    public function handle(Throwable $e): Response
    {
        return $this->renderer->render($e);
    }
}

// GOOD — ResolveCallable for dynamic invocation
final readonly class RouteDispatcher
{
    public function __construct(
        private ResolveCallable $resolver,
    ) {}

    public function dispatch(string $handlerClass, array $params): mixed
    {
        $handler = $this->resolver->resolve($handlerClass);
        return $handler(...$params);
    }
}

// GOOD — Fluent API for complex operations
Error::response($exception)
    ->withStatus(500)
    ->withHeaders(['X-Correlation-ID' => $request->id()])
    ->render();
```

### 5.3 Allowed Exceptions

`new Class()` is allowed ONLY in:

```text
ServiceProvider register() methods — composition root
Factory classes with explicit responsibility — object creation is their job
Tests — test setup and mock creation
Fluent API terminal methods — when building a value object
Value object private constructors — named constructor pattern
```

Every other `new Class()` in production code is a violation.

---

## 6. Container Ownership Rule

### 6.1 Core Principle

```text
Value objects can be new.
Services must be injected.
Composition roots may assemble.
Runtime execution must only execute.
```

This rule resolves the fundamental question: "When is `new` allowed?"

The answer depends on **what the object is** and **where the code lives**.

### 6.2 When DI/Container is MANDATORY

A class MUST be container-managed when it is:

- a framework or runtime service
- a capability with dependencies
- a class that performs I/O
- a class that owns lifecycle or state
- a class that must be configurable or replaceable
- a class that needs a fake or test implementation
- a class used by runtime execution paths
- a middleware, listener, controller, job, dispatcher, resolver, renderer, factory, provider, store, logger, client,
  repository, transport, cache store, queue broker, filesystem adapter, database connection, or clock
- a class whose implementation may differ by environment
- a class that must be reset or scoped in long-lived runtimes

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
    ) {}

    public function handle(Request $request): Response
    {
        return $this->createHttpResponse->json(['ok' => true]);
    }
}
```

### 6.3 When direct `new` is ALLOWED — Data and Result Objects

A class does NOT need to be container-managed when it is:

- value object
- DTO
- event
- exception
- enum
- simple immutable config object
- message, command, or query object
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

This is allowed because `Response` is the **result**, not the **service**.

### 6.4 Decision Tests

When unsure, apply this test:

```text
Can this class be safely created with `new` anywhere, without configuration,
lifecycle, I/O, state, environment, overrides, mocks, reset, or policy?
```

If yes, direct `new` is fine.

If no, container/DI/ServiceProvider owns it.

Second test:

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

### 6.5 Direct `new` by Path Context — Not by Class Name

Permissions are **path/context-based**, not class-name-based.

Direct instantiation is allowed ONLY in:

```text
ServiceProvider register() methods — composition root
System/Configuration/ — assembly boundary
System/Configuration/Builders/ — configuration-time graph assembly
explicit Factory classes — object creation is their job
test setup (tests/) — test fixture creation
tooling scripts (tooling/) — developer tools
Foundation value objects — named constructor pattern
examples — config/value objects and top-level app assembly only
```

**No broad allowlists by class name.**

Saying "Filesystem is allowed" is wrong.

Saying "Filesystem may be `new` in ServiceProvider, Configuration, Builder, test, or tooling context" is correct.

### 6.6 Factory Class Precision

A factory class may use `new` only when its job is to create a **result**, not to assemble a runtime graph.

**GOOD — factory creates a result:**

```php
final readonly class CreateHttpResponse
{
    public function json(array $data): Response
    {
        return new Response(
            status: 200,
            headers: ['Content-Type' => ['application/json']],
            body: json_encode($data),
        );
    }
}
```

**SUSPICIOUS — factory assembles a graph:**

```php
final readonly class RuntimeFactory
{
    public function create(): Runtime
    {
        return new Runtime(
            router: new Router(),
            dispatcher: new EventDispatcher(),
            logger: new Logger(),
        );
    }
}
```

This second example is actually a `Configuration/Builder` or `ServiceProvider`, not an ordinary factory. It assembles
dependencies, it does not produce a simple result.

### 6.7 Clock Default Binding

`Clock` must come from DI in runtime code.

`new SystemClock()` is allowed ONLY in a ServiceProvider as a default binding:

```php
$container->bind(
    Clock::class,
    static fn (): Clock => new SystemClock(),
);
```

It is NOT allowed in runtime flows, controllers, middleware, listeners, or builders invoked per-request.

### 6.8 Builder Placement Rule

A builder belongs in `System/Configuration/Builders/` only when it assembles **configuration-time** dependency graphs,
runtime packages, default bindings, or component wiring.

A builder belongs in `System/Capabilities/` when it creates **runtime results** or performs runtime behavior.

**Allowed in `Configuration/Builders/`:**

```text
BuildApplicationRuntime
BuildAuthRuntime
AssembleHttpKernel
RegisterAuthDefaults
BuildCacheRuntime
```

**Belongs in `Capabilities/`:**

```text
BuildHttpResponse
BuildGraphQLSchema
BuildOpenApiDocument
BuildUrl
BuildMiddlewarePipeline
BuildSqlQuery
```

**Rule:**

```text
Configuration builders assemble the system.
Capability builders create runtime results.
```

### 6.9 Runtime Composition Leak Rule

Runtime code **MUST NOT** discover or assemble optional capabilities.

**Forbidden in runtime paths:**

```php
class_exists(SomeCapability::class);
new BuildSomething();
new SomeMiddleware(...);
$middleware[] = new SomeMiddleware(...);
$builder->build();
```

Optional capability wiring belongs in `ServiceProvider`, `Configuration`, or `Configuration/Builders`.

**Runtime receives ready objects:**

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

For the full runtime composition law, see:

```text
.agents/how-to/how-to-runtime-composition.md
```

### 6.10 Examples Must Show Canonical Style

Golden path examples must show canonical framework usage, not manual service graph assembly.

Examples may instantiate:

```php
new ApplicationBuilder(...)
new ProjectPath(...)
new EnvironmentName(...)
```

Examples should NOT manually instantiate runtime services:

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

### 6.11 Short Version

```text
Value objects can be new.
Services must be injected.
Composition roots may assemble.
Runtime execution must only execute.

Is it a machine? It comes from DI.
Is it a product? It may be new.
Is it optional? It is registered, not detected.
```

---

## 8. Fluent API Law

### 8.1 The Rule

Complex operations MUST provide fluent, chainable, intent-first APIs.

Call sites must read like human DSL, not internal plumbing.

### 8.2 Fluent API Design

```php
// GOOD — fluent builder
QueryBuilder::table('users')
    ->where('active', true)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// GOOD — fluent error response
Error::response($exception)
    ->withStatus($code)
    ->withHeaders($headers)
    ->render();

// GOOD — fluent queue dispatch
Queue::on('emails')
    ->withDelay(now()->addMinutes(5))
    ->dispatch(new SendWelcomeEmail($user));

// GOOD — fluent transaction
Transaction::begin()
    ->isolation('serializable')
    ->timeout(30)
    ->run(fn () => $this->processOrder($order));
```

### 8.3 Fluent API Rules

```text
Entry point is static factory or DI-resolved instance.
Chain is immutable — each call returns a new builder instance.
Terminal method executes the operation (get(), render(), dispatch(), run()).
Fluent chain MUST NOT have side effects until the terminal method.
Fluent API MUST accept natural inputs — boundary owns normalization.
```

### 8.4 Forbidden Fluent Patterns

```php
// BAD — mutable fluent API
$query = QueryBuilder::table('users');
$query->where('active', true);  // mutates $query
$query->get();

// BAD — no terminal method
$builder = new ErrorRenderer($e);
$builder->setStatus(500);
$builder->setHeaders($headers);
$builder->render();  // not fluent, just setter chain

// BAD — nested construction in call site
ErrorRenderer::fromResponse(
    ResponseFactory::fromPsrResponse($response)
)->render($e);
```

---

## 9. Static Method Rules

### 9.1 Allowed Static Usage

```text
Fluent API entry points: Queue::dispatch(), Error::response(), Transaction::begin()
Value object factories: User::fromArray(), Money::fromCents()
Immutable named constructors: Config::fromFile(), Schema::fromMigration()
Configuration access: DataTransfer::config(), Cache::ttl()
```

### 9.2 Forbidden Static Usage

```text
Mutable shared state without reset capability
Business logic that should be DI-resolved
Side-effect-heavy operations that should be testable
Operations that bypass the container
Facade patterns that hide dependencies
```

### 9.3 Static Mutable State Law

If a component uses static mutable state:

```text
It MUST be resettable via explicit reset() method.
It MUST be documented as using static state.
It MUST be safe for test isolation.
It MUST NOT leak state between requests in long-lived runtimes.
```

---

## 10. Container Resolution Law

### 10.1 ResolveCallable — Primary Callable Resolver

`ResolveCallable` is the primary mechanism for resolving and invoking callables.

```text
If PSR-11 container is available, delegate to it.
If not, fall back to zero-arg new — but this is a YELLOW status, not GREEN.
Reject non-invokable class-strings.
Support method-level autowiring for route handlers.
```

### 10.2 Autowiring Implementation

The container MUST support:

```text
constructor autowiring by type
method parameter autowiring by type
attribute-based resolution (#[Config], #[Body], #[CurrentUser])
config value injection
request DTO injection through #[Body]
current user injection through #[CurrentUser]
policy context injection
interface -> concrete aliasing
fallback to default when dependency is truly optional
```

### 10.3 Resolution Failure Law

```text
Resolution failure MUST produce clear diagnostics at boot, not runtime.
Missing binding MUST report: which class, which parameter, which file, which line.
Circular dependency MUST be detected and reported with full chain.
Non-instantiable class MUST report: is it abstract? interface? missing autoload?
```

---

## 11. Tooling Gates

The following tooling gates enforce this governance:

```bash
php tooling/refactor/check-container-service-locator.php
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-service-provider-coverage.php
```

These gates are mandatory before any stage may be marked GREEN.

### 11.1 check-direct-instantiation.php

Scans all production PHP files for:

```text
new Class() outside constructors
new Class() outside ServiceProvider register()
new Class() outside factory classes
?? new Fallback() patterns
static::method() bypassing DI
Container::get() outside bootstrap
```

Each violation reports:

```text
file path
line number
violation type
severity (BLOCKER / HIGH / MEDIUM)
suggested fix
```

---

## 12. Review Checklist

A DI / ServiceProvider / Fluent API review passes only if:

```text
every component has a ServiceProvider
ServiceProvider register() declares all public APIs
ServiceProvider boot() is idempotent
no new Class() outside composition root / factory / test
no ?? new Fallback() in production code
constructor injection uses promotion
method autowiring works for route handlers
fluent APIs have immutable builders and terminal methods
static methods are only for entry points and value objects
static mutable state is resettable and documented
container resolution failures produce clear diagnostics
tooling gates pass GREEN
no Container::get() in business logic
no service locator pattern in flows or capabilities
```

If any item fails, status is YELLOW or RED.

---

## 13. Migration Strategy

### 13.1 For Existing Code with `new Class()`

```text
Step 1: Identify all new Class() outside constructors
Step 2: Determine if the class is a stable dependency or dynamic callable
Step 3: For stable dependencies — add to constructor injection
Step 4: For dynamic callables — use ResolveCallable
Step 5: Add ServiceProvider registration for the component
Step 6: Remove ?? new Fallback() — register fallback in ServiceProvider
Step 7: Run tests and validation
```

### 13.2 For Existing Code with `?? new Fallback()`

```text
Step 1: Identify all ?? new Fallback() patterns
Step 2: Register the fallback as a default binding in ServiceProvider
Step 3: Inject the dependency through constructor
Step 4: Remove the null-coalescing fallback
Step 5: Verify tests pass
```

### 13.3 For Existing Code with Static Calls

```text
Step 1: Identify static calls that bypass DI
Step 2: Determine if it is a valid static entry point (fluent API, value object)
Step 3: If not valid — convert to DI-resolved instance
Step 4: Add to ServiceProvider if needed
Step 5: Update call sites to use injected dependency
```

---

## 14. Final Law

```text
Framework = DI.
ServiceProvider = composition root per component.
new Class() = forbidden outside composition root.
?? new Fallback() = missing DI configuration.
Static = entry points and value objects only.
Fluent API = intent-first call sites.
Autowiring = mandatory for route handlers.
Container = the framework's heart.
Evidence = validation proves DI discipline.
```

No claim of GREEN may be made without:

```text
check-direct-instantiation.php GREEN
check-container-service-locator.php GREEN
every component has ServiceProvider
all tests pass through DI-resolved dependencies
```
