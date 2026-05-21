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
new Class() for services outside approved composition context
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

## 3.5 Approved Composition Contexts Rule

Direct `new` for services is allowed only inside approved composition contexts.

**Approved composition contexts:**

```text
ServiceProvider register()
ServiceProvider boot(), only for boot-time wiring objects and only when idempotent
System/Configuration/
System/Configuration/Builders/
explicit graph assembly classes
explicit factories, but only for produced result objects
tests
tooling
migration/recovery scripts when not active runtime
examples, only for value/config objects and top-level application configuration DSL
```

**Runtime execution code MUST NOT create services.**

Runtime execution includes:

```text
Flows executing application behavior
Capabilities executing runtime behavior
PublicSurface behavior
request handling
middleware execution
controller dispatch
event dispatch
listener execution
queue/job execution
database/query execution
failure handling
worker loop execution
runtime kernel execution
```

**Short law:**

```text
Value objects can be new.
Services must be injected.
Composition roots may assemble.
Runtime execution must only execute.
```

---

## 3.6 Optional Dependency Binding Rule

Optional default does not mean hidden fallback.

Optional default means explicit default binding.

**BAD:**

```php
$this->clock ?? new SystemClock()
```

**BAD even inside a ServiceProvider closure:**

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

**GOOD:**

```php
$container->bind(
    Clock::class,
    static fn (): Clock => new SystemClock(),
);

$container->bind(
    Filesystem::class,
    static fn (): Filesystem => new Filesystem(),
);

$container->bind(
    SomeRuntimeService::class,
    static fn (Container $container): SomeRuntimeService => new SomeRuntimeService(
        clock: $container->get(Clock::class),
        filesystem: $container->get(Filesystem::class),
    ),
);
```

**Rules:**

- MUST register explicit default bindings first.
- MUST resolve dependencies from the container after that.

---

## 3.7 Missing dependency failure belongs to container compile/verify/boot

Required dependencies MUST fail during container compile/verify/boot.

Runtime/business code MUST NOT contain missing-dependency checks for framework services.

Forbidden in runtime/business code:

- `if ($this->filesystem === null) throw ...`
- `if (! $container->has(...)) throw ...`
- `$dependency ?? new Dependency()`
- `$container->has(...) ? $container->get(...) : new Dependency()`

If a dependency is required, constructor injection must require it.

If a default exists, it must be registered as an explicit default binding.

If no binding exists, container verification must fail before runtime execution starts.

**Short version:**

```text
Dependency failure belongs to boot.
Business code belongs to business.
Runtime must never discover that the app was badly assembled.
```

---

## 4. ServiceProvider Pattern — Per Active Component

### 4.0 Active Component ServiceProvider Rule

Every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I/O,
configuration, or lifecycle ownership MUST have exactly one real ServiceProvider.

A real ServiceProvider MUST register at least one of:

- component public API entrypoints
- runtime services
- default infrastructure bindings
- component configuration objects
- aliases/contracts
- capability implementations
- factories for produced result objects
- boot-time wiring hooks
- health/doctor checks
- resettable state hooks

The following component statuses are exempt from mandatory ServiceProvider creation:

- ROADMAP
- SCAFFOLD
- LABS_ONLY
- EVIDENCE_ONLY
- TEST_ONLY
- PURE_FOUNDATION
- DEPRECATED, if inactive and classified

Exempt components MUST be classified in component-status-lock or current governance evidence.

**Empty ServiceProvider shells are FORBIDDEN.**

A ServiceProvider that registers nothing, only exists to satisfy a gate, or contains TODO-only behavior is a governance
violation.

**Status:** MANDATORY  
**Severity:** BLOCKER

### 4.1 Root Application Container + FrameworkBootstrapServiceProvider Rule

The framework MUST have a dedicated `FrameworkBootstrapServiceProvider` located in `framework/System/Configuration/`.

This provider is responsible for the earliest bootstrap phase and MUST:

1. Bind the `RootApplicationContainer` itself into the container.
2. Use the canonical alias `avax.container` for the root container.
3. Ensure the container is available as a service for approved composition contexts.

