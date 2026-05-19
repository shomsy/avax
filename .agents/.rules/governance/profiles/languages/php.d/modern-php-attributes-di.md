# how-to-modern-php-attributes-di.md

# AvaX Modern PHP, Attributes & Dependency Injection Governance

## 1. Status

This document is mandatory governance for AvaX framework development.

It defines how AvaX uses modern PHP 8.0 through 8.5 language features, attributes as declarative metadata,
and dependency injection with autowiring discipline.

This is not a style preference document.

This is a correctness, performance, maintainability, and runtime-safety rule.

---

## 2. Core Philosophy

```text
Attributes declare.
Compiled metadata drives runtime.
DI composes.
Methods act.
```

AvaX uses attributes as the central declaration style — similar to NestJS decorators, Java annotations, or .NET
attributes.

But attributes must not become runtime chaos.

The flow is always:

```text
attributes -> discovery -> compiled metadata -> runtime uses compiled metadata
```

Never:

```text
attributes -> reflection on every request -> slow runtime
```

---

## 3. Modern PHP Adoption Rule

PHP 8.x features are tools, not decoration.

Every feature must have a clear use case.

### 3.1 PHP 8.0 — Foundation

| Feature                        | When to use                                     | When not to use                         |
|--------------------------------|-------------------------------------------------|-----------------------------------------|
| attributes                     | routing, validation, DI, policy, audit, tracing | replacing clear constructor logic       |
| named arguments                | explicit API calls, configuration               | positional APIs that forbid them        |
| union types                    | precise type signatures                         | masking unclear return types            |
| constructor property promotion | default for all classes                         | when property needs hooks or validation |
| match                          | expressive branching over enums or scalars      | side-effect-heavy branching             |
| nullsafe operator              | safe chaining on nullable objects               | hiding missing validation               |
| WeakMap                        | object metadata cache, per-object associations  | persistent cache, cross-request cache   |

### 3.2 PHP 8.1 — Expressiveness

| Feature               | When to use                                               |
|-----------------------|-----------------------------------------------------------|
| enums                 | domain/state with closed value sets                       |
| readonly properties   | immutable value objects, DTOs                             |
| first-class callables | explicit function references, callbacks                   |
| fibers                | cooperative concurrency in long-lived runtimes            |
| intersection types    | precise interface contracts requiring multiple interfaces |
| never                 | functions that always throw or exit                       |

### 3.3 PHP 8.2 — Immutability and Type Precision

| Feature                    | When to use                                               |
|----------------------------|-----------------------------------------------------------|
| readonly classes           | fully immutable DTOs, value objects, configuration shapes |
| DNF types                  | precise nullable/union combinations                       |
| standalone true/false/null | precise boolean/null typing                               |
| SensitiveParameter         | secret protection in stack traces                         |
| Random extension           | secure random generation                                  |

### 3.4 PHP 8.3 — Contract Strictness

| Feature                      | When to use                                        |
|------------------------------|----------------------------------------------------|
| typed class constants        | framework contracts, policy values, discriminators |
| #[Override]                  | strict inheritance discipline                      |
| dynamic class constant fetch | flexible constant resolution in generic code       |

### 3.5 PHP 8.4 — Object Model Upgrade

| Feature               | When to use                                              |
|-----------------------|----------------------------------------------------------|
| property hooks        | value normalization, invariant enforcement on properties |
| asymmetric visibility | public-read/private-write state                          |
| request_parse_body()  | multipart parsing in AvaX Request                        |
| PDO driver subclasses | type-safe database access where PDO is used              |

### 3.6 PHP 8.5 — Futuristic Syntax

| Feature                      | When to use                                               | When not to use                   |
|------------------------------|-----------------------------------------------------------|-----------------------------------|
| pipe operator                | pure data transformations (schema, config, normalization) | side-effect-heavy code            |
| #[NoDiscard]                 | important return values that must not be ignored          | void-like methods                 |
| clone-with syntax            | immutable object modification                             | mutable update patterns           |
| static asymmetric visibility | class-level public-read/private-write                     | unnecessary visibility complexity |
| final constructor promotion  | promoted properties that cannot be overridden             | extensible base classes           |

### 3.7 Application Defaults

