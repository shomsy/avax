# Large Unit Threshold Gate

## Implementation

`tooling/governance/check-large-unit-thresholds.php`

Reports classes/methods exceeding thresholds.

## Thresholds

| Threshold | Value | Severity |
|---|---|---|
| Class lines | 300 | REVIEW |
| Method lines | 50 | REVIEW |
| Constructor dependencies | 8 | REVIEW |
| PublicSurface lines | 150 | REVIEW |
| ServiceProvider lines | 250 | REVIEW |
| Builder lines | 300 | BLOCKER |

## Fixtures

| Fixture | Expected | Actual | PASS? |
|---|---|---|---|
| `fixtures/large-units/small-class.php` | PASS | PASS | ✅ |
| `fixtures/large-units/large-class.php` | REVIEW (>300 lines) | REVIEW | ✅ |