**Status:** MANDATORY  
**Severity:** BLOCKER

### 4.2 The Rule

Every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I/O,
configuration, or lifecycle ownership MUST have exactly one real ServiceProvider.

The ServiceProvider is the composition root for that component.

It lives in `System/Configuration/<ComponentName>ServiceProvider.php`.

Exempt statuses: ROADMAP, SCAFFOLD, LABS_ONLY, EVIDENCE_ONLY, TEST_ONLY, PURE_FOUNDATION, inactive DEPRECATED.

### 4.3 ServiceProvider Contract

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

### 4.4 Registration Rules

```text
register() MUST declare all public APIs of the component.
register() MUST use container->singleton() for long-lived services.
register() MUST use container->bind() for request-scoped services.
register() MUST use container->alias() for interface -> concrete mapping.
register() MUST NOT instantiate classes outside container registration.
register() MUST NOT contain business logic.
```

### 4.5 Boot Rules

```text
boot() runs AFTER all ServiceProviders have registered.
boot() MUST NOT register new dependencies.
boot() MAY subscribe to events, register middleware, compile routes.
boot() MUST be idempotent — calling boot() twice must not double-register.
boot() MUST be deterministic.
boot() MUST avoid runtime execution side effects.
boot() MUST NOT run destructive operations.
boot() MUST NOT run expensive or external health checks unless explicitly configured.
boot() MUST NOT process real requests, jobs, or events.
boot() MUST NOT mutate request-scoped state.
```

**ServiceProvider boot() Rule:**

register() binds dependencies.

boot() wires already registered systems.

boot() **MAY**:

- subscribe events
- register middleware declarations
- register routes
- register health check declarations
- register cache warmers
- register lifecycle hooks
- connect facade/static/public-surface bridges
- finalize component wiring

boot() **MUST**:

- be idempotent
- be deterministic
- avoid runtime execution side effects
- not register new dependencies that should be in register()
- not run destructive operations
- not run expensive or external health checks unless explicitly configured
- not process real requests/jobs/events
- not mutate request-scoped state

**Health/doctor rule:**

boot() MAY register health checks.
boot() MUST NOT execute expensive/destructive/external health checks during normal boot unless explicitly configured.

### 4.6 Example ServiceProvider

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

### 4.7 ServiceProvider Discovery

The framework boot process MUST:

```text
1. Scan all components for *ServiceProvider.php in System/Configuration/
2. Sort by explicit priority if declared
3. Call register() on each in order
4. Call boot() on each in order
5. Validate all registered bindings resolve
```

### 4.8 ServiceProvider Builder Delegation

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

`new Class()` is allowed ONLY in approved composition contexts.

See **Section 3.5: Approved Composition Contexts Rule** for the full list.

Every other `new Class()` in production code is a violation.

---

## 6. Container Lifetime and Scope Taxonomy

AvaX uses a strict taxonomy for container object lifetimes and scopes.
Bindings MUST explicitly declare their intended scope.

### 7.1 Singleton Scope

- **Meaning**: One instance per application lifetime.
- **Use when**: Stateless services, immutable config, thread-safe global capabilities.
- **Worker Safety**: Must be stateless or have explicit `reset()` for worker loops.

### 7.2 Scoped (Request/Job) Scope

- **Meaning**: One instance per execution unit (HTTP Request, CLI Command, Queue Job).
- **Use when**: Request-specific state, session, current user, database transaction.
- **Worker Safety**: MUST be cleared or recreated at the start of each execution loop.

### 7.3 Transient Scope

- **Meaning**: New instance for every resolution.
- **Use when**: Short-lived builders, lightweight strategy objects, non-reusable stateful units.

### 7.4 Tenant Scope (SaaS)

- **Meaning**: One instance per tenant identifier.
- **Use when**: Tenant-specific configuration, isolated storage adapters.

### 7.5 Test/Mock Scope

- **Meaning**: Instance valid only during test execution.
- **Use when**: Fakes, mocks, or specialized test-only infrastructure.

**Rule:** Every binding in a `ServiceProvider` SHOULD aim for `singleton` by default unless request-scoped state is
involved.

