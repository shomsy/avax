# 05 — Public API and DSL Readability Audit

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Public surface audit — framework and components

## Audit Results

| API / call site            | Current shape                        | Problem                           | Fix now? | Action       |
|----------------------------|--------------------------------------|-----------------------------------|----------|--------------|
| `Avax::create()` / `App`   | Static factory + fluent routes       | Clean intent-first API            | No       | KEEP_CURRENT |
| `App::get/post/put/...`    | Fluent route registration            | Clean                             | No       | KEEP_CURRENT |
| `App::run()`               | Single dispatch                      | Clean                             | No       | KEEP_CURRENT |
| FailureBoundary attributes | 8 PHP attributes + compiled metadata | Clean declarative API             | No       | KEEP_CURRENT |
| MessageBus facade          | Static methods, lazy init            | Minor: static state in facade     | No       | BACKLOG_V5.8 |
| BuildFailureBoundary       | Fluent builder                       | Clean                             | No       | KEEP_CURRENT |
| SystemDesignKit facade     | Single facade class                  | Clean for labs-promoted component | No       | KEEP_CURRENT |
| Queue dispatch             | QueueDispatcher facade               | Clean                             | No       | KEEP_CURRENT |
| Resilience components      | Individual flow classes              | Clean                             | No       | KEEP_CURRENT |
| Doctor commands            | Individual check classes             | Clean                             | No       | KEEP_CURRENT |

## DSL Readability Verdict

No critical DSL issues found. Public surfaces are intent-first and fluent where appropriate.
Known future improvements (V5.7+) are backlogged, not blocking:

- Events fluent DSL (V5.7)
- Boot DSL (V5.9)
- Async/Future DSL (V6.4)
- Scenario DSL (V6.8)
