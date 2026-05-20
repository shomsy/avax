# Validation Output — TODO-003 CSRF/Session Authority

Generated: 2026-05-20

## composer validate --no-check-publish

```
./composer.json is valid
```

## composer dump-autoload -o

```
Generating optimized autoload files
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
Generated optimized autoload files containing 9351 classes
```

## vendor/bin/phpunit --filter "Csrf|Session" --no-coverage

```
PHPUnit 10.5.63 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.5.5
Configuration: /home/shomsy/projects/avax/phpunit.xml

.................                                                 17 / 17 (100%)

Time: 00:00.266, Memory: 60.00 MB

OK (17 tests, 51 assertions)
```

## PHPStan (changed files only)

```
Note: Using configuration file /home/shomsy/projects/avax/phpstan.neon.
(no output — clean)
```

## php tooling/refactor/check-runtime-composition-leaks.php

```
PASS
```

## php tooling/governance/check-governance-index-current.php

```
GREEN: Governance index is current.
```

## php tooling/governance/check-root-evidence-hygiene.php

```
Root Evidence Hygiene Check
===========================

Files: 9
Directories: 0
Total size: 32.4KB

GREEN: Root evidence hygiene PASSED.
```
