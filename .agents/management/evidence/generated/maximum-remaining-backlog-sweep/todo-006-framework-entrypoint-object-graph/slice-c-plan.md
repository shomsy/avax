# TODO-006 Slice C Plan

## Source

- Main HEAD at branch creation: `6a642b525`
- Branch: `architecture/todo-006-framework-entrypoint-object-graph-slice-c`
- Worktree: `/home/shomsy/projects/avax-todo-006-c`
- Previous state: Slices A and B merged with accepted YELLOW.

## Remaining TODO-006 Findings

After Slice B, `check-direct-instantiation.php` no longer reports `BootDsl.php`.

Remaining public `App.php` findings:

- `framework/System/PublicSurface/App.php:199` - `OpenHttpRequestScope`
- `framework/System/PublicSurface/App.php:203` - `CloseHttpRequestScope`
- `framework/System/PublicSurface/App.php:282` - `RouteDefinition`
- `framework/System/PublicSurface/App.php:316` - `RuntimeRequest`

## Slice Selection

Slice C moves these public-entrypoint constructions out of `App` by injecting focused collaborators:

- `OpenHttpRequestScope`
- `CloseHttpRequestScope`
- `FrameworkRouteRegistrar`
- `CreateRuntimeRequestFromHttpRequest`

`App` remains the public fluent API and request entrypoint, but it no longer constructs these internal runtime/route objects directly.

## Allowed Files

- `framework/System/PublicSurface/App.php`
- `framework/System/Flows/HandleIncomingHttp/CreateRuntimeRequestFromHttpRequest.php`
- `framework/System/Flows/CreateApplication/CreateApplication.php`
- `framework/System/Configuration/BootDsl/BootDslEngine.php`
- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`
- focused tests under `tests/Unit/Framework/V4RuntimeApp/`
- Slice C evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/`

## Forbidden Files

- `TODO.md`
- `fix-this.md`
- TODO-007/AuthBuilder/Identity files
- unrelated component backlog
- unrelated broad direct-instantiation findings

## Expected Validation

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --filter "AppTest|CreateApplicationTest|BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
vendor/bin/phpstan analyse framework/System/PublicSurface/App.php framework/System/Flows/HandleIncomingHttp/CreateRuntimeRequestFromHttpRequest.php framework/System/Flows/CreateApplication/CreateApplication.php framework/System/Configuration/BootDsl/BootDslEngine.php tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php tests/Unit/Framework/V4RuntimeApp/AppTest.php tests/Unit/Framework/V4RuntimeApp/CreateApplicationTest.php tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php --memory-limit=1G
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/refactor/check-namespace-drift.php
git diff --check
```

Accepted YELLOW:

- broad direct-instantiation backlog outside TODO-006
- runtime-composition four pre-existing HIGH findings outside TODO-006

## Implementation May Proceed

YES.

Ownership is clear: `App` delegates public operations; existing flow/configuration owners receive the object construction.
