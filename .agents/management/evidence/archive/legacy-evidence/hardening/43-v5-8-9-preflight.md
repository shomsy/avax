# V5.8.9 Preflight

## Date
2026-05-15

## Branch
main

## Commit
a97529645 (V5.8.8 commit)

## Current PHPStan Count
253 errors

## Current Runtime Composition Gate Status
FAIL — 163 findings (3 new from V5.8.7 DispatchConfiguredRoute, 160 pre-existing)

## Current Runtime Assembly Gate Status
FAIL — 3 violations in GraphQLSchema.php (pre-existing, ?? new fallbacks)

## AuthBuilder Error Count
~170 errors (constructor drift across build() method — ~100 PHPStan errors in Auth area alone, rest from callers)

## DispatchConfiguredRoute Leak Count
3 active runtime leaks:
- Line 92: `new ControllerResolver()`
- Line 93: `new ArgumentResolver()`
- Line 99: `new ControllerDispatcher()`

## GraphQLSchema Assembly Finding Count
3 findings:
- Line 52: `$fieldAssembler ?? new AssembleFieldsFromMap()`
- Line 53: `$schemaSerializer ?? new SchemaToArray()`
- Line 54: `$schemaRouter ?? new SchemaRouter()`

## Planned Fix Order
1. Phase A: DispatchConfiguredRoute runtime leaks (3 fixes — move assembly to `fromRegisteredRoutes` factory, inject into runtime)
2. Phase B: AuthBuilder constructor drift (systematic — match build() `new` calls to actual constructor signatures)
3. Phase C: GraphQLSchema runtime assembly (3 fixes — remove ?? new defaults, require injected deps or register defaults in ServiceProvider)
4. Phase D: Remaining PHPStan groups (array shapes, mixed narrowing, return types, always-true instanceof)

## Validation Commands
```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/components/check-component-runtime-assembly.php
php tooling/refactor/check-public-surface.php
php tooling/components/check-hollow-public-surfaces.php
```

## Scope Boundaries
**In scope:** DispatchConfiguredRoute leaks, AuthBuilder constructor drift, GraphQLSchema assembly, remaining PHPStan type strictness.
**Out of scope:** V5.9 Boot DSL, full PublicSurface thinning, Static State proof, EventStore, RuntimeCompilation/JIT, SearchIndex, pre-existing runtime composition debt outside the 3 new DispatchConfiguredRoute leaks.

## Core Rules
- No `?? new` fallback construction
- No private default factory methods as final fix
- No weakening constructors to nullable
- No broad PHPStan ignores
- No test weakening
- Runtime receives ready dependencies
- Configuration assembles
- Missing dependencies fail at container compile/verify/boot
