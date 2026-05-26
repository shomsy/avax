# AntiPattern Dictionary Completion

Task: Engineering Canon Convergence dictionary completion.

## Expected Entries

| Entry | Status |
|---|---|
| `.agents/dictionary/antipatterns/analysis-paralysis.md` | present |
| `.agents/dictionary/antipatterns/architecture-theater.md` | present |
| `.agents/dictionary/antipatterns/blob-god-object.md` | present |
| `.agents/dictionary/antipatterns/cut-and-paste-programming.md` | present |
| `.agents/dictionary/antipatterns/fake-abstraction.md` | present |
| `.agents/dictionary/antipatterns/generic-bucket.md` | present |
| `.agents/dictionary/antipatterns/golden-hammer.md` | present |
| `.agents/dictionary/antipatterns/service-locator.md` | present |
| `.agents/dictionary/antipatterns/shallow-tests.md` | present |
| `.agents/dictionary/antipatterns/spaghetti-code.md` | present |
| `.agents/dictionary/antipatterns/stovepipe-system.md` | present |

## Required Heading Format

Each entry uses:

```text
# AntiPattern: <Name>
## What It Is
## Symptoms
## Why It Is Dangerous
## Common AI Failure Mode
## How to Fix
## Allowed Exceptions
## Severity
```

## Checker Enforcement

`tooling/governance/check-antipatterns.php` and `tooling/governance/check-engineering-canon-traceability.php` require the exact dictionary entry set and required headings.