**Status:** MANDATORY  
**Severity:** HIGH

---

## 7. Container Ownership Rule

### 7.1 Core Principle

```text
Value objects can be new.
Services must be injected.
Composition roots may assemble.
Runtime execution must only execute.
```

This rule resolves the fundamental question: "When is `new` allowed?"

The answer depends on **what the object is** and **where the code lives**.

### 7.2 When DI/Container is MANDATORY

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

### 7.3 When direct `new` is ALLOWED — Data and Result Objects

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

### 7.4 Decision Tests

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

### 7.5 Direct `new` by Path Context — Not by Class Name

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

### 7.6 Factory Class Precision

A factory may instantiate produced result objects.

A factory **MUST NOT** secretly assemble framework/runtime graphs.

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

**FORBIDDEN — factory assembles a graph:**

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

If a class assembles multiple runtime services, it belongs in:

```text
System/Configuration/Builders/
```

not in a generic factory.

**Factory rule:**

- Factories create produced objects.
- Configuration builders assemble system graphs.
- Runtime services execute behavior.

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

**Builder Validity Cross-Reference:**

For complete builder governance including:

* builder validity rule (allowed/forbidden builder patterns)
* builder decision questions
* builder naming rule
* builder method naming rule
* fluent DSL principles
* assembly graph vs runtime DSL distinctions

See:

* `how-to-architecture.md` — Section 13.3 Builders Rule (canonical builder validity)
* `how-to-design-components.md` — Section 6.5.1 Configuration/Builders Rule
* `how-to-architecture.md` — Section 13.3.3 Builder Naming Rule
* `how-to-architecture.md` — Section 13.3.4 Builder Method Naming Rule
* `how-to-dependency-injection.md` — Section 8 Fluent DSL Design Principles

**Performance/Runtime Consideration:**

Builders must remain runtime-safe and performance-aware.

Builders must not:

* cause unnecessary object churn during assembly
* use runtime reflection in hot paths
* repeatedly reconstruct graphs during runtime execution
* act as service-locator-style lazy pulling in hot execution paths

Builders should prefer:

* compiled metadata where applicable
* stable graph assembly
* explicit runtime ownership
* reusable runtime-safe plans

See `avax-runtime-performance-cache` for runtime safety requirements.

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

### 6.10 Golden Path Example Rule

Golden path examples **MUST** show canonical framework usage.

Examples are architecture because humans and AI copy them.

Golden path examples **MAY** instantiate:

```text
ApplicationBuilder
ProjectPath
EnvironmentName
simple config/value objects
userland DTOs/events/messages
top-level application configuration DSL objects
```

Golden path examples **MUST NOT** manually instantiate framework runtime services:

```text
RunDoctor
HandleIncomingHttp
ResponseFactory / CreateHttpResponse
Filesystem
EventDispatcher
Logger
DatabaseConnection
QueryOrchestrator
HttpMiddlewareStack
FailureBoundary
ControllerResolver
ResolveCallable
```

**BAD:**

```php
return new ApplicationBuilder(
    clock: new SystemClock(),
    runDoctor: new RunDoctor(),
    handleIncomingHttp: new HandleIncomingHttp(
        responseFactory: new ResponseFactory(),
    ),
    filesystem: new Filesystem(),
);
```

**GOOD:**

```php
return ApplicationBuilder::forProject(
    projectPath: $projectPath,
    environmentName: $environmentName,
)
    ->withDefaultRuntimeServices()
    ->withHttpRoutes(__DIR__ . '/config/routes.php');
```

If an example needs custom runtime services, it **MUST** show provider/configuration override, not manual graph
assembly.

**Acceptance criteria:**

- Golden path examples do not manually assemble runtime services.
- Examples show provider/configuration override for customization.
- Examples teach canonical framework usage, not cheap assembly.

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

## 7.6 Root Application Container Rule

AvaX MUST use one canonical root Application Container as the runtime object graph owner.

Components MUST NOT own independent runtime containers by default.

Components own registrations through:

- ServiceProviders
- ComponentDefinitions
- Configuration/Builders

The root container owns:

