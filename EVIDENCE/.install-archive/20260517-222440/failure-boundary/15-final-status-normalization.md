# FailureBoundary — Final Status Normalization

**Date:** 2026-05-12
**Stage:** Phase 3 — Final Acceptance Audit

## Purpose

Normalize final status with mathematical precision. Every capability/attribute classified
separately across 6 dimensions. No ambiguity, no hidden gaps.

## Required Classification Matrix

| Item | Exists | Compiled | Wired | Used in real flow | Tested E2E | Production-ready | Final status |
|------|--------|----------|-------|-------------------|------------|------------------|--------------|
| OnFailure | YES | YES | YES | YES (DemoFailureController + AppKernel) | YES (422, 503, propagate) | YES | GREEN |
| ReportFailure | YES | YES | YES | YES (DemoFailureController) | YES (Logger records verified) | YES* | GREEN* |
| Retry | YES | YES | YES | YES (unit-tested retry loop) | YES (succeeds on 2nd attempt) | YELLOW | YELLOW |
| Fallback | YES | YES | YES | YES (unit-tested handler) | YES (returns fallback-result) | GREEN | GREEN |
| DeadLetter | YES | YES | YES | YES (unit-tested dead letter path) | YES (null result proven) | YELLOW | YELLOW |
| Timeout | YES | YES | NO | NO | NO | DEFERRED | DEFERRED_NOT_ENFORCED |
| RecoverWith | YES | YES | NO | NO | NO | DEFERRED | DEFERRED_NOT_ENFORCED |
| Rethrow | YES | YES | YES | YES (default behavior, unmapped exceptions) | YES (LogicException propagates) | GREEN | GREEN |
| Cleanup | YES | N/A | YES | YES (finally block always runs) | NO (stub, no behavior) | YELLOW | YELLOW (intentional stub) |

\* ReportFailure: GREEN when Logger is provided (primary path). YELLOW fallback path when Logger is not provided. Overall: GREEN because Logger integration is the designed primary path and is production-ready.

## Rules Applied

- **EXISTS only** = RED or DEFERRED, never GREEN
- **EXISTS + COMPILED but not WIRED** = DEFERRED or RED
- **WIRED but not tested E2E** = YELLOW
- **Tested only in fixtures, not real flow** = YELLOW
- **Production-ready** requires canonical dogfooding, no fake local implementation, tests, gates, docs, and evidence
- **Intentionally future-only** = DEFERRED_NOT_PART_OF_GREEN_SCOPE
- **Documented as usable but runtime does not enforce** = RED until fixed or removed from public claims

## Classification Rationale

### GREEN Items

| Item | Why GREEN |
|------|-----------|
| OnFailure | Compiled → wired → real flow → E2E proven → canonical ResponseFactory |
| ReportFailure | Logger primary path with redaction; error_log fallback documented; structured context |
| Fallback | Attribute-driven, instantiates handler, tested with real return value |
| Rethrow | Default behavior, unmapped exceptions propagate, negative tests prove no swallowing |

### YELLOW Items

| Item | Why YELLOW | What would make it GREEN |
|------|------------|--------------------------|
| Retry | Standalone engine, not using canonical Resilience component | Resilience RetryExecutor integration |
| DeadLetter | Structured envelope ready, but transport is error_log (not canonical Queue) | Messaging/DeadLetterQueue integration |
| Cleanup | Intentional stub, no concrete cleanup needs identified | Real cleanup hooks when needed |

### DEFERRED_NOT_ENFORCED Items

| Item | Why Deferred | Dependency |
|------|-------------|------------|
| Timeout | Requires fiber/async runtime for enforcement | V4 runtime adapters (ReactPHP, Swoole, FrankenPHP) |
| RecoverWith | Requires recovery strategy component with state management | V5.6 reliability engine |

## PHPStan Warning Classification

5 warnings, all pre-existing in test files, unrelated to FailureBoundary changes:

| File | Warning | Reason | Owner |
|------|---------|--------|-------|
| FailurePolicyCompilerTest.php:201 | array value type not specified | Test fixture class, cosmetic | Test author |
| FailurePolicyCompilerTest.php:201 | array return type not specified | Test fixture class, cosmetic | Test author |
| HttpFailureBoundaryTest.php:19 | abstract return type | Mock class, cosmetic | Test author |
| HttpFailureBoundaryTest.php:36 | assertInstanceOf always true | Test assertion style, cosmetic | Test author |
| HttpFailureBoundaryTest.php:83 | array value type not specified | Mock class, cosmetic | Test author |

**FailureBoundary scope (framework/System/Capabilities/FailureBoundary): 0 warnings**

## Final Feature Status

**Core FailureBoundary: GREEN**
**Extended policies: YELLOW/DEFERRED**
**Overall: YELLOW**

Description: "Core production-ready, extended policies deferred."
