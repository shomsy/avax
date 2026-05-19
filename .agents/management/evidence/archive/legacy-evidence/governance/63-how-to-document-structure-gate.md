# How-To Document Structure Gate

## Implementation

`tooling/governance/check-how-to-document-structure.php`

Scans `.agents/how-to/how-to-*.md` for:
- Banned draft/AI phrases (e.g., "Below is a fully expanded governance draft")
- Duplicate heading numbers within same document
- Broken markdown fences (unbalanced ` ``` `)
- Suspicious GREEN claim wording

## Fixtures

| Fixture | Expected | Actual | PASS? |
|---|---|---|---|
| `fixtures/how-to-structure/good-how-to.md` | PASS | PASS | ✅ |
| `fixtures/how-to-structure/draft-phrases-how-to.md` | FAIL (banned phrases) | FAIL | ✅ |
| `fixtures/how-to-structure/duplicate-headings-how-to.md` | FAIL (duplicate heading) | FAIL | ✅ |
| `fixtures/how-to-structure/fake-green-wording-how-to.md` | FAIL (fake GREEN) | FAIL | ✅ |