```text
constructor property promotion is the default
readonly where the object is honestly immutable
final by default unless the class is an extension point
typed constants and typed properties everywhere
named arguments where the API allows
static closures where possible
property hooks only where they reduce boilerplate without hiding lifecycle logic
asymmetric visibility for public-read/private-write state
pipe operator only for clear pure transformations
NoDiscard for important return values
```

---

## 4. Pipe Operator Rule

The pipe operator is not for everything.

Use it only for pure data transformations:

```text
data transformations
normalization pipelines
schema transformations
string/path transformations
config processing
```

Good:

```php
$schema = $class
    |> $this->readShape(...)
    |> $this->convertToJsonSchema(...)
    |> $this->normalizeSchema(...);
```

Bad:

```php
$request
    |> $this->saveToDatabase(...)
    |> $this->sendNotification(...)
    |> $this->chargeCard(...);
```

Use pipeline or flow orchestration for side-effect-heavy code.

---

## 5. Attribute Runtime

### 5.1 Attribute Areas

AvaX uses these attribute categories:

#### Routing and Controllers

```php
#[Controller]
#[Route('/path')]
#[Get]
#[Post]
#[Put]
#[Patch]
#[Delete]
#[Options]
#[Head]
#[Any]
#[Middleware(MiddlewareClass::class)]
#[RateLimit(requests: 100, window: 60)]
```

#### Dependency Injection and Autowiring

```php
#[Inject(ServiceClass::class)]
#[Autowire]
#[Config('config.key')]
#[Body]
#[CurrentUser]
#[Request]
#[Param('name')]
#[Header('X-Correlation-ID')]
```

#### Validation and Data

```php
#[Validate]
#[Required]
#[StringType]
#[Min(N)]
#[Max(N)]
#[RegexPattern('/pattern/')]
#[Email]
#[Length(min: N, max: N)]
```

#### Security and Policy

```php
#[Policy('policy.name')]
#[Can('ability')]
#[FeatureFlag('flag.name')]
#[Transactional]
#[Audit('event.name')]
#[Trace]
```

#### Performance and Caching

```php
#[Cache(ttl: 3600)]
#[Queue('queue.name')]
#[MessageConsumer('channel')]
```

#### Language-Level Discipline

```php
#[NoDiscard]
#[Override]
```

### 5.2 Attribute Compilation Rule

Attributes are declaration, not runtime execution.

Every attribute area must have a compiler:

```text
CompileControllerAttributes
CompileRouteAttributes
CompileValidationAttributes
CompilePolicyAttributes
CompileInjectionAttributes
CompileMessageConsumerAttributes
CompileAuditAttributes
```

The compilation flow:

```text
1. Discovery phase (boot / first run)
   - scan classes for attributes
   - build metadata maps
   - validate attribute combinations

2. Compilation phase
   - resolve class references
   - merge overlapping attributes
   - build lookup tables
   - cache to warm storage

3. Runtime phase
   - use compiled metadata only
   - no reflection per request
   - no attribute scanning per call
   - invalidate cache only on source change
```

### 5.3 Never

```text
scan attributes on every request
reflection on every controller call
magic autowiring without diagnostics
silent failure if attribute is invalid
compile attributes without caching
cache attributes without freshness check
```

### 5.4 Attribute Validation

Every attribute must be validated at compile time:

```text
class references must exist
class references must be instantiable or resolvable
enum values must be valid
numeric constraints must be consistent
policy names must be registered
feature flag names must be known
queue names must be configured
middleware classes must implement the contract
```

Invalid attributes must produce clear diagnostics at boot, not silent failures at runtime.

---

## 6. Dependency Injection and Autowiring

### 6.1 Principle

Constructor injection is the default for stable dependencies.

Method injection / autowiring is allowed for runtime-scoped context:

```text
request-scoped dependencies
controller action parameters
command handler context
message consumer context
policy context
current user / session / request
transaction scope
```

### 6.2 Constructor Bloat Rules

Dependency count determines action:

```text
0-4 dependencies: normal
5-7 dependencies: check responsibility
8+ dependencies: architecture warning, consider splitting
```

If a class has 12 dependencies, it probably does too much.

### 6.3 Attribute-Driven DI

