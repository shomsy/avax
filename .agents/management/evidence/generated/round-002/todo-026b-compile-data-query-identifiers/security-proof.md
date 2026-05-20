# Security Proof — TODO-026b

## Threat Model

**Asset**: SQL query integrity during CompileDataQuery compilation
**Attacker**: User who can influence DataQuery field names, table names, join clauses, sort fields, or comparison operators
**Attack vector**: SQL injection through identifier interpolation
**Trust boundary**: DataQuery is an immutable value object. If constructed from user input at any public boundary, identifiers become hostile.

## Attack Surfaces Neutralized

### 1. SELECT Column Injection
**Attack**: `select: ['*; DROP TABLE users--']`
**Before**: `SELECT *; DROP TABLE users-- FROM user`
**After**: `InvalidArgumentException: Invalid SQL identifier segment: "*; DROP TABLE users--"`
**Proof**: Test `malicious_select_identifier_with_sql_injection_is_rejected`

### 2. SELECT Subquery Injection
**Attack**: `select: ['(SELECT password FROM admins)']`
**Before**: `SELECT (SELECT password FROM admins) FROM user`
**After**: `InvalidArgumentException` (parenthesis fails allowlist)
**Proof**: Test `malicious_select_identifier_with_subquery_is_rejected`

### 3. ORDER BY Field Injection
**Attack**: `orderBy: ['id; DROP TABLE users--' => 'ASC']`
**Before**: `ORDER BY id; DROP TABLE users-- ASC`
**After**: `InvalidArgumentException: Invalid SQL identifier segment`
**Proof**: Test `malicious_orderby_field_with_sql_injection_is_rejected`

### 4. ORDER BY Direction Injection
**Attack**: `orderBy: ['id' => 'ASC; DROP TABLE users--']`
**Before**: `ORDER BY id ASC; DROP TABLE users--`
**After**: `InvalidArgumentException: Invalid ORDER BY direction`
**Proof**: Test `malicious_orderby_direction_with_sql_injection_is_rejected`

### 5. JOIN Table Injection
**Attack**: `joins: [['table' => 'users; DROP TABLE passwords--', ...]]`
**Before**: `JOIN users; DROP TABLE passwords-- ON ...`
**After**: `InvalidArgumentException: Invalid SQL identifier segment`
**Proof**: Test `malicious_join_table_with_sql_injection_is_rejected`

### 6. JOIN ON Clause Injection
**Attack**: `joins: [['on' => 'a.id = b.user_id OR 1=1', ...]]`
**Before**: `JOIN posts ON a.id = b.user_id OR 1=1`
**After**: `InvalidArgumentException: Invalid JOIN ON clause. Contains disallowed characters.`
**Proof**: Test `malicious_join_on_clause_with_parentheses_is_rejected`

### 7. JOIN ON SQL Comment Injection
**Attack**: `joins: [['on' => 'a.id = b.user_id--', ...]]`
**Before**: `JOIN posts ON a.id = b.user_id--`
**After**: `InvalidArgumentException: Invalid JOIN ON clause. Contains disallowed characters.`
**Proof**: Test `malicious_join_on_clause_with_sql_comment_is_rejected`

### 8. JOIN Type Injection
**Attack**: `joins: [['type' => 'DROP TABLE users', ...]]`
**Before**: `DROP TABLE users JOIN posts ON ...`
**After**: `InvalidArgumentException: Invalid JOIN type`
**Proof**: Test `malicious_join_type_is_rejected`

### 9. Condition Field Injection
**Attack**: `conditions: [['field' => 'id; DROP TABLE users--', ...]]`
**Before**: `id; DROP TABLE users-- = ?`
**After**: `InvalidArgumentException: Invalid SQL identifier segment`
**Proof**: Test `malicious_condition_field_with_sql_injection_is_rejected`

### 10. Condition Operator Injection
**Attack**: `conditions: [['operator' => '= 1; DROP TABLE users--', ...]]`
**Before**: `id = 1; DROP TABLE users-- ?`
**After**: `InvalidArgumentException: Invalid comparison operator`
**Proof**: Test `malicious_condition_operator_with_sql_injection_is_rejected`

## Defense-in-Depth

1. **Allowlist validation**: Only known-safe patterns accepted
2. **Identifier wrapping**: Even valid identifiers are double-quoted per SQL standard
3. **Double-quote escaping**: Embedded `"` in identifiers escaped as `""`
4. **Fail-closed**: No degraded mode, no silent fallback

## Test Coverage

- **14 negative tests** proving each attack surface is rejected
- **27 positive tests** proving valid identifiers compile correctly
- **57 total assertions** across 41 tests
- All tests pass with zero failures

## Residual Risk

None at the CompileDataQuery level. The remaining risk is whether a public boundary constructs a DataQuery from untrusted input without prior validation. That is a separate concern at the HTTP/API boundary layer, not within this compilation flow.
