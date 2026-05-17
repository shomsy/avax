# Composer Integrity Report

Composer problem:

- `composer.lock` was out of sync with `composer.json`.
- `phpstan/phpstan` constraint in `composer.json` was `^1.10` but `composer.lock` had `2.1.54`.
- `vimeo/psalm` was required in `composer.json` but not present in `composer.lock`.
- `psalm.xml` exists, implying Psalm is intentionally used.
- Numerous unused packages were present in `vendor/` and `composer.lock` that are no longer required in `composer.json`.

Decision:

- Update `phpstan/phpstan` constraint to `^2.1` in `composer.json` to match the installed 2.1.54 version.
- Run `composer update --lock` to sync the lock file and remove unused abandoned packages.
- Run `composer require --dev vimeo/psalm --with-all-dependencies` to formally lock and install Psalm since `psalm.xml`
  is present.

Files changed:

- `composer.json` (phpstan constraint updated)
- `composer.lock` (synced, removed 107 unused packages, installed psalm)

Commands run:

```bash
composer update --lock
composer require --dev vimeo/psalm --with-all-dependencies
composer validate --no-check-publish
composer dump-autoload -o
```

Result:

- `composer validate` passes perfectly.
- Clean dependency tree without leftover legacy packages.

Remaining risk:

- None for composer integrity.
