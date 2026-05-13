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

## 6. Fluent API Law

### 6.1 The Rule

Complex operations MUST provide fluent, chainable, intent-first APIs.

Call sites must read like human DSL, not internal plumbing.

### 6.2 Fluent API Design

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

### 6.3 Fluent API Rules

```text
Entry point is static factory or DI-resolved instance.
Chain is immutable — each call returns a new builder instance.
Terminal method executes the operation (get(), render(), dispatch(), run()).
Fluent chain MUST NOT have side effects until the terminal method.
Fluent API MUST accept natural inputs — boundary owns normalization.
```

### 6.4 Forbidden Fluent Patterns

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

## 7. Static Method Rules

### 7.1 Allowed Static Usage

```text
Fluent API entry points: Queue::dispatch(), Error::response(), Transaction::begin()
Value object factories: User::fromArray(), Money::fromCents()
Immutable named constructors: Config::fromFile(), Schema::fromMigration()
Configuration access: DataTransfer::config(), Cache::ttl()
```

### 7.2 Forbidden Static Usage

```text
Mutable shared state without reset capability
Business logic that should be DI-resolved
Side-effect-heavy operations that should be testable
Operations that bypass the container
Facade patterns that hide dependencies
```

### 7.3 Static Mutable State Law

If a component uses static mutable state:

```text
It MUST be resettable via explicit reset() method.
It MUST be documented as using static state.
It MUST be safe for test isolation.
It MUST NOT leak state between requests in long-lived runtimes.
```

---

## 8. Container Resolution Law

### 8.1 ResolveCallable — Primary Callable Resolver

`ResolveCallable` is the primary mechanism for resolving and invoking callables.

```text
If PSR-11 container is available, delegate to it.
If not, fall back to zero-arg new — but this is a YELLOW status, not GREEN.
Reject non-invokable class-strings.
Support method-level autowiring for route handlers.
```

### 8.2 Autowiring Implementation

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

### 8.3 Resolution Failure Law

```text
Resolution failure MUST produce clear diagnostics at boot, not runtime.
Missing binding MUST report: which class, which parameter, which file, which line.
Circular dependency MUST be detected and reported with full chain.
Non-instantiable class MUST report: is it abstract? interface? missing autoload?
```

---

## 9. Tooling Gates

The following tooling gates enforce this governance:

```bash
php tooling/refactor/check-container-service-locator.php
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-service-provider-coverage.php
```

These gates are mandatory before any stage may be marked GREEN.

### 9.1 check-direct-instantiation.php

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

## 10. Review Checklist

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

## 11. Migration Strategy

### 11.1 For Existing Code with `new Class()`

```text
Step 1: Identify all new Class() outside constructors
Step 2: Determine if the class is a stable dependency or dynamic callable
Step 3: For stable dependencies — add to constructor injection
Step 4: For dynamic callables — use ResolveCallable
Step 5: Add ServiceProvider registration for the component
Step 6: Remove ?? new Fallback() — register fallback in ServiceProvider
Step 7: Run tests and validation
```

### 11.2 For Existing Code with `?? new Fallback()`

```text
Step 1: Identify all ?? new Fallback() patterns
Step 2: Register the fallback as a default binding in ServiceProvider
Step 3: Inject the dependency through constructor
Step 4: Remove the null-coalescing fallback
Step 5: Verify tests pass
```

### 11.3 For Existing Code with Static Calls

```text
Step 1: Identify static calls that bypass DI
Step 2: Determine if it is a valid static entry point (fluent API, value object)
Step 3: If not valid — convert to DI-resolved instance
Step 4: Add to ServiceProvider if needed
Step 5: Update call sites to use injected dependency
```

---

## 12. Final Law

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
