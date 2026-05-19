# Proposed fix-this.md Delta — TODO-026 Verification Results

Generated: 2026-05-20
Source: TODO-026 verification commit 0954ef171
Type: Planning/reconciliation — does NOT edit fix-this.md

---

## Change 1: Promote SAI-0085 to P1 — CSV Formula Injection

| Field | Value |
|---|---|
| Source finding ID | SAI-0085 |
| Current status | NEEDS_VERIFICATION / LOW |
| Proposed status | CONFIRMED / P1 HIGH |
| Evidence path | `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md` |
| Evidence path (summary) | `.agents/management/evidence/generated/todo-026-sql-csv-verification/verification-summary.md` |
| Affected files | `components/HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php`, `components/HTTP/ContentNegotiation/System/PublicSurface/CsvFormatter.php` |
| Exact TODO section | Insert new P1 TODO after TODO-019 (or replace TODO-026 with split) |
| Suggested TODO title | TODO-026a: Escape CSV formula injection cells before rendering |
| Problem | CsvFormat.php and CsvFormatter.php use `fputcsv()` with `escape='\\'` but do not prefix cells starting with `=`, `+`, `-`, `@`, or tab characters. Values like `=cmd|'/C calc'!A0` execute as formulas when opened in Excel/LibreOffice. |
| Why it matters | CSV formula injection is a confirmed attack vector (OWASP CSV Injection). Any user-supplied data exported as CSV can trigger arbitrary formula execution. |
| Target state | All cell values starting with `=`, `+`, `-`, `@`, or `\t` are prefixed with a neutral character (e.g., `'` or `\t`) before fputcsv. Negative tests prove formula characters are escaped. |
| Validation command | `vendor/bin/phpunit --filter "CsvFormat|CsvFormatter|CsvInjection" --no-coverage` |
| Done when | CSV formula prefix escaping implemented in CsvFormat.php and CsvFormatter.php; negative tests for `=`, `+`, `-`, `@`, `\t` prefix characters pass; validation GREEN |
| Owner | AvaX maintainer |
| Expiry | Next remediation batch |

---

## Change 2: Add VER-001 as new P1 — CompileDataQuery Identifier Interpolation

| Field | Value |
|---|---|
| Source finding ID | VER-001 (new, discovered during verification) |
| Current status | N/A (not previously tracked) |
| Proposed status | CONFIRMED / P1 HIGH |
| Evidence path | `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md` |
| Evidence path (summary) | `.agents/management/evidence/generated/todo-026-sql-csv-verification/verification-summary.md` |
| Affected files | `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php` |
| Exact TODO section | Insert new P1 TODO after TODO-026a (or alongside) |
| Suggested TODO title | TODO-026b: Sanitize identifier interpolation in CompileDataQuery SQL compilation |
| Problem | CompileDataQuery::buildSql() interpolates `$select` columns, `$join['table']`, `$join['on']`, `$orderBy` field/direction, and `$condition['field']` directly into SQL strings via sprintf/concat without any identifier wrapping, validation, or allowlist. If DataQuery fields originate from user input, this is SQL injection. |
| Why it matters | Unlike the base Grammar class which uses `wrap()` for all identifiers, CompileDataQuery has zero identifier protection. This is a more direct SQL injection vector than the Grammar sprintf findings. |
| Target state | All identifiers in CompileDataQuery are either: (a) routed through Grammar::wrap(), (b) validated against a strict allowlist pattern `/^[a-zA-Z_]\w*$/`, or (c) documented as internal-only with explicit trust boundaries. Negative tests prove malicious identifiers are rejected or escaped. |
| Validation command | `vendor/bin/phpunit --filter "CompileDataQuery|SqlInjection" --no-coverage` |
| Done when | Identifier sanitization implemented in CompileDataQuery.php; negative tests for SQL injection via field/table/join names pass; validation GREEN |
| Owner | AvaX maintainer |
| Expiry | Next remediation batch |

---

## Change 3: Demote SAI-0087 to P3 Code Hygiene

| Field | Value |
|---|---|
| Source finding ID | SAI-0087 |
| Current status | NEEDS_VERIFICATION / LOW |
| Proposed status | FALSE_POSITIVE_CANDIDATE / P3 LOW |
| Evidence path | `.agents/management/evidence/generated/todo-026-sql-csv-verification/false-positive-candidates.md` |
| Affected files | `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php:388,425,437` |
| Exact TODO section | Merge into TODO-030 (low-risk cleanup) or keep as P3 in TODO-026 disposition |
| Problem | Three `// noinspection SqlNoDataSourceInspection` IDE annotations exist in production code. These are PHPStorm suppressions with zero runtime effect. |
| Why it matters | Not a security risk. Valid code-hygiene concern but does not warrant P1/P2 remediation urgency. |
| Target state | IDE annotations removed or moved to a project-level inspection configuration file. |
| Validation command | `grep -rn "noinspection" components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php` |
| Done when | Annotations removed or relocated; no functional behavior change |
| Owner | AvaX maintainer |
| Expiry | TODO-030 batch |

---

## Change 4: Keep SAI-0076/0077/0078 as PARTIAL — Grammar sprintf with wrap() mitigation

