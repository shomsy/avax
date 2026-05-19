# Semantic PHPDoc Gate

## Implementation

`tooling/governance/check-semantic-phpdoc.php`

Scans production PHP files for:
- Missing class/interface/trait/enum docblocks
- Missing public/protected method docblocks
- Banned fake phrases in docblocks

## Fixtures

| Fixture | Expected | Actual | PASS? |
|---|---|---|---|
| `fixtures/semantic-phpdoc/good-class-docblock.php` | PASS (good docblock) | PASS | ✅ |
| `fixtures/semantic-phpdoc/missing-class-docblock.php` | FAIL (missing class doc) | FAIL | ✅ |
| `fixtures/semantic-phpdoc/fake-redundant-docblock.php` | FAIL (banned phrase) | FAIL (detects "Handles things", "Processes data") | ✅ |
| `fixtures/semantic-phpdoc/good-value-object.php` | PASS | PASS | ✅ |
| `fixtures/semantic-phpdoc/good-public-surface.php` | PASS | PASS | ✅ |
