# How-To Document Structure Correction

Date: 2026-05-16
Stage: V5.9 Governance Baseline Classification
Status: GREEN

## 1. Gate Result

Command:

```bash
php tooling/governance/check-how-to-document-structure.php
```

Raw output: `EVIDENCE/v5.9-codex/raw/how-to-document-structure-after.txt`

Result:

| Metric           | Value |
|------------------|------:|
| Scanned files    |    19 |
| Violations found |     0 |
| Exit code        |     0 |

## 2. Findings And Fixes

| Finding                       | File                                            | Real issue? | Fix                                                                                                                     | Result |
|-------------------------------|-------------------------------------------------|------------:|-------------------------------------------------------------------------------------------------------------------------|--------|
| Unclosed markdown fence       | `.agents/how-to/how-to-architecture.md`         |         YES | Removed stale trailing fence after the final section.                                                                   | PASS   |
| Duplicate heading number `7`  | `.agents/how-to/how-to-dependency-injection.md` |         YES | Renumbered Root Application Container heading to `7.6` to sit under container ownership.                                | PASS   |
| Unclosed markdown fence       | `.agents/how-to/how-to-dogfooding.md`           |         YES | Removed stale trailing fence after the final law.                                                                       | PASS   |
| Duplicate heading number `8`  | `.agents/how-to/how-to-production-readiness.md` |         YES | Renumbered Immediate Next Actions to `29`.                                                                              | PASS   |
| Duplicate heading number `9`  | `.agents/how-to/how-to-production-readiness.md` |         YES | Renumbered Forbidden Work section to `30`.                                                                              | PASS   |
| Duplicate heading number `8`  | `.agents/how-to/how-to-runtime-composition.md`  |         YES | Renumbered Root Application Container cross-reference to `14`.                                                          | PASS   |
| Duplicate heading number `76` | `.agents/how-to/how-to-unit-test.md`            |         YES | Renumbered Gate Self-Test Cross-Reference to `90`.                                                                      | PASS   |
| Unclosed markdown fence       | `.agents/how-to/how-to-unit-test.md`            |         YES | Removed stale wrapper fence and extra closing fences; corrected a four-backtick close to a normal three-backtick fence. | PASS   |

## 3. Review Notes

- No governance content was weakened.
- No rules were removed.
- Changes were limited to markdown fence balance and duplicate heading numbers.
- The gate now passes without allowlists or false-positive suppressions.