- object graph
- lifecycle scopes
- verification
- freezing
- runtime resolution

FrameworkBootstrapServiceProvider or FrameworkCoreServiceProvider MUST load first.

It may register only framework-level primitives/defaults/lifecycle services:

- Clock / SystemClock
- Filesystem
- Logger / NullLogger
- ResolveCallable
- ComponentRegistry
- ProviderRegistry
- ResettableStateRegistry
- RequestScopeFactory
- HealthRegistry
- container compile/verify hooks

It MUST NOT register component-specific behavior such as Router, Database, Auth, Queue, GraphQL, Response, Cache stores,
or Events internals unless those are true framework bootstrap primitives.

Child containers/scopes MAY exist only for lifecycle isolation:

- request
- worker
- tenant
- plugin sandbox
- tests

**Short version:**

```text
Component is not a container.
Component owns registrations.
Application container owns the graph.
Runtime scope owns lifecycle state.
```

---

## 8. Fluent API Law

### 8.1 The Rule

Complex operations MUST provide fluent, chainable, intent-first APIs.

Call sites must read like human DSL, not internal plumbing.

### 8.2 Fluent DSL Design Principles

Public DSLs should remain fluent and human-readable.

Internal assembly classes should remain explicit and typed.

Fluent APIs are encouraged only when they:

* improve readability
* preserve explicit intent
* preserve type safety
* preserve ordering clarity
* avoid hidden side effects
* reflect real domain concepts

**Preferred:**

```php
$app->routes()->compiledTable()
$auth->tokens()->authentication()
```

**Avoid:**

```php
$builder->withStuff()->andMore()->doIt()
```

Fluent chains must read like intent, not like internal plumbing.

A call site should answer "What is happening?" not "How many internal objects are required to make it happen?"

### 8.3 Fluent API Design

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

### 8.4 Fluent API Rules

```text
Entry point is static factory or DI-resolved instance.
Chain is immutable — each call returns a new builder instance.
Terminal method executes the operation (get(), render(), dispatch(), run()).
Fluent chain MUST NOT have side effects until the terminal method.
Fluent API MUST accept natural inputs — boundary owns normalization.
```

### 8.5 Forbidden Fluent Patterns

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

// BAD — generic fluent chain without intent
$builder->withStuff()->andMore()->doIt();
```

### 8.6 DSL Method Naming Rule

Do not default to generic method names in fluent APIs.

Use the most intention-revealing method name.

**Preferred order:**

1. Domain/product-specific methods:
   * `authentication()`
   * `runtimeKernel()`
   * `routeTable()`
   * `middlewarePipeline()`
   * `policies()`
   * `identity()`

2. `create()` when the class name fully names the product.

3. `build()` only when assembling a graph, plan, pipeline, blueprint, or compiled structure is semantically important.

4. `__invoke()` only when:
   * the class is intentionally a callable factory
   * call-site clarity remains obvious
   * no intent ambiguity exists

**Forbidden:**

* `build()` everywhere by habit
* `create()` everywhere by habit
* `__invoke()` when it hides intent
* magical DSL ambiguity
* generic `with*()` chains that hide intent

### 8.7 Assembly Graph vs Runtime DSL

Assembly-time fluent DSLs (used in `Configuration/Builders` or `ServiceProvider`) assemble object graphs:

```php
ApplicationBuilder::forProject($path, $env)
    ->withDefaultRuntimeServices()
    ->withHttpRoutes(__DIR__ . '/config/routes.php')
    ->build();
```

Runtime DSLs (used in `Capabilities/` or `PublicSurface/`) execute behavior or create runtime results:

```php
Error::response($e)
    ->withStatus(500)
    ->render();
