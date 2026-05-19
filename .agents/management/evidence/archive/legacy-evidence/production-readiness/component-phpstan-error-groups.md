# Component PHPStan Error Groups

Date: 2026-05-03  
Status: RED  
Command:

```bash
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
```

## Current Groups

| Group                       | Evidence                                                                                                                                 | Required Action                                                                     |
|-----------------------------|------------------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------|
| Unknown classes             | API draft previously used `Avax\\API\\...`; legacy tests use `Avax\\HTTP\\...`, `components\\...`, and missing route-definition classes. | Rewrite to canonical namespaces or archive stale tests outside the canonical suite. |
| Stale namespaces            | Composer autoload reports many skipped classes under Identity, Security, DataStack, Application Cache, DataLayer, and legacy tests.      | Repair namespaces and paths; do not add compatibility bridges unless governed.      |
| Wrong named arguments       | Framework tests still call old constructor/method parameter names such as `builder`, `request`, `scopeId`, and `result`.                 | Update tests to current public signatures.                                          |
| Constructor drift           | Session, Operations Events, Cache, and runtime tests call old constructors.                                                              | Update tests or production signatures only when the production API is wrong.        |
| Multi-class files           | Composer skips or misclassifies files with multiple production classes.                                                                  | Split classes into one class/interface/enum per file.                               |
| PHPDoc/generic drift        | Arrays without value types and mixed values appear across components.                                                                    | Add precise list/array PHPDoc or value objects.                                     |
| Real production type errors | Some component code still has namespace and ownership drift, especially Security and Cache.                                              | Repair production code inside the correct owner, then add behavior tests.           |

## Current V2 Draft Note

The existing API Contract draft was normalized after this full PHPStan run. A targeted rerun was requested but blocked
by
the execution environment usage limit after one nullsafe warning was fixed.

## Verdict

Stage 08 remains RED until full PHPStan passes or a conscious baseline is created that does not hide missing classes,
broken autoload, or stale namespaces.
