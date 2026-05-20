# Negative Test Proof — TODO-026a CSV Formula Injection

## Tests Added: 23

### NeutralizeFormulaCell Unit Tests (13)

| Test | Proves |
|------|--------|
| `test_equals_formula_cell_is_neutralized` | `=SUM(A1:A10)` → `'`=SUM(A1:A10)` |
| `test_plus_formula_cell_is_neutralized` | `+1+2` → `'+1+2` |
| `test_minus_formula_cell_is_neutralized` | `-1+2` → `'-1+2` |
| `test_at_formula_cell_is_neutralized` | `@SUM(A1)` → `'@SUM(A1)` |
| `test_tab_prefixed_formula_cell_is_neutralized` | `\t=cmd|...` → `'\t=cmd|...` |
| `test_cr_prefixed_cell_is_neutralized` | `\r=malicious` → `'\r=malicious` |
| `test_lf_prefixed_cell_is_neutralized` | `\n=malicious` → `'\n=malicious` |
| `test_normal_text_passes_unchanged` | `Hello World` → `Hello World` |
| `test_numeric_string_passes_unchanged` | `12345` → `12345` |
| `test_negative_number_is_neutralized` | `-42` → `'-42` |
| `test_empty_string_passes_unchanged` | `""` → `""` |
| `test_non_string_value_is_cast_to_string` | `42` → `"42"` |
| `test_null_value_is_cast_to_empty_string` | `null` → `""` |

### CsvFormat Integration Tests (5)

| Test | Proves |
|------|--------|
| `test_csv_format_neutralizes_formula_cells` | Multi-row data with formula cells are escaped |
| `test_csv_format_preserves_normal_numeric_cells` | Safe numeric cells have no `'` prefix |
| `test_csv_format_handles_single_row_array` | Single key-value row is escaped |
| `test_csv_format_handles_non_array_data` | Non-array input passes through unchanged |
| `test_csv_format_neutralizes_all_dangerous_prefixes` | All 5 dangerous prefixes in one row |

### CsvFormatter Integration Tests (5)

| Test | Proves |
|------|--------|
| `test_csv_formatter_neutralizes_formula_cells` | Multi-row data with formula cells are escaped |
| `test_csv_formatter_preserves_normal_numeric_cells` | Safe numeric cells have no `'` prefix |
| `test_csv_formatter_handles_single_row_array` | Single key-value row is escaped |
| `test_csv_formatter_handles_non_array_data` | Non-array input passes through unchanged |
| `test_csv_formatter_neutralizes_all_dangerous_prefixes` | All 5 dangerous prefixes in one row |

## Test Results

```
PHPUnit 10.5.63
OK (46 tests, 82 assertions)
```

All negative tests pass. Formula characters are neutralized. Normal cells are preserved.
