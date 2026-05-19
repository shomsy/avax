# Code Style Governance

## Status

**MANDATORY** - This document defines non-negotiable code style rules for PHP projects.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

---

## Rule: Constructor Promotion

**Status:** MANDATORY  
**Scope:** All PHP 8+ code  
**Severity:** HIGH  
**Enforcement:** PHPStan, Rector

### Requirement

Classes **MUST** use constructor promotion for dependencies.

```php
// GOOD
public function __construct(
    private readonly QueryBuilder $queryBuilder,
    private readonly Cache $cache,
) {}
```

```php
// BAD - multi-line without promotion
public function __construct(QueryBuilder $queryBuilder, Cache $cache) {
    $this->queryBuilder = $queryBuilder;
    $this->cache = $cache;
}
```

### Single Dependency Shortcut

If a constructor has exactly one dependency, it **MAY** be written on one line:

```php
public function __construct(private readonly QueryBuilder $queryBuilder) {}
```

### GREEN Criteria

- All new classes use constructor promotion
- No manual property assignment in constructors unless justified

### RED Criteria

- Manual `$this->property = $property` assignments without promotion

---

## Rule: Nullable Type Format

**Status:** RECOMMENDED  
**Scope:** All PHP code  
**Severity:** LOW  
**Enforcement:** Project-level preference

### Recommendation

Nullable types are clearer when written as `Type|null` rather than `?Type`.
Projects may adopt either style consistently; the key is uniformity within a codebase.

---

## Rule: Named Arguments

**Status:** MANDATORY  
**Scope:** All public API calls, especially configuration and factory calls  
**Severity:** MEDIUM  
**Enforcement:** Manual review, Rector

### Requirement

Method calls with multiple arguments **SHOULD** use named arguments for clarity.

```php
// GOOD
$this->runtime->context()->finishRequest(
    response: $response,
    timeout: 30,
);

new CacheEntry(
    key: 'users.active',
    value: $data,
    ttl: 3600,
);
```

### GREEN Criteria

- Complex method calls (3+ args) use named arguments

### RED Criteria

- Calls with 5+ positional arguments that could use names

---

## Rule: Return Type Presence

**Status:** MANDATORY  
**Scope:** All functions and methods  
**Severity:** HIGH  
**Enforcement:** PHPStan

### Requirement

All functions and methods **MUST** have explicit return types.

```php
// GOOD
public function getName(): string
public function findAll(): array
private function computeValue(): int|null
```

### GREEN Criteria

- All methods have return types

### RED Criteria

- Missing return type on any method

---

## Rule: Throws Documentation

**Status:** MANDATORY  
**Scope:** All public and protected methods that throw  
**Severity:** MEDIUM  
**Enforcement:** PHPStan, manual review

### Requirement

Methods that throw exceptions **MUST** document exceptions with `@throws`.

```php
/**
 * @throws InvalidArgumentException
 * @throws DatabaseConnectionFailed
 */
public function execute(): void
```

### GREEN Criteria

- All throwing methods have `@throws` tags

### RED Criteria

- Exceptions thrown without documentation

---

## Rule: Unhandled Exceptions

**Status:** MANDATORY  
**Scope:** All exception handling  
**Severity:** HIGH  
**Enforcement:** PHPStan

### Requirement

All caught exceptions **MUST** be handled explicitly. Silent catching is FORBIDDEN.

```php
// GOOD
try {
    $this->process($data);
} catch (ValidationException $e) {
    $this->logger->warning('Validation failed', ['error' => $e->getMessage()]);
    throw new BadRequestException('Invalid data', previous: $e);
}

// BAD
try {
    $this->process($data);
} catch (\Exception) {
    // do nothing
}
```

### GREEN Criteria

- No empty catch blocks
- All catches either rethrow, log, or handle explicitly

### RED Criteria

- Empty catch blocks
- Swallowed exceptions

---

## Rule: Static Anonymous Functions

**Status:** RECOMMENDED  
**Scope:** Anonymous functions in performance-critical paths  
**Severity:** LOW  
**Enforcement:** Rector, PHPStorm inspections

