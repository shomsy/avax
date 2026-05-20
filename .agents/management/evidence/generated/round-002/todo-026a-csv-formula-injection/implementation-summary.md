# Implementation Summary — TODO-026a CSV Formula Injection Hardening

## Design Decision

CSV formula injection (SAI-0085) was confirmed P1/HIGH. Both `CsvFormat.php` and `CsvFormatter.php` used `fputcsv()` with only delimiter/enclosure escaping (`escape: '\\'`), leaving formula-leading characters (`=`, `+`, `-`, `@`, `\t`, `\r`, `\n`) unescaped.

## Approach

Created a single capability `NeutralizeFormulaCell` in the `Formats/` capability folder. Both `CsvFormat` (capability) and `CsvFormatter` (public surface) delegate to this capability through a private `neutralizeRow()` method.

### Why this approach:

1. **Single source of truth**: Formula neutralization logic lives in one place
2. **No fake abstraction**: `NeutralizeFormulaCell` is a static capability with one method — it does exactly what the name says
3. **No Service/Manager/Helper**: Named by behavior, not by technical category
4. **Fail-closed**: Dangerous cells get a `'` prefix; safe cells pass unchanged
5. **Testable**: The capability can be unit-tested independently of CSV rendering

## Files Changed

| File | Change |
|------|--------|
| `components/HTTP/ContentNegotiation/System/Capabilities/Formats/NeutralizeFormulaCell.php` | NEW — neutralizes formula-prefix cells |
| `components/HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php` | MODIFIED — calls NeutralizeFormulaCell via neutralizeRow() |
| `components/HTTP/ContentNegotiation/System/PublicSurface/CsvFormatter.php` | MODIFIED — calls NeutralizeFormulaCell via neutralizeRow() |
| `tests/Unit/Components/HTTP/ContentNegotiation/CsvFormulaInjectionTest.php` | NEW — 23 tests covering all formula prefixes and both formatters |

## Formula Characters Neutralized

- `=` — Excel/LibreOffice formula start
- `+` — legacy formula start
- `-` — negative number / formula start
- `@` — Excel implicit intersection
- `\t` — tab-prefixed formula bypass
- `\r`, `\n` — carriage return / line feed prefixed formula bypass

## Neutralization Method

Prefix dangerous values with `'` (single quote). This is the standard OWASP-recommended approach for CSV formula injection prevention.
