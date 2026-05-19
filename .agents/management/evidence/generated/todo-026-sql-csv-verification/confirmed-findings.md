# Confirmed Findings — TODO-026

## CONFIRMED_P1: SAI-0085 — CSV Formula Injection

**Files**:
- `components/HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php:18,21,24,25`
- `components/HTTP/ContentNegotiation/System/PublicSurface/CsvFormatter.php:18,21,24,25`

**Evidence**:
Both files use `fputcsv($handle, $row, escape: '\\')` without any formula-prefix escaping.
PHP's `fputcsv()` only escapes the delimiter (`,`) and enclosure (`"`) characters.
It does NOT escape formula-leading characters: `=`, `+`, `-`, `@`, `\t`.

**Attack scenario**:
If application data contains values like `=cmd|'/C calc'!A0` or `@SUM(A1:A10)`,
the CSV output will contain these raw values. When opened in Excel/LibreOffice,
they execute as formulas/macros.

**No existing test coverage** for CSV formula injection was found.

**Recommendation**: Promote to P1. Remediation requires prefixing cells that start
with `=`, `+`, `-`, `@`, or tab characters with a neutral prefix (e.g., single quote `'`
or tab `\t` prefix). Add negative tests proving formula characters are escaped.

---

## CONFIRMED_P1: VER-001 — CompileDataQuery Raw SQL Interpolation

**File**: `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php:69-100,74,87,121`

**Evidence**:
The `buildSql()` method interpolates the following values directly into SQL strings:
- `$select` (implode of DataQuery->select array) — line 69-70
- `$join['type']`, `$join['table']`, `$join['on']` — line 74
- `$field` and `$direction` in orderBy — line 87
- `$condition['field']` and `$condition['operator']` in WHERE — line 121

None of these values pass through identifier wrapping, validation, or parameterization.

**Attack scenario**:
If a DataQuery is constructed with user-controlled field names, table names,
or join clauses, the resulting SQL will contain unsanitized input.

**No existing test coverage** for SQL injection in CompileDataQuery was found.

**Recommendation**: Promote to P1. Remediation requires either:
(a) routing identifiers through the Grammar wrap() method, or
(b) validating identifiers against a strict allowlist pattern, or
(c) treating CompileDataQuery as an internal-only compilation layer with
    documented trust boundaries.

---

## PARTIAL: SAI-0076/0077/0078 — Grammar sprintf with wrap()

**File**: `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php:388-389,425-426,437-438`

**Evidence**:
The Grammar class uses `sprintf('INSERT INTO %s ...', $table, ...)` where `$table`
is the result of `$this->wrap($queryState->from)`. The wrap() method calls
wrapSegment() which double-quotes identifiers: `'"'.str_replace('"', '""', $segment).'"'`.

The sprintf is used for SQL sentence structure (INSERT INTO, UPDATE, DELETE FROM),
not for raw value interpolation. Values use `?` placeholders.

**Mitigation present**: All table/column identifiers pass through wrap() → wrapSegment().

**Residual risk**: The finding correctly identifies that sprintf is used with SQL
structure strings. The risk is not in Grammar itself but in callers that might
bypass wrap() or pass untrusted data to the QueryState without validation.

**Recommendation**: Keep as PARTIAL. The Grammar layer is mitigated. The real
risk is in the QueryBuilder/QueryState assembly path. Requires audit of whether
untrusted input can reach QueryState.from/.columns/.joins without validation.

---

## PARTIAL: VER-002 — DatabaseSessionStore sprintf

**File**: `components/HTTP/Session/System/Capabilities/Storage/DatabaseSessionStore.php:30,50,58`

**Evidence**:
The constructor validates the table name against `/^[a-zA-Z_]\w*$/` before assignment.
The table name is then used in `sprintf('SELECT payload FROM %s WHERE id = ?', $this->table)`
within prepared statements. The session ID is properly parameterized.

**Mitigation present**: Allowlist regex on table name at construction time.

**Residual risk**: None at runtime. The table name is a constructor dependency,
not a runtime user input. Risk only exists if an untrusted party can configure
the PDO connection or session store table name.

**Recommendation**: Demote to ACCEPTED_EXCEPTION_CANDIDATE. The design is safe
because table name is constructor-injected and validated.
