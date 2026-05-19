# PHP Coding Standards

## Status

**MANDATORY** - This document defines non-negotiable PHP coding standards for PHP projects.

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

## 1. General

### 1.1 Strict Types

All PHP files **MUST** declare strict types:

```php
declare(strict_types=1);
```

The declaration must appear on the first line after the opening `<?php` tag, before any other code.

### 1.2 PSR Compliance

Code **MUST** follow PSR-12 formatting standards.

### 1.3 Namespace and Imports

- All classes **MUST** be in a namespace.
- Imports **MUST** use `use` statements with fully qualified class names.
- Fully qualified class names **MUST NOT** appear in code — always import and use the short name.
- Unused imports **MUST NOT** be present.
- Imports in docblocks **MUST** match imports in code.

### 1.4 Visibility

All properties and methods **MUST** have explicit visibility (`public`, `protected`, `private`).

Default visibility **MUST** be the most restrictive option that satisfies the design.

Prefer `private` unless there is a proven need for `protected` or `public`.

### 1.5 Immutability

Prefer immutable objects when the object represents:

- a value
- configuration
- public API input or output
- reusable framework state
- a DTO

Mutable objects are allowed only when lifecycle, performance, or runtime behavior requires mutation.

If mutable, mutation must be explicit and controlled.

---

## 2. Constructor Promotion

Constructor promotion **SHOULD** be used for properties that are:

- required dependencies
- configuration values
- immutable state

```php
// GOOD
final readonly class User
{
    public function __construct(
        private string $name,
        private EmailAddress $email,
    ) {}
}

// BAD — manual property declaration for simple promotion
final readonly class User
{
    private string $name;
    private EmailAddress $email;

    public function __construct(string $name, EmailAddress $email)
    {
        $this->name = $name;
        $this->email = $email;
    }
}
```

Constructor-promoted properties **MAY** be marked `final` when contract stability or inheritance boundaries matter.

---

## 3. Type Discipline

### 3.1 Return Types

All methods **MUST** declare return types.

```php
// GOOD
public function getName(): string

// BAD
public function getName()
```

Methods that return nothing **MUST** declare `void`.

### 3.2 Nullable Types

Prefer `Type|null` over `?Type` for consistency with PHPDoc and union type readability:

```php
// PREFERRED
public function findUser(int $id): User|null

// ALLOWED but less preferred
public function findUser(int $id): ?User
```

### 3.3 Parameter Types

All parameters **MUST** be typed.

```php
// GOOD
public function process(User $user, int $count): void

// BAD
public function process($user, $count)
```

Named arguments **MAY** be used where they improve readability.

### 3.4 Mixed Type

`mixed` **SHOULD NOT** be used unless the value is genuinely unconstrained by design (e.g., a generic serializer).

Prefer explicit types or generics-aware patterns over `mixed`.

### 3.5 Typed Class Constants

Class constants **SHOULD** be typed where PHP 8.3+ supports it:

```php
class Status
{
    public const string ACTIVE = 'active';
    public const string INACTIVE = 'inactive';
}
```

---

## 4. Modern PHP Features

### 4.1 Required Features (PHP 8.0+)

The following features **MUST** be used where applicable:

| Feature | Usage |
|---|---|
| Match expressions | Prefer over `switch` for value returns |
| Constructor promotion | For simple property assignment |
| Named arguments | Where readability improves |
| Attributes | Native over PHPDoc where possible |
| First-class callables | When callable references are needed |
| Nullsafe operator | For safe chained null access |
| `#[Override]` | When overriding methods intentionally |
| Readonly properties/classes | For immutable data |
| Enums | For closed value sets |
| `declare(strict_types=1)` | In every file |

### 4.2 PHP 8.4+ Features

| Feature | Usage |
|---|---|
| Property hooks | When a property needs validation, normalization, or computed access |
| Asymmetric visibility | When read and write access should differ |
| `#[NoDiscard]` | On methods whose return value must not be silently ignored |
| `(void)` cast | Only when intentionally discarding a `#[NoDiscard]` return |
| `clone($object, [...])` | For immutable updates and wither-style APIs |
| Pipe operator `\|>` | Only when it improves readability over nesting |
| Attributes on constants | When metadata belongs to constants |
| Static closures in constant expressions | When they improve locality |

### 4.3 Syntax Selection Rule

Modern syntax is not a goal by itself.

Use newer syntax only when it improves:

- correctness
- locality
- maintainability
- readability
- domain expressiveness

---

## 5. Object Model

### 5.1 Value Objects and DTOs

Value objects and DTOs **SHOULD** be `final readonly class`.

```php
final readonly class EmailAddress
{
    public function __construct(
        public string $value,
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: {$value}");
        }
    }
}
```

### 5.2 Composition Over Inheritance

Prefer composition over inheritance.

Inheritance **MUST** only model a true "is-a" relationship with shared implementation.

Prefer interfaces for behavioral contracts.

