# TODO-006 Slice B Plan

## Source

- Main HEAD at branch creation: `eae848e54`
- Branch: `architecture/todo-006-framework-entrypoint-object-graph-slice-b`
- Worktree: `/home/shomsy/projects/avax-todo-006-b`
- Previous state: Slice A merged with accepted YELLOW.

## Remaining Finding Inventory

Commands run:

```bash
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-composition-leaks.php
```

Results:

- `check-public-surface.php`: PASS
- `check-direct-instantiation.php`: FAIL with broad existing backlog and TODO-006 residual findings
- `check-runtime-composition-leaks.php`: FAIL with four pre-existing HIGH findings outside TODO-006

Relevant TODO-006 public entrypoint residual findings:

- `framework/System/PublicSurface/BootDsl.php:150`
- `framework/System/PublicSurface/BootDsl.php:151`
- `framework/System/PublicSurface/BootDsl.php:155`
- `framework/System/PublicSurface/BootDsl.php:156`
- `framework/System/PublicSurface/BootDsl.php:159`
- `framework/System/PublicSurface/BootDsl.php:164`
- `framework/System/PublicSurface/App.php:199`
- `framework/System/PublicSurface/App.php:203`
- `framework/System/PublicSurface/App.php:282`
- `framework/System/PublicSurface/App.php:316`

## Slice Selection

Slice B selects `BootDsl::create()` engine assembly.

Why this is the smallest safe slice:

- `BootDsl::create()` is a PublicSurface method.
- Its object graph is cohesive: clock fallback, project/environment value objects, HTTP response/handler, provider registry, and `BootDslEngine`.
- The correct owner already exists in `framework/System/Configuration/BootDsl/`.
- Moving this graph does not require touching TODO-007, identity code, `Avax::create()`, or `App::as*Kernel()`.

## Allowed Files

- `framework/System/PublicSurface/BootDsl.php`
- `framework/System/Configuration/BootDsl/BuildBootDslEngine.php`
- `framework/System/Configuration/BootDsl/BootDslBuilder.php` only if sharing the same internal builder removes duplication without changing public behavior
- `tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php`
- `tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php` only if existing behavior assertions need focused coverage
- evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/todo-006-framework-entrypoint-object-graph/`
- review/merge evidence under `.agents/management/evidence/generated/maximum-remaining-backlog-sweep/`

## Forbidden Files

- `TODO.md`
- `fix-this.md`
- `components/Identity/**`
- TODO-007/AuthBuilder files
- unrelated framework flows/capabilities
- broad direct-instantiation backlog outside the selected BootDsl path
- generated dumps, IDE metadata, screenshots, temp files

## Expected Validation

Focused:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --filter "BootDslTest|V4AppDoesNotDuplicateComponentsTest" --no-coverage
vendor/bin/phpstan analyse framework/System/PublicSurface/BootDsl.php framework/System/Configuration/BootDsl/BuildBootDslEngine.php framework/System/Configuration/BootDsl/BootDslBuilder.php tests/Contract/V4Architecture/V4AppDoesNotDuplicateComponentsTest.php tests/Unit/Framework/Configuration/BootDsl/BootDslTest.php --memory-limit=1G
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-direct-instantiation.php
php tooling/refactor/check-runtime-composition-leaks.php
php tooling/refactor/check-namespace-drift.php
git diff --check
```

Accepted YELLOW:

- broad direct-instantiation backlog outside Slice B
- remaining TODO-006 `App.php` construction findings
- runtime-composition four pre-existing HIGH class discovery findings outside Slice B
- broad PHPStan baseline errors in unrelated tests, if broad PHPStan is run

## Implementation May Proceed

YES.

Ownership is clear: PublicSurface should delegate; Configuration/BootDsl should assemble `BootDslEngine`.
