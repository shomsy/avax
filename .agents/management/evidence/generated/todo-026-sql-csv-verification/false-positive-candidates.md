# False Positive Candidates — TODO-026

## FALSE_POSITIVE_CANDIDATE: SAI-0087 — IDE noinspection annotations

**File**: `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php:388,425,437`

**Evidence**:
Three instances of `// noinspection SqlNoDataSourceInspection` appear in Grammar.php:
- Line 388: before `return sprintf('INSERT INTO %s ...')`
- Line 425: before `return trim(sprintf('UPDATE %s ...'))`
- Line 437: before `return trim(sprintf('DELETE FROM %s ...'))`

**Analysis**:
These are PHPStorm/IDE annotation suppressions to silence "no data source"
warnings for dynamically generated SQL strings. They have zero runtime effect.
They do not suppress any security mechanism, validation, or runtime behavior.

**Why flagged**:
Supplemental static analysis flagged these as "IDE-only annotations in production
code" — a code hygiene concern, not a security finding.

**Recommendation**:
Demote to FALSE_POSITIVE_CANDIDATE for security classification.
The finding is valid as a P3 code-hygiene item (IDE annotations in production code),
but it is not a SQL injection, CSV injection, or any runtime security risk.
Can be addressed during general cleanup (TODO-030) without security urgency.
