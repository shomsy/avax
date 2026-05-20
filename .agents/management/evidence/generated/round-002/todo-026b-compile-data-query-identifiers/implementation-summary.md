# Implementation Summary — TODO-026b

## What Changed

### Production Code
- `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php`

### Tests
- `tests/Unit/Components/DataStack/Persistence/SqlInjection/CompileDataQueryIdentifierSafetyTest.php` (new, 41 tests)

## Identifier Safety Design

### Problem
`CompileDataQuery::buildSql()` interpolated identifiers (select columns, join tables/on, orderBy field/direction, condition fields/operators) directly into SQL strings via `sprintf`/concat with zero wrapping, validation, or allowlist. This is a confirmed SQL injection vector if any `DataQuery` fields originate from user input.

### Solution: Dual Defense — Validate + Wrap

Every identifier passes through two gates before reaching the SQL string:

**Gate 1: Strict Allowlist Validation**
- Each identifier segment validated against `/^[a-zA-Z_]\w*$/`
- JOIN types validated against explicit allowlist (INNER, LEFT, RIGHT, CROSS, etc.)
- Comparison operators validated against explicit allowlist (=, !=, <, >, LIKE, IN, etc.)
- ORDER BY directions validated against explicit allowlist (ASC, DESC)
- JOIN ON clauses validated against character allowlist (alphanumeric, underscore, dot, space, comparison operators)

**Gate 2: Double-Quote Wrapping**
- Valid identifiers wrapped in `"` with embedded `"` escaped as `""`
- Dotted identifiers (`table.column`) split and each segment wrapped separately
- Wildcard `*` passed through unwrapped (standard SQL behavior)

### What Each Attack Surface Now Does

| Attack Surface | Before | After |
|---|---|---|
| `$select` columns | Raw `implode()` | Each validated + wrapped |
| `$join['table']` | Raw interpolation | Validated + wrapped |
| `$join['on']` | Raw interpolation | Character allowlist + identifier wrapping |
| `$join['type']` | Raw interpolation | Explicit allowlist (8 types) |
| `$orderBy` field | Raw interpolation | Validated + wrapped |
| `$orderBy` direction | Raw interpolation | Explicit allowlist (ASC/DESC) |
| `$condition['field']` | Raw `sprintf` | Validated + wrapped |
| `$condition['operator']` | Raw `sprintf` | Explicit allowlist (15 operators) |
| Table name (from entity) | Raw `camelToSnake` | Validated + wrapped |

### Design Decisions

1. **No Grammar dependency**: CompileDataQuery is in `Persistence`, not `Database`. Depending on `Grammar` would create a cross-boundary dependency. Instead, identifier validation and wrapping is self-contained.

2. **No fake abstractions**: No `IdentifierSanitizerService`, `SqlIdentifierHelper`, or generic wrappers. The validation and wrapping logic lives directly in the flow that owns SQL compilation.

3. **Fail-closed**: Any invalid identifier throws `InvalidArgumentException` immediately. No degraded fallback, no silent stripping.

4. **JOIN ON clause handling**: The ON clause is a compound expression (e.g., `users.id = posts.user_id`). It is validated by character allowlist first, then individual identifiers within it are parsed and wrapped via regex.

5. **Preserved behavior**: Valid queries compile to the same logical SQL, with identifiers now safely quoted. Bindings extraction is unchanged.

## Advanced OOP Quality

- Class name says responsibility: `CompileDataQuery` — a flow that compiles data queries
- Method names say exact action: `wrapIdentifier`, `validateJoinType`, `validateOperator`, `validateDirection`
- Constants define explicit allowlists, not magic strings
- No generic Service/Manager/Helper/Util
- No fake abstractions or empty wrappers
- `InvalidArgumentException` carries context (the invalid value and expected format)