Method parameters may use attributes to resolve runtime context:

```php
final readonly class RegisterUserController
{
    public function __construct(
        private RegisterUser $registerUser,
    ) {}

    public function __invoke(
        #[Body] RegisterRequest $request,
        #[CurrentUser] User|null $user,
        #[Config('security.registration_enabled')] bool $enabled,
    ) : Response {
        // ...
    }
}
```

Constructor receives stable, long-lived dependencies.
Method parameters receive request-scoped context.

### 6.4 Autowiring Rules

The container must support:

```text
constructor autowiring by type
method parameter autowiring by type
attribute-based source resolution (#[Config], #[Body], #[CurrentUser])
config value injection
request DTO injection through #[Body]
current user injection through #[CurrentUser]
policy context injection
```

### 6.5 Forbidden

```text
Container::get() in business logic (allowed in bootstrap only)
service locator pattern masked as DI
nullable dependencies that are not truly optional
hidden static dependencies
god constructors with 12+ dependencies
autowiring that silently resolves to null without diagnostics
```

### 6.6 Service Locator Discipline

The container facade is intentionally Laravel-style and allowed at bootstrap level.

But business logic must receive dependencies explicitly through constructor or method injection.

Tooling must detect Container::get() outside bootstrap boundaries.

---

## 7. Compiled Metadata vs Hot-Path Reflection

### 7.1 The Rule

Reflection is not a hot path.

### 7.2 Reflection Allowed In

```text
build phase / boot phase
compile phase / first discovery
tests
tooling
one-time schema generation
migration planning
diagnostic checks
```

### 7.3 Reflection Forbidden In

```text
every request
every SecureRequest hydration
every controller invocation
every queue job
repeated schema generation
middleware execution per call
policy evaluation per request
route matching per request
```

### 7.4 Compiled Metadata Types

```text
CompiledDataObjectShape
CompiledSecureRequestShape
CompiledValidationAttributeMap
CompiledCasterMap
CompiledSchemaMetadata
CompiledOpenApiMetadata
CompiledRouteTable
CompiledMiddlewarePipeline
CompiledDIContainerGraph
CompiledControllerActionMap
```

### 7.5 Cache Rules

Compiled metadata must live behind a cache with:

```text
freshness check against source file modification time
invalidation on changed source
clear command (cache:clear / metadata:clear)
warm command (cache:warm / metadata:compile)
doctor check for cache health
no usage to hide bugs
```

---

## 8. WeakMap Cache Rule

Use WeakMap for:

```text
object metadata cache
reflection-to-object metadata
temporary runtime associations
per-object computed metadata
```

Do not use WeakMap for:

```text
persistent cache
cross-request cache
compiled metadata cache (use file/APCu/Redis)
```

WeakMap keys are objects — when the object is garbage collected, the entry is removed.

This is perfect for per-request, per-object associations that must not leak memory.

---

## 9. Property Hooks Rule

Property hooks are for value normalization and invariant enforcement.

Good:

```php
final class UserInput
{
    public string $email {
        set {
            $trimmed = trim($value);
            if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidEmailException($value);
            }
            $this->email = strtolower($trimmed);
        }
    }
}
```

Bad:

```php
final class Order
{
    public bool $isComplete {
        set {
            if ($value) {
                $this->sendNotification();
                $this->updateInventory();
                $this->recordAudit();
            }
            $this->isComplete = $value;
        }
    }
}
```

Property hooks must not hide lifecycle logic with side effects.
Use explicit methods for side-effect-heavy operations.

---

## 10. Asymmetric Visibility Rule

Use asymmetric visibility for public-read/private-write state:

```php
final class Counter
{
    public private(set) int $count = 0;

    public function increment() : void
    {
        $this->count++;
    }
}
```

This is clearer than a getter with no setter, or a private property with a public getter.

---

## 11. #[NoDiscard] Rule

Use #[NoDiscard] for methods whose return value must not be silently ignored:

```php
#[NoDiscard]
public function validate() : ValidationResult
{
    // ...
}
```

If the caller ignores the result, the engine produces a diagnostic.

To intentionally ignore, use explicit void cast:

```php
(void) $result->validate();
```

---

## 12. Constructor Property Promotion Rule

