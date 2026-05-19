# Final Decision — TODO-026 SQL/CSV Injection Verification

Generated: 2026-05-20

## Verification Status: VERIFICATION_COMPLETE

## Finding Disposition

| Finding | Original Severity | Classification | New Recommendation |
|---|---|---|---|
| SAI-0076 | MEDIUM | PARTIAL | Keep PARTIAL; Grammar wrap() mitigates; risk is upstream callers |
| SAI-0077 | MEDIUM | PARTIAL | Keep PARTIAL; same as SAI-0076 |
| SAI-0078 | MEDIUM | PARTIAL | Keep PARTIAL; same as SAI-0076 |
| SAI-0085 | LOW | CONFIRMED_P1 | PROMOTE to P1; CSV formula injection is real and unmitigated |
| SAI-0087 | LOW | FALSE_POSITIVE_CANDIDATE | DEMOTE to P3 code-hygiene; IDE annotations are not a security risk |

## New Findings Discovered

| ID | Classification | Severity | Area |
|---|---|---|---|
| VER-001 | CONFIRMED_P1 | P1 | CompileDataQuery raw SQL interpolation — no wrap/validation on identifiers |
| VER-002 | PARTIAL | MEDIUM | DatabaseSessionStore — mitigated by constructor allowlist regex |

## Recommended fix-this.md Status Changes

1. **SAI-0085**: Promote from LOW to P1. Add to TODO-026 remediation batch as the primary
   security fix. Remediation shape: add formula-prefix escaping to CsvFormat.php and
   CsvFormatter.php; add negative tests for formula characters.

2. **SAI-0076/0077/0078**: Keep as PARTIAL with note that Grammar.wrap() provides
   identifier quoting. The real risk is in the QueryBuilder/QueryState assembly path.
   Recommend a follow-up audit of whether untrusted input can reach QueryState
   identifiers without validation.

3. **SAI-0087**: Demote to P3 code-hygiene or merge into TODO-030 (low-risk cleanup).
   Not a security finding.

4. **VER-001** (new): Add as CONFIRMED_P1 to TODO-026 or create a new SQL injection
   TODO. This is a more direct SQL injection vector than the Grammar sprintf findings
   because CompileDataQuery has no identifier wrapping at all.

5. **VER-002** (new): Document as ACCEPTED_EXCEPTION_CANDIDATE. DatabaseSessionStore
   validates table name at construction with allowlist regex.

## Evidence Paths

- `.agents/management/evidence/generated/todo-026-sql-csv-verification/context-loaded.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/verification-summary.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/false-positive-candidates.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/validation-output.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/final-decision.md`

## Remaining Risk

- **P1 CSV injection** (SAI-0085) requires remediation in CsvFormat.php and CsvFormatter.php
- **P1 SQL injection** (VER-001) requires remediation in CompileDataQuery.php
- **PARTIAL SQL interpolation** (SAI-0076/0077/0078) requires upstream caller audit
- No negative tests exist for CSV formula injection or SQL injection in the affected files
- 114 Grammar/SessionStore tests pass but none test the specific injection vectors identified

## Commit

No commit made yet. Evidence files written. Awaiting governance review before commit.