### Requirement

Anonymous functions **SHOULD** be static when they do not use `$this`.

```php
// GOOD - static when $this not used
$callback = static function (): int {
    return rand(1, 100);
};

// REQUIRED - must NOT be static when $this used
$callback = function (): int {
    return $this->compute();
};
```

### GREEN Criteria

- Non-closure lambdas are static where possible

### RED Criteria

- Non-static closures where static would work

---

## Rule: Pipe Operator Usage

**Status:** RECOMMENDED  
**Scope:** Chain of transformations  
**Severity:** LOW  
**Enforcement:** Manual review

### Requirement

The pipe operator **SHOULD** be used when it improves readability over nesting.

```php
// GOOD - pipe
$result = $data
    |> $this->normalize(...)
    |> $this->transform(...)
    |> $this->finalize(...);

// GOOD - simple chain
$result = $query
    ->where('active', true)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();
```

### GREEN Criteria

- Chains of transformations use pipe or fluent methods

### YELLOW Criteria

- Simple 2-3 call chains may use either style

---

## Rule: Import Statements

**Status:** MANDATORY  
**Scope:** All PHP files  
**Severity:** HIGH  
**Enforcement:** PHPStan, PSR-12

### Requirement

Fully qualified class names **MUST** be replaced with imports.

```php
// GOOD
use App\Domain\User;
use App\Domain\Repository;

$user = new User();

// BAD
$user = new \App\Domain\User();
```

### GREEN Criteria

- No FQN usage in code (except for truly global functions/constants)

### RED Criteria

- Unnecessary FQN like `\App\Domain\User()`

---

## Rule: Null Object Pattern

**Status:** RECOMMENDED  
**Scope:** Return types that may be null  
**Severity:** MEDIUM  
**Enforcement:** Manual review

### Requirement

When returning nullable objects, consider null object pattern for common cases.

```php
// GOOD - explicit null
public function findById(int $id): User|null

// GOOD - null object when appropriate
public function getCurrentUser(): User
```

### GREEN Criteria

- Return types use `Type|null` consistently

### RED Criteria

- Inconsistent nullable handling

---

## Rule: Type Safety in Collections

**Status:** MANDATORY  
**Scope:** Arrays and collections  
**Severity:** HIGH  
**Enforcement:** PHPStan

### Requirement

Arrays and collections **MUST** have documented key and value types.

```php
/**
 * @return array<int, User>
 */
public function getUsers(): array

/**
 * @return array<string, Config>
 */
private function loadConfigs(): array
```

### GREEN Criteria

- Arrays have documented types in PHPDoc or are strictly typed via generics

### RED Criteria

- `array` without type documentation in return types

---

## Rule: Semantic PHPDoc

**Status:** MANDATORY
**Scope:** All production PHP classes, interfaces, traits, enums, and public/protected methods
**Severity:** HIGH
**Enforcement:** Manual review, gate tooling

### Cross-Reference

For the full semantic PHPDoc governance including class PHPDoc, method PHPDoc, tag rules, flow/action documentation,
review criteria, gate rules, and phased adoption, see:

```text
.agents/how-to/how-to-document.md — Semantic PHPDoc Rule
.agents/how-to/how-to-code-review.md — §24 PHPDoc Review Rule
```

### Summary

```
Every class explains its responsibility.
Every public/protected method explains its action.
Every exception is documented.
Every complex type boundary is documented.
No PHPDoc may lie, drift, decorate, or merely repeat code.
PHPDoc must make the architecture easier to read.
```

---

## Completion Language

A component or stage **MUST NOT** be marked GREEN unless:

- All MANDATORY rules pass
- All tests pass
- PHPStan passes with no errors
- No BLOCKER or HIGH severity issues remain

A stage **MUST** be YELLOW if:

- Validation passes but mandatory evidence incomplete
- MEDIUM issues remain deferred without owner

A stage **MUST** be RED if:

- Tests fail
- PHPStan fails with errors
- BLOCKER rule violations exist