Constructor property promotion is the default.

```php
final readonly class CreateUser
{
    public function __construct(
        private UserRepository $users,
        private HashPassword $hashPassword,
    ) {}
}
```

Exceptions — do NOT use promotion when:

```text
property needs a hook
property needs asymmetric visibility
property needs different visibility than constructor parameter
property is computed from other parameters
property is nullable but parameter is required
```

---

## 13. Readonly Rule

Use readonly classes for honestly immutable objects:

```text
DTOs
value objects
configuration shapes
request/response objects
event objects
query results
```

Do not use readonly for:

```text
objects that mutate internally (counters, builders, pools)
objects that track state over time (sessions, transactions)
objects with lifecycle (connections, workers)
```

---

## 14. Final Rule

Classes are final by default unless:

```text
the class is an explicit extension point
the class is a framework base meant to be customized
the class has documented inheritance contract
```

Final constructor promotion properties cannot be overridden by child classes:

```php
final readonly class BaseService
{
    public function __construct(
        final private LoggerInterface $logger,
    ) {}
}
```

---

## 15. Enums Rule

Use enums for domain and system state with closed value sets.

Candidates:

```text
DoctorStatus
HealthStatus
RuntimeName
QueueDriver
JobStatus
MessageStatus
MessageDirection
PolicyEffect
FeatureFlagState
StorageVisibility
ResponseFormat
ContentType
HttpMethod
RouteVerb
CacheState
ComponentStatus
FailureSeverity
RetryDecision
```

Do not use enums for:

```text
open-ended user input
external system identifiers you do not control
values expected to grow dynamically
free-form text fields
```

---

## 16. Superglobal Isolation Rule

All PHP superglobals must be isolated behind AvaX Request object.

```text
$_GET -> Request::query()
$_POST -> Request::body() / Request::post()
$_FILES -> Request::files()
$_COOKIE -> Request::cookies()
$_SERVER -> Request::server()
php://input -> Request::readBody()
```

PHP 8.4 request_parse_body() may be used internally by the Request component.

No other code may access superglobals directly.

The Request component is the sole owner of superglobal access.

---

## 17. Tooling Gates

The following tooling gates enforce this governance:

```bash
php tooling/refactor/check-modern-php-style.php
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-container-service-locator.php
php tooling/refactor/check-raw-reflection-hot-path.php
php tooling/refactor/check-cacheable-metadata.php
php tooling/refactor/check-superglobal-usage.php
php tooling/refactor/check-naked-domain-strings.php
```

These gates are mandatory before any V5 stage may be marked GREEN.

---

## 18. Custom Rector Rules

The following Rector rules support automated migration:

```text
ConstructorPromotionRector — convert simple constructor assignments to promotion
PropertyHookRector — replace getter/setter with hooks where safe
ReflectionHotPathRector — report reflection in hot paths
ConstructorBloatRector — report 8+ dependency constructors
NakedDomainStringRector — report strings that should be enums
```

Rector must not aggressively change semantics without tests.

---

## 19. Review Checklist

A modern PHP / attributes / DI review passes only if:

```text
attributes are compiled, not scanned per request
compiled metadata is cached with freshness check
no reflection in hot paths
constructor dependencies are 0-4 (or justified 5-7)
no Container::get() in business logic
method injection used for runtime context only
pipe operator used for pure transformations only
superglobals isolated behind AvaX Request
enums used for closed domain/state values
readonly used for honestly immutable objects
final by default unless extension point
property hooks without side-effect lifecycle logic
asymmetric visibility for public-read/private-write
NoDiscard on important return values
WeakMap for per-object cache, not persistent cache
tooling gates pass
```

If any item fails, status is YELLOW or RED.

---

## 20. Final Law

```text
Attributes declare.
Compilation resolves.
Runtime executes.
DI composes.
Methods act.
```

Modern PHP features are not decoration.

They are tools for clarity, safety, and performance.

Use them deliberately.

Compile aggressively.

Cache everything expensive.

Never reflect in a hot path.

Never bypass the container facade in business logic.

Never let constructor bloat hide responsibility confusion.

Never use pipe operator for side effects.

Never expose superglobals outside the Request component.

Never claim GREEN without tooling evidence.
