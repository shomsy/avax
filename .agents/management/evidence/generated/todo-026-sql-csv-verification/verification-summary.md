# Verification Summary — TODO-026 SQL/CSV Injection

Generated: 2026-05-20

## Scope

Verification-only audit of CLUSTER-021: SQL/CSV injection surfaces.
Source findings: SAI-0076, SAI-0077, SAI-0078, SAI-0085, SAI-0087.
No production code was changed.

## Summary Table

| Finding | Area | Classification | Severity | Rationale |
|---|---|---|---|---|
| SAI-0076 | Grammar sprintf table interpolation | PARTIAL | MEDIUM → P1 | sprintf used with %s for wrapped table name; wrap() provides identifier quoting; risk is upstream callers bypassing wrap() |
| SAI-0077 | Grammar sprintf table/column sprintf | PARTIAL | MEDIUM → P1 | Same as SAI-0076; all identifiers pass through wrap() → wrapSegment() with double-quote escaping |
| SAI-0078 | Grammar same SQL interpolation | PARTIAL | MEDIUM → P1 | Same pattern; compiled SQL uses sprintf for template structure, not raw value interpolation |
| SAI-0085 | CSV formula injection (= + - @) | CONFIRMED_P1 | LOW → P1 | CsvFormat.php and CsvFormatter.php use fputcsv with escape='\\' but no formula-prefix escaping; cells starting with = + - @ \t are not sanitized |
| SAI-0087 | IDE noinspection annotations | FALSE_POSITIVE_CANDIDATE | LOW | `// noinspection SqlNoDataSourceInspection` at Grammar.php:388,425,437 are IDE suppressions; not a runtime security risk; valid code-hygiene concern |

## Additional Findings Discovered During Verification

| ID | Area | Classification | Severity | Rationale |
|---|---|---|---|---|
| VER-001 | CompileDataQuery.php raw SQL interpolation | CONFIRMED_P1 | P1 | buildSql() interpolates $select, join table/on, orderBy field, and condition field directly via sprintf/concat without any wrapping or validation; if DataQuery fields originate from user input this is SQL injection |
| VER-002 | DatabaseSessionStore table name | PARTIAL | MEDIUM | sprintf used for table name in prepared statements, but constructor validates against `/^[a-zA-Z_]\w*$/`; table name is constructor-injected not runtime-user-controlled |

## Exploitability Assessment

### SAI-0076/0077/0078 — Grammar sprintf

- **Path**: QueryState → Grammar::compileInsert/Update/Delete → sprintf with %s for table name
- **Mitigation**: Table name passes through `wrap()` → `wrapSegment()` which double-quotes the identifier
- **Residual risk**: If a caller passes a raw string to sprintf without going through wrap(), injection is possible. The Grammar class itself is safe; the risk is in callers.
- **Conclusion**: The finding correctly identifies the pattern, but the Grammar class has mitigation. Severity remains MEDIUM for Grammar itself, P1 for callers that bypass wrap().

### SAI-0085 — CSV Formula Injection

- **Path**: HTTP Accept: text/csv → CsvFormat::format() or CsvFormatter::format() → fputcsv()
- **No mitigation**: fputcsv with escape='\\' only handles CSV delimiter/enclosure escaping
- **Attack**: A cell value `=1+1` or `@SUM(A1:A10)` rendered as CSV would execute as a formula when opened in Excel/LibreOffice
- **Conclusion**: Confirmed. This is a real CSV injection vector. No escaping of formula-leading characters (=, +, -, @, tab, CR, LF).

### VER-001 — CompileDataQuery Raw SQL

- **Path**: DataQuery → CompileDataQuery::buildSql() → sprintf/concat with raw $select, $join['table'], $join['on'], $orderBy field, $condition['field']
- **No mitigation**: No wrap(), no validation, no parameterization for identifiers
- **Attack**: If DataQuery is constructed from user-controlled field names, table names, or join clauses
- **Conclusion**: Confirmed. This is a real SQL injection vector at the persistence compilation layer.

### VER-002 — DatabaseSessionStore

- **Path**: Constructor $table → sprintf in prepared statements
- **Mitigation**: Constructor validates with `/^[a-zA-Z_]\w*$/`
- **Residual risk**: None at runtime; table name is constructor-injected
- **Conclusion**: Mitigated by design. Not exploitable unless the PDO connection is shared with untrusted table name configuration.
