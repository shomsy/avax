# TODO-006 Slice D Plan

## Source

- Main HEAD at branch creation: `166aca8e5`
- Branch: `architecture/todo-006-framework-entrypoint-object-graph-slice-d`
- Worktree: `/home/shomsy/projects/avax-todo-006-d`
- Previous state: Slices A, B, and C successfully completed and merged into main. Handoff recorded.

## Scope of Slice D

Slice D targets `framework/System/PublicSurface/Avax.php` to clean up inline object-graph assembly inside `Avax::create()` and `bootInternal()`.
The assembly is delegated to a dedicated Configuration helper `BuildAvaxEngine.php` under `framework/System/Configuration/BuildApplication/Builders/`.

## Allowed Files

- `framework/System/Configuration/BuildApplication/Builders/BuildAvaxEngine.php` (New file)
- `framework/System/PublicSurface/Avax.php` (Modified file)
- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php` (Modified test file)
- Slice D evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/`

## Forbidden Files

- `TODO.md`
- `fix-this.md`
- Components outside framework PublicSurface and Configuration
- TODO-007 / AuthBuilder / Identity component files

## Expected Validation

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --filter "AvaxCreateTest|CreateApplicationTest|AppTest|BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
vendor/bin/phpstan analyse framework/System/PublicSurface/Avax.php framework/System/Configuration/BuildApplication/Builders/BuildAvaxEngine.php tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php --memory-limit=1G
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/refactor/check-namespace-drift.php
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-root-evidence-hygiene.php
git diff --check
```

## Implementation May Proceed

YES. Ownership is clear: `Avax` acts as the thin public facade/entrypoint boundary, delegating all internal application creation and kernel configuration logic to the Configuration builder.
