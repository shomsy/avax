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

Agents read learning files before repeating similar work.