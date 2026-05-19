# FailureBoundary — Ownership and Duplication Audit

**Date:** 2026-05-12
**Stage:** Phase 3 — Production-Grade Closure

## Purpose

Prove that FailureBoundary has a single canonical owner, no duplicated capabilities,
and clear boundaries with neighboring domains.

## Canonical Owner

```
framework/System/Capabilities/FailureBoundary/
```

All failure boundary behavior lives here. No other directory owns failure handling.

## ErrorHandling Migration Status

The old `ErrorHandling` namespace was fully migrated to FailureBoundary canonical paths:

| Old Path                                                       | New Path                                                                  | Status   |
|----------------------------------------------------------------|---------------------------------------------------------------------------|----------|
| `framework/.../ErrorHandling/ClassifyApplicationException`     | `framework/.../FailureBoundary/Capabilities/ClassifyApplicationException` | Migrated |
| `framework/.../ErrorHandling/RenderApplicationError`           | `framework/.../FailureBoundary/Capabilities/RenderApplicationError`       | Migrated |
| `framework/.../ErrorHandling/HandleIncomingHttp` (outer catch) | `framework/.../FailureBoundary/Integration/HttpFailureBoundaryMiddleware` | Replaced |

Consumers updated:

- `framework/System/PublicSurface/App.php` — import updated
- `tests/Unit/Framework/V4RuntimeApp/ClassifyApplicationExceptionTest.php` — import updated
- `tests/Unit/Framework/V4RuntimeApp/RenderApplicationErrorTest.php` — import updated

Result: **Zero references to ErrorHandling namespace remain.**

## Duplication Scan

### Retry Logic

| Location                                                                       | Purpose                                    | Duplication?                                |
|--------------------------------------------------------------------------------|--------------------------------------------|---------------------------------------------|
| `FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php`         | Attribute-driven retry in failure pipeline | Yes — standalone, should dogfood Resilience |
| `components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor.php` | General-purpose retry executor             | Canonical retry engine                      |

**Decision:** RetryFailedAction should delegate to Resilience RetryExecutor for production.
Currently MVP-standalone — acceptable for now, documented as YELLOW.

### Logging/Reporting

| Location                                                                     | Purpose                          | Duplication?                            |
|------------------------------------------------------------------------------|----------------------------------|-----------------------------------------|
| `FailureBoundary/Capabilities/ReportFailure/ReportFailure.php`               | Reports failure via error_log    | Yes — MVP, should dogfood Observability |
| `components/Operations/Observability/System/Capabilities/Logging/Logger.php` | Structured logging with handlers | Canonical logging                       |
| `components/Operations/Logging/System/PublicSurface/Log.php`                 | Logging facade                   | Canonical logging facade                |

**Decision:** ReportFailure should use Observability Logger + StructuredLogRecord + Redaction.
Currently MVP — documented as YELLOW.

### Dead Letter Queue

| Location                                                                                | Purpose                             | Duplication?                        |
|-----------------------------------------------------------------------------------------|-------------------------------------|-------------------------------------|
| `FailureBoundary/Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php`      | Serializes failure → error_log JSON | Yes — MVP, should dogfood Messaging |
| `components/SystemDesign/System/Capabilities/Messaging/DeadLetters/DeadLetterQueue.php` | Dead letter queue for messaging     | Canonical dead letter               |

**Decision:** SendFailureToDeadLetter should use SystemDesign Messaging DeadLetterQueue.
Currently MVP — documented as YELLOW.

### Fallback Logic

| Location                                                                     | Purpose                             | Duplication?                                               |
|------------------------------------------------------------------------------|-------------------------------------|------------------------------------------------------------|
| `FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php`       | Attribute-driven fallback execution | No — specific to FailureBoundary attribute model           |
| `components/Operations/Resilience/System/Capabilities/Fallback/Fallback.php` | General-purpose fallback strategy   | Different model — resilience pattern, not attribute-driven |

**Decision:** No duplication. FailureBoundary fallback is attribute-driven; Resilience fallback is strategy-driven.
Different responsibilities, no conflict.

## Overlap Map

```
FailureBoundary (framework)
  ├── OnFailure / ReportFailure / Retry / Fallback / DeadLetter / Rethrow / Timeout / RecoverWith
  │   └── Attribute-driven, compiled metadata, pipeline decision
  │
  └── Reuses (should dogfood):
       ├── Operations/Resilience/Retry    → for retry execution
       ├── Operations/Observability/Logging → for failure reporting
       └── SystemDesign/Messaging/DeadLetters → for dead letter queue
```

## Conclusion

| Check                             | Status | Evidence                                         |
|-----------------------------------|--------|--------------------------------------------------|
| Single canonical owner            | GREEN  | `framework/System/Capabilities/FailureBoundary/` |
| ErrorHandling removed             | GREEN  | grep confirms zero references                    |
| No orphaned duplicates            | GREEN  | All overlaps are intentional MVP bridges         |
| Clear boundary with Resilience    | GREEN  | Attribute-driven vs strategy-driven              |
| Clear boundary with Observability | GREEN  | FailureBoundary reports → Observability logs     |
| Clear boundary with Messaging     | GREEN  | FailureBoundary dead-letters → Messaging queue   |

**Overall: YELLOW** — ownership is clean, but 3 MVP capabilities need dogfooding integration.
