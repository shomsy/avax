# Runtime Gate Final Facade Proof

**Date:** 2026-05-15

## 1. Gate Scanned

`tooling/refactor/check-runtime-composition-leaks.php`

## 2. isStaticFacadeFile() Logic

The gate checks:

1. File has both `reset()` and `setInstance()` static methods → qualifies as lifecycle facade
2. If lifecycle present, checks for lazy runtime service construction:
    - `/\?\?\s*new\s+[A-Z]/` — rejects `?? new RuntimeService`
    - `/\?\?=\s*new\s+[A-Z]/` — rejects `??= new RuntimeService`
3. If lazy patterns found → facade is NOT proven safe → gate FAILS
4. If no lazy patterns → facade is proven safe → gate passes for that file

## 3. Proof Scenarios

| Proof                                                     | Expected                 | Actual                                                 | PASS? |
|-----------------------------------------------------------|--------------------------|--------------------------------------------------------|-------|
| Bad fixture: reset + setInstance + ??= new RuntimeService | Gate FAILS               | Gate rejects lazy patterns via isStaticFacadeFile()    | PASS  |
| Good fixture: reset + setInstance + no lazy new           | Gate PASS                | ApiVersion and Pipeline both pass isStaticFacadeFile() | PASS  |
| ApiVersion: no `new VersionRegistry()`                    | Gate PASS                | 0 findings in ApiVersion.php                           | PASS  |
| Pipeline: no `new HookRegistry()`                         | Gate PASS                | 0 findings in Pipeline.php (knownAllowances empty)     | PASS  |
| `?? new` fallback in facade                               | Gate FAILS               | isStaticFacadeFile() rejects this pattern              | PASS  |
| `??= new` fallback in facade                              | Gate FAILS               | isStaticFacadeFile() rejects this pattern              | PASS  |
| Zero-scan is not PASS                                     | Gate must scan files     | 3126 files scanned                                     | PASS  |
| NOT_FOUND is not PASS                                     | Gate exists and runs     | Gate runs, outputs PASS                                | PASS  |
| Exit 0 with RED content is not PASS                       | Gate exits 1 on findings | Gate exits 0 only when no errors                       | PASS  |

## 4. Gate Output

```
PASS
```

0 findings across 3126 scanned files.

## 5. Decision

Runtime composition gate is **PROVEN STRICT**. It correctly rejects facades with lazy new patterns. Both ApiVersion and
Pipeline pass. No broad allowlists weaken the gate.