### 5.3 Magic Methods

Magic methods **SHOULD NOT** be used unless building a real framework boundary, proxy, or DSL.

`__call`, `__get`, `__set`, `__isset`, `__unset` are forbidden as a default abstraction strategy.

`__serialize()` / `__unserialize()` **MUST** be preferred over `__sleep()` / `__wakeup()`.

### 5.4 Final Classes

Classes **SHOULD** be `final` unless explicitly designed for extension.

Open-for-extension classes **MUST** document their extension points.

---

## 6. Error Handling

### 6.1 Exceptions

Exceptions **MUST** be thrown for exceptional conditions.

Expected error conditions **SHOULD** use result objects or explicit return types rather than exceptions.

### 6.2 @throws Tags

Methods that throw exceptions **MUST** document them with `@throws` PHPDoc tags:

```php
/**
 * @throws InvalidArgumentException
 * @throws RuntimeException
 */
public function process(string $input): Result
```

### 6.3 Catch Blocks

Broad `catch (Throwable|Exception)` **SHOULD NOT** be used unless:

- at the application boundary
- for logging and re-throwing
- in a worker loop for crash protection

Broad catch-and-ignore is forbidden.

### 6.4 Error Messages

Error messages **MUST** explain:

- what failed
- what input caused the failure (without exposing secrets)
- what the caller should do next

---

## 7. Code Hygiene

### 7.1 Comments

Comments **MUST** explain *why*, not restate *what*.

Comments above `namespace`, `use` statements, and `trait` declarations are forbidden.

Redundant PHPDoc that adds no value beyond the signature **SHOULD NOT** be present.

### 7.2 PHPDoc

PHPDoc **MUST** be present on:

- public classes
- public methods
- methods with non-obvious behavior
- methods that throw exceptions

PHPDoc on private methods **MAY** be omitted when the method is trivial.

### 7.3 Dead Code

Dead code, unused methods, and unreachable branches **MUST NOT** be present.

### 7.4 Deprecated Patterns

The following deprecated patterns **MUST NOT** be used:

- Backticks as alias for `shell_exec()`
- `Reflection*::setAccessible()` for accessing private properties
- Returning `null` from `__debugInfo()`
- Using `null` as an array offset

---

## 8. Naming

### 8.1 General Rules

Names **MUST** be:

- simple
- descriptive
- consistent within the project
- in English

### 8.2 Naming Law

- **folder says flow or capability**
- **unit says responsibility**
- **function says exact action**

### 8.3 Forbidden Generic Names

The following names are forbidden as default architectural buckets:

- Services
- Helpers
- Utils
- Common
- Misc
- Managers
- Shared
- Core

These names hide responsibility instead of clarifying it.

---

## 9. Dependency Injection

### 9.1 Construction Rule

Direct `new Class()` instantiation **MUST NOT** appear in:

- flow/orchestration code
- business logic
- public surface code

`new` is allowed in:

- composition root
- factories
- tests
- value object constructors

### 9.2 Fallback Rule

`?? new Fallback()` construction **MUST NOT** appear in production code.

Fallbacks **MUST** be registered through the composition root or service registration.

### 9.3 Method-Level Injection

Method-level dependency injection **SHOULD** be used for route handlers and flow entry points.

```php
// GOOD — dependencies injected at method level
public function handle(Request $request, UserRepository $users, LoggerInterface $log): Response
```

---

## 10. Security

### 10.1 Input Validation

All external input **MUST** be validated before use.

### 10.2 Output Encoding

All output rendered to the browser **MUST** be encoded/escaped.

### 10.3 SQL

SQL queries **MUST** use parameterized statements.

String concatenation for SQL with user input is forbidden.

### 10.4 Secrets

Secrets **MUST NOT** be:

- hard-coded
- logged
- returned in error messages
- committed to version control

### 10.5 Authorization

Authorization **MUST** protect the object, not only the route.

Authentication is not authorization.

---

## 11. Performance

### 11.1 Hot Path Rule

Performance optimization in hot paths **MUST** be measured, not guessed.

### 11.2 External Calls

All external calls (HTTP, database, filesystem, cache) **MUST** have:

- explicit timeouts
- retry limits where applicable
- failure handling

### 11.3 Hidden I/O

Hidden I/O in public methods **MUST NOT** be present.

I/O must be visible at the orchestration layer.

---

## 12. Testing

### 12.1 Behavior Tests

Tests **MUST** verify behavior, not implementation trivia.

### 12.2 Security and Performance

Security and performance-sensitive behavior **MUST** have negative and boundary tests where practical.

### 12.3 Mocking

Over-mocked tests that freeze internals **SHOULD NOT** be present.

Prefer real objects where the cost is low.

---

## 13. References

For full PHP 8.0-8.5 feature adoption details, see:

```text
profiles/languages/php.d/modern-php-features.md
```

For clean code principles applicable across languages, see:

```text
profiles/languages/php.d/clean-code.md
```
