# ADR 0010: Testing and Quality Gates

## Status

Accepted

## Context

Every increment must be verified. Tests protect behavior, not implementation. Quality gates prevent structural decay.

## Decision

Per-increment quality gates:
1. `composer validate`
2. `composer dump-autoload`
3. `php -l` for changed PHP files
4. PHPUnit targeted tests
5. Full PHPUnit suite when migration affects shared paths
6. Static analysis (PHPStan)
7. Code style (PHP-CS-Fixer)
8. Documentation validation
9. Governance review

## Consequences

- Each migrated behavior must have tests
- Unit tests cover meaningful units
- Integration tests prove framework/component collaboration
- Feature tests prove HTTP, console, worker boot paths
- State leak tests exist for worker requests
- Governance compliance is verified per increment