| Field | Value |
|---|---|
| Source finding IDs | SAI-0076, SAI-0077, SAI-0078 |
| Current status | NEEDS_VERIFICATION / MEDIUM |
| Proposed status | PARTIAL / MEDIUM (no promotion, no demotion) |
| Evidence path | `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md` |
| Affected files | `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php` |
| Exact TODO section | Update TODO-026 disposition with verification result |
| Problem | Grammar uses sprintf for SQL sentence structure (INSERT INTO, UPDATE, DELETE FROM) with %s placeholders for table names. |
| Mitigation | All table/column identifiers pass through `wrap()` → `wrapSegment()` which double-quotes identifiers. Values use `?` placeholders. |
| Residual risk | Risk is in callers that bypass wrap() or pass untrusted data to QueryState without validation. Requires upstream caller audit. |
| Target state | Audit of QueryBuilder/QueryState assembly paths to confirm untrusted input cannot reach identifiers without validation. If gaps found, escalate to P1. |
| Validation command | Upstream audit: trace QueryState.from/.columns/.joins sources through QueryBuilder, Database public surface, and any HTTP-to-query paths |
| Done when | Upstream caller audit complete; either gaps found and escalated, or trust boundaries documented and accepted |
| Owner | AvaX maintainer |
| Expiry | Next security audit cycle |

---

## Change 5: Classify VER-002 as ACCEPTED_EXCEPTION_CANDIDATE — DatabaseSessionStore

| Field | Value |
|---|---|
| Source finding ID | VER-002 (new, discovered during verification) |
| Current status | N/A (not previously tracked) |
| Proposed status | ACCEPTED_EXCEPTION_CANDIDATE / MEDIUM |
| Evidence path | `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md` |
| Affected files | `components/HTTP/Session/System/Capabilities/Storage/DatabaseSessionStore.php` |
| Exact TODO section | Add to Accepted Exceptions section or document in TODO-026 disposition |
| Problem | DatabaseSessionStore uses sprintf for table name in prepared statements. |
| Mitigation | Constructor validates table name against `/^[a-zA-Z_]\w*$/`. Table name is constructor-injected, not runtime user-controlled. |
| Residual risk | None at runtime. Risk only exists if an untrusted party can configure the PDO connection or session store table name. |
| Target state | Document as accepted exception with owner, risk, and mitigation. No code change required. |
| Validation command | None required; design is safe by construction |
| Done when | Accepted exception documented with owner, risk, mitigation, and expiry |
| Owner | AvaX maintainer |
| Expiry | Review at next security audit cycle |

---

## Proposed TODO-026 Structural Change

Current TODO-026 is a single NEEDS_VERIFICATION entry. The verification results warrant splitting it:

```
TODO-026 (original): NEEDS_VERIFICATION → mark as VERIFIED with disposition summary

TODO-026a (new): P1 HIGH — CSV formula injection (SAI-0085)
TODO-026b (new): P1 HIGH — CompileDataQuery SQL injection (VER-001)
TODO-026c (new): PARTIAL/MEDIUM — Grammar sprintf upstream audit (SAI-0076/0077/0078)
TODO-026d (new): ACCEPTED_EXCEPTION — DatabaseSessionStore (VER-002)
TODO-026e (new): P3 LOW — IDE annotation cleanup (SAI-0087) → merge into TODO-030
```

Alternatively, keep TODO-026 as the parent and add two new P1 TODOs for the confirmed findings.

---

## Summary of Proposed Changes to fix-this.md

| Change | Action | Target Location |
|---|---|---|
| SAI-0085 → P1 | New TODO-026a or new standalone P1 TODO | P1 Queue after TODO-019 |
| VER-001 → P1 | New TODO-026b or new standalone P1 TODO | P1 Queue after TODO-026a |
| SAI-0087 → P3 | Demote and merge into TODO-030 | P3 Queue / TODO-030 |
| SAI-0076/0077/0078 → PARTIAL | Update TODO-026 disposition | TODO-026 section |
| VER-002 → ACCEPTED_EXCEPTION | Add to Accepted Exceptions or TODO-026 disposition | Accepted Exceptions section |
| TODO-026 status | Change NEEDS_VERIFICATION → VERIFIED | TODO-026 header |
| CLUSTER-021 | Update cluster disposition in finding-clusters.md | Review-reconciliation evidence |

---

## Validation Commands for Remediation Batches

### TODO-026a (CSV formula injection):
```bash
vendor/bin/phpunit --filter "CsvFormat|CsvFormatter|CsvInjection" --no-coverage
```

### TODO-026b (CompileDataQuery SQL injection):
```bash
vendor/bin/phpunit --filter "CompileDataQuery|SqlInjection|DataQuery" --no-coverage
```

### TODO-026c (Grammar upstream audit):
```bash
# No code changes expected; audit evidence only
# Trace QueryState identifier sources through QueryBuilder and Database public surface
```

---

## Evidence Paths

- `.agents/management/evidence/generated/todo-026-sql-csv-verification/context-loaded.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/verification-summary.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/false-positive-candidates.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/validation-output.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/final-decision.md`
- `.agents/management/evidence/generated/todo-026-sql-csv-verification/fix-this-delta.md` (this file)