```

These are different concerns.

Assembly DSLs build the system.
Runtime DSLs use the system.

Both must remain fluent, type-safe, and intent-first.

For builder governance, see:

* `how-to-architecture.md` — Section 13.3 Builders Rule
* `how-to-design-components.md` — Section 6.5.1 Configuration/Builders Rule

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

### 10.1 Container Resolution Rule

**Status:** MANDATORY
**Scope:** All production PHP code
**Severity:** BLOCKER

Container resolution **MUST** occur only in approved composition contexts.

Service locator pattern **MUST NOT** be used in runtime execution code.

```text
Container::get()
$container->get()
$container->make()
$container->resolve()
app()
resolve()
```

These calls **MUST NOT** appear in:

```text
business/runtime execution paths
Flows
Capabilities (runtime methods)
PublicSurface (runtime methods)
controllers
middleware execution
event/listener execution
queue/job execution
database/query execution
request handling
failure handling
worker loop iteration
```

**BAD:**

```php
public function handle(Request $request): Response
{
    $logger = app(LoggerInterface::class);
    $logger->info('Request received');

    $service = $container->get(SomeService::class);

    return $service->process($request);
}
```

**GOOD:**

```php
final readonly class HandleIncomingHttp
{
    public function __construct(
        private LoggerInterface $logger,
        private SomeService $someService,
    ) {}

    public function handle(Request $request): Response
    {
        $this->logger->info('Request received');

        return $this->someService->process($request);
    }
}
```

### 10.2 Approved Container Resolution Contexts

Container resolution is **ALLOWED** only in:

```text
ServiceProvider register() method
ServiceProvider boot() method (with idempotency)
System/Configuration/
System/Configuration/Builders/
explicit factory classes (for result objects, not graph assembly)
test setup (tests/)
tooling scripts (tooling/)
composition roots (Avax::create, CreateApplication)
```

**GOOD:**

```php
final readonly class LoggerServiceProvider
{
    public function register(Container $container): void
    {
        $container->bind(
            LoggerInterface::class,
            static fn (): LoggerInterface => new FileLogger(
                projectPath: $container->get(ProjectPath::class),
            ),
        );
    }
}
```

### 10.3 ResolveCallable — Primary Callable Resolver

`ResolveCallable` is the primary mechanism for resolving and invoking callables.

```text
If PSR-11 container is available, delegate to it.
Container autowiring MUST resolve all constructor dependencies.
Reject non-invokable class-strings.
Support method-level autowiring for route handlers.
```

Container resolution failure MUST produce clear diagnostics at boot, not runtime.

### 10.4 Autowiring Implementation

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

### 10.5 Resolution Failure Law

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

### 11.2 Gate Enforcement Rule

**Status:** MANDATORY
**Scope:** All tooling gates and manual review
**Severity:** BLOCKER

Tooling gates **MUST** be path/context-aware, not class-name based.

Gates **MUST NOT** use allowlists of class names to decide what is allowed.

Gates **MUST** scan by path context and rule violation pattern.

```text
FORBIDDEN: allowlist of class names that may be instantiated
ALLOWED:  scan all runtime folders for forbidden patterns
```

Gate logic:

```text
1. Scan framework/System/Flows/** for forbidden patterns
2. Scan framework/System/Capabilities/** runtime methods for forbidden patterns
3. Scan framework/System/PublicSurface/** runtime methods for forbidden patterns
4. Scan components/**/System/Flows/** for forbidden patterns
5. Scan components/**/System/Capabilities/** runtime methods for forbidden patterns
6. Scan components/**/System/PublicSurface/** runtime methods for forbidden patterns
7. Report every violation with file, line, pattern type, severity
```

Forbidden patterns (§2 Forbidden Patterns):

```text
class_exists() gating
new Build* in runtime code
new *Middleware, *Handler, *Dispatcher, *Resolver in runtime code
->build() in runtime execution
$middleware[], $pipeline[] construction in runtime code
?? new fallback in runtime code
= new ClassName() default parameter in runtime code
Container::get(), $container->get(), app(), resolve() in runtime code
service locator pattern in runtime code
```

Approved composition contexts (§3.5 Approved Composition Contexts):

```text
ServiceProvider register()
ServiceProvider boot()
System/Configuration/
System/Configuration/Builders/
explicit factory classes
tests/
tooling/
composition roots
```

If a file is in an approved composition context, it is exempt from runtime pattern scanning.

If a file is in a runtime context, it is scanned for all forbidden patterns regardless of class name.

---

## 12. Review Checklist

A DI / ServiceProvider / Fluent API review passes only if:

```text
every ACTIVE component has a ServiceProvider
ROADMAP/SCAFFOLD/LABS_ONLY/EVIDENCE_ONLY/TEST_ONLY/PURE_FOUNDATION components are exempt and classified
empty ServiceProvider shells do not exist
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

## 15. PublicSurface Factory Boundary Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

PublicSurface may expose public factories only when they create public value/result objects or protect users from
internal construction details.

PublicSurface factories MUST NOT:

```text
assemble runtime service graphs
instantiate runtime services
access the container as service locator
create middleware, dispatchers, resolvers, clients, stores, loggers, repositories, or framework runtime services
hide dependency assembly
```

**Allowed:**
`Responses::json()` delegates to `CreateHttpResponse` and returns `Response`.

**Forbidden:**
`Responses::json()` creates new `CreateHttpResponse` internally.

**Forbidden:**
PublicSurface factory creates `Runtime` with `Router`, `EventDispatcher`, `Logger`, `Container`, `MiddlewareStack`.

### Rule Summary

PublicSurface may create produced public values.
PublicSurface must not assemble machinery.

---

## 16. DDD Factory vs Runtime Assembly Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

A DDD factory owns meaningful creation of domain/value/result objects when construction has invariants, policy, or
language meaning.

A DDD factory MUST NOT assemble framework runtime service graphs.

**Allowed:**
`CreateReleaseCandidate` creates `ReleaseCandidate` with invariants.

**Allowed:**
`CreatePublicApiSnapshot` creates a domain snapshot.

**Forbidden:**
`RuntimeFactory` creates `Router`, `EventDispatcher`, `Logger`, `MiddlewareStack`, `DatabaseConnection`.

### Rule Summary

If a class assembles runtime services, it belongs in:

```text
ServiceProvider
System/Configuration
System/Configuration/Builders
```

not in a DDD factory.

Factories create meaningful objects.
Configuration assembles the system.

---

## 16.1 Universal Enterprise Codecraft Cross-Reference

**Status:** MANDATORY  
**Severity:** BLOCKER

Dependency injection must serve the universal enterprise codecraft philosophy.

See:

- `how-to-design-components.md` — Section 30: Universal Enterprise Codecraft Rule
- `how-to-architecture.md` — Section 55: Universal Enterprise Codecraft Rule

Relevant principles for DI:

- **No Technical Theater:** DI must not introduce unnecessary builders, graphs, factories, or wiring layers. Assembly should be simple and readable.
- **Fluent API:** DI configuration should produce fluent, intention-revealing call-sites. The object graph should be obvious from provider code.
- **Cognitive Load:** If understanding a component's dependencies requires opening five providers, the DI structure is suspicious. Dependencies should be discoverable and local.
- **Structural Honesty:** Provider registration must reveal real dependency boundaries. Never hide god objects or dependency chaos behind provider wrappers.
- **Horizontal Blindness:** Providers must not wire sibling components to depend on each other directly. Coordination flows through a parent orchestrator.
- **Single Preferred Entry:** DI should route through the subsystem's approved gateway, not bypass it by injecting internal components.

---

## 17. Final Law

```text
Framework = DI.
ServiceProvider = composition root per active component.
new Class() = forbidden outside approved composition contexts.
?? new Fallback() = missing DI configuration.
Static = entry points and value objects only.
Fluent API = intent-first call sites.
Autowiring = mandatory for route handlers.
Container = the framework's heart.
Evidence = validation proves DI discipline.
Gates = path/context-aware, not class-name allowlists.
Runtime = execute only, never assemble.
Composition = assemble only, never execute.
PublicSurface = receives and delegates, never assembles.
DDD Factory = creates domain objects, never runtime graphs.
```

No claim of GREEN may be made without:

```text
check-direct-instantiation.php GREEN
check-container-service-locator.php GREEN
check-service-provider-coverage.php GREEN
check-runtime-composition-leaks.php GREEN
every ACTIVE component has real ServiceProvider
empty ServiceProvider shells do not exist
all tests pass through DI-resolved dependencies
no forbidden patterns in runtime folders
no service locator in runtime execution paths
```
