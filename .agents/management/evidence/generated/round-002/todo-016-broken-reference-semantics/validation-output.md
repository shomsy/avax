# Validation Output — TODO-016

## Commands run from worktree: /home/shomsy/projects/avax-todo-016
## Branch: cleanup/todo-016-broken-reference-semantics

### composer validate --no-check-publish
```
./composer.json is valid
```

### composer dump-autoload -o
```
Generating optimized autoload files
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
Generated optimized autoload files containing 9353 classes
```

### php tooling/refactor/check-broken-reference-semantics.php
```
=== BROKEN REFERENCE SEMANTICS AUDIT ===

SKIPPED (OPTIONAL_PHP_EXTENSION): 2 refs
  - Memcached
  - Redis

SKIPPED (TEST_FIXTURE): 1 refs
  - NonExistentResourceType

SKIPPED (OPTIONAL_VENDOR): 3 refs
  - Spiral\RoadRunner\Http\PSR7Worker
  - Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample
  - Symplify\RuleDocGenerator\ValueObject\RuleDefinition

PASS: 0 active broken references (6 total refs classified)
```

### php tooling/refactor/check-namespace-drift.php
```
PASS
```

### vendor/bin/phpunit --filter "SagaReferenceSemantics|MiddlewareFailureReference" --no-coverage
```
OK (18 tests, 37 assertions)
```

### vendor/bin/phpstan analyse components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php components/HTTP/Middleware/System/Flows/RunMiddlewarePipeline/MiddlewarePipelineFailed.php --memory-limit=1G --error-format=raw --no-progress
```
(Note: Using configuration file phpstan.neon.)
(no errors — clean)
```

### php tooling/governance/check-governance-index-current.php
```
GREEN: Governance index is current.
```

### php tooling/governance/check-root-evidence-hygiene.php
```
GREEN: Root evidence hygiene PASSED.
```

### php tooling/refactor/check-component-suite-structure.php
```
PASS
```

## Summary
All validation gates: GREEN
Broken references: 0 (was 5)
PHPStan on changed files: clean
Focused tests: 18 pass, 0 fail
