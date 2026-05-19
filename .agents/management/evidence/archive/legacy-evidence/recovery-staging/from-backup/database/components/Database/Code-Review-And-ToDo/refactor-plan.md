# Database Refactor Completion Plan

## Goal

Finish the Database component so that `Foundation/Database/refactor.md` is materially complete and aligned with the AI
Prompt architecture/review rules.

## Completed

- Root capability facades remain stable: `Database.php`, `Query.php`, `EntityManager.php`, `Schema.php`,
  `Migrations.php`, `Transactions.php`, `Telemetry.php`.
- Query grammar expansion is present, including `DialectFactory.php` and split dialect/type ownership.
- Query IR phase is complete enough for extension work:
    - nodes normalized
    - builder/transformer/validator/normalizer/cache added
- Type-safe projection slice completed with builder and type inference support.
- Advanced query folders completed with CTE, window, bulk, and upsert helpers.
- Transactions enterprise helpers added.
- Telemetry/OpenTelemetry helper slices added.
- ORM DataLoader slice completed.
- Missing `how-this-works.md` ownership documents added.
- Database PSR-4/autoload issues removed.

## Validation Gates

- `composer dump-autoload -o` must produce no `Foundation/Database` autoload warnings.
- `php -l` across `Foundation/Database/System/Capabilities` must stay clean.
- Database test suite should be run before final merge.

## Remaining Work Policy

- Prefer wiring new helpers into public APIs only when a concrete consumer needs them.
- Reject new generic “Helpers”, “Utils”, or “Support” buckets inside Database.
- Keep docs synchronized with ownership folders after every structural change.

