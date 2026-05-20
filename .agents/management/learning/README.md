# Lesson: PHPUnit Named Arguments

## Observation

PHPUnit assertions may use `@no-named-arguments`.

## Failure Seen

Named arguments caused PHPStan warnings or runtime errors.

## Rule

Do not use named arguments with APIs that explicitly forbid them.

## Applies To

- PHPUnit assertions
- vendor APIs with unstable parameter names

## Checker

Manual / PHPStan

## Status

Active

```

---

# Lesson: Public Surface Leakage

## Observation

Components that put behavior in PublicSurface are leaking internals.

## Failure Seen

Components with large PublicSurface folders had internal logic exposed.

## Rule

PublicSurface receives and delegates. It does not own behavior.

## Applies To

- All components

## Checker

check-public-surface.php

## Status

Active
```

---

# Lesson: Forbidden Folder Drift

## Observation

Agents create forbidden folders like Services/, Managers/, Helpers/ when not explicitly forbidden.

## Failure Seen

Adapters/, Commands/, UseCases/ folders appeared in components.

## Rule

Folder still says flow or capability. Concept words are not folder names.

## Applies To

- All components

## Checker

check-advanced-pattern-folder-violations.php

## Status

Active

```

---

# Lesson: Fake Builder Anti-Pattern

## Observation

Builders are sometimes created solely to hide long constructors, bypass DI gates, or wrap single `new` calls without adding assembly logic.

## Failure Seen

Builder classes like `AuthBuilder`, `ServiceBuilder` that merely forward arguments to constructors, adding no cohesive assembly responsibility.

## Rule

A builder is valid only when it owns a real cohesive assembly responsibility. Builders that exist only to hide constructors, bypass DI, or act as thin wrappers are forbidden. See `how-to-architecture.md` Section 13.3.1.

## Applies To

- All component configuration
- All assembly code
- All builder creation decisions

## Checker

Manual review, `check-constructor-bloat.php`, governance review

## Status

Active

---

# Lesson: Service-Locator Builder Anti-Pattern

## Observation

Builders may reach into the container at runtime to resolve dependencies instead of receiving them through their own constructor or assembly graph.

## Failure Seen

Builder classes calling `$container->get()` or `Container::get()` inside build methods, masking missing dependencies and breaking DI discipline.

## Rule

Builders must not use service-locator pattern. Dependencies must be injected at assembly time or passed explicitly. Builders operate at composition time, not as runtime service locators. See `how-to-architecture.md` Section 13.3.1, `how-to-dependency-injection.md` Section 3.4.

## Applies To

- All builder classes
- All assembly code
- All Configuration/Builders/ code

## Checker

Manual review, governance review, `how-to-clean-code.md` Section 13

## Status

Active

---

# Lesson: Assembly Graph Naming Strategy

## Observation

Generic builder names like `AuthBuilder`, `ServiceFactory`, `Manager` hide responsibility and create dumping grounds.

## Failure Seen

Builders named after patterns rather than the assembly graph they own, leading to god objects and unclear ownership.

## Rule

Builder names should describe the assembly graph they own: `TokenAuthenticationGraph`, `DatabaseConnectionGraph`, `EventDispatcherAssembly`. Forbidden names include generic terms like `*BuilderHelper`, `*ServiceFactory`, `*Manager`. See `how-to-architecture.md` Section 13.3.3.

## Applies To

- All builder class names
- All Configuration/Builders/ classes
- All assembly graph classes

## Checker

Manual review, naming conventions, `how-to-coding-standards.md` Section 20

## Status

Active

---

# Lesson: DSL Method Naming Strategy

## Observation

DSL methods with generic names like `set()`, `add()`, `config()` are unclear and force users to read implementation details.

## Failure Seen

Fluent APIs with generic method chains that read like setter calls rather than domain sentences.

## Rule

DSL method names should be domain-specific, chainable, and self-documenting. Public DSLs should read like sentences describing assembly intent. Prefer `withTokenValidation()` over `setAuth()`, `usingS3Storage()` over `setDriver()`. See `how-to-dependency-injection.md` Section 8.6.

## Applies To

- All fluent DSL methods
- All public assembly APIs
- All builder method names

## Checker

Manual review, DSL readability, `how-to-dependency-injection.md` Section 8

## Status

Active

---

Agents read learning files before repeating similar work.