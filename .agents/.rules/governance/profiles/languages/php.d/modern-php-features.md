# Modern PHP Features Governance

## Status

This document defines generic PHP best practices for PHP 8.0 through 8.5.

It is Layer 2 (PHP Ecosystem) governance — applicable to any PHP project.

This is not a style preference document.

This is a correctness, performance, and maintainability rule.

---

## 1. Modern PHP Adoption Rule

PHP 8.x features are tools, not decoration.

Every feature must have a clear use case.

### 1.1 PHP 8.0 — Foundation

| Feature                        | When to use                                     | When not to use                         |
|--------------------------------|-------------------------------------------------|-----------------------------------------|
| attributes                     | declarative metadata, routing, validation, DI   | replacing clear constructor logic       |
| named arguments                | explicit API calls, configuration               | positional APIs that forbid them        |
| union types                    | precise type signatures                         | masking unclear return types            |
| constructor property promotion | default for all classes                         | when property needs hooks or validation |
| match                          | expressive branching over enums or scalars      | side-effect-heavy branching             |
| nullsafe operator              | safe chaining on nullable objects               | hiding missing validation               |
| WeakMap                        | object metadata cache, per-object associations  | persistent cache, cross-request cache   |

### 1.2 PHP 8.1 — Expressiveness

| Feature               | When to use                                               |
|-----------------------|-----------------------------------------------------------|
| enums                 | domain/state with closed value sets                       |
| readonly properties   | immutable value objects, DTOs                             |
| first-class callables | explicit function references, callbacks                   |
| fibers                | cooperative concurrency in long-lived runtimes            |
| intersection types    | precise interface contracts requiring multiple interfaces |
| never                 | functions that always throw or exit                       |

### 1.3 PHP 8.2 — Immutability and Type Precision

| Feature                    | When to use                                               |
|----------------------------|-----------------------------------------------------------|
| readonly classes           | fully immutable DTOs, value objects, configuration shapes |
| DNF types                  | precise nullable/union combinations                       |
| standalone true/false/null | precise boolean/null typing                               |
| SensitiveParameter         | secret protection in stack traces                         |
| Random extension           | secure random generation                                  |

### 1.4 PHP 8.3 — Contract Strictness

| Feature                      | When to use                                        |
|------------------------------|----------------------------------------------------|
| typed class constants        | framework contracts, policy values, discriminators |
| #[Override]                  | strict inheritance discipline                      |
| dynamic class constant fetch | flexible constant resolution in generic code       |

### 1.5 PHP 8.4 — Object Model Upgrade

| Feature               | When to use                                              |
|-----------------------|----------------------------------------------------------|
| property hooks        | value normalization, invariant enforcement on properties |
| asymmetric visibility | public-read/private-write state                          |
| request_parse_body()  | multipart parsing in HTTP request handlers               |
| PDO driver subclasses | type-safe database access where PDO is used              |

### 1.6 PHP 8.5 — Futuristic Syntax

| Feature                      | When to use                                               | When not to use                   |
|------------------------------|-----------------------------------------------------------|-----------------------------------|
| pipe operator                | pure data transformations (schema, config, normalization) | side-effect-heavy code            |
| #[NoDiscard]                 | important return values that must not be ignored          | void-like methods                 |
| clone-with syntax            | immutable object modification                             | mutable update patterns           |
| static asymmetric visibility | class-level public-read/private-write                     | unnecessary visibility complexity |
| final constructor promotion  | promoted properties that cannot be overridden             | extensible base classes           |

### 1.7 Application Defaults

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

## 2. Pipe Operator Rule

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

## 3. WeakMap Cache Rule

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

## 4. Property Hooks Rule

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

## 5. Asymmetric Visibility Rule

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

## 6. #[NoDiscard] Rule

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

## 7. Constructor Property Promotion Rule

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

## 8. Readonly Rule

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

## 9. Final Rule

Classes are final by default unless:

```text
the class is an explicit extension point
the class is a framework base meant to be customized
the class has a documented inheritance contract
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

## 10. Enums Rule

Use enums for domain and system state with closed value sets.

Candidates:

```text
status enumerations (active, inactive, pending, completed)
health status values
runtime or environment identifiers
queue driver types
job states
message status and direction
policy effects
feature flag states
storage visibility levels
response formats
content types
HTTP methods
cache states
component lifecycle states
failure severity levels
retry decisions
```

Do not use enums for:

```text
open-ended user input
external system identifiers you do not control
values expected to grow dynamically
free-form text fields
```

---

## 11. Final Law

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

Never let constructor bloat hide responsibility confusion.

Never use pipe operator for side effects.
