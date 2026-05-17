> **SUPERSEDED** by `14-final-production-closure-report.md` and `15-final-status-normalization.md`.
> This document reflects the MVP-era state. For current status, see the Phase 3 closure reports.

# FailureBoundary Adoption Scan (MVP Era — Superseded)

Date: 2026-05-12

## Per-Attribute Adoption

| Attribute     | Defined | Compiled | Used in real flow                 | E2E tested | Status       |
|---------------|---------|----------|-----------------------------------|------------|--------------|
| OnFailure     | yes     | yes      | yes (demo controller + E2E)       | yes        | GREEN        |
| ReportFailure | yes     | yes      | yes (demo controller + E2E)       | yes        | GREEN        |
| Retry         | yes     | yes      | no                                | unit only  | YELLOW       |
| Fallback      | yes     | yes      | no                                | unit only  | YELLOW       |
| DeadLetter    | yes     | yes      | no                                | unit only  | YELLOW       |
| Rethrow       | yes     | yes      | yes (middleware default behavior) | unit only  | YELLOW       |
| Timeout       | yes     | yes      | not enforced                      | not tested | RED/deferred |
| RecoverWith   | yes     | yes      | not enforced                      | not tested | RED/deferred |

## Definitions

- **Defined:** Attribute class exists in `Foundation/Attributes/`
- **Compiled:** Attribute is recognized by `CompileFailurePolicies` and included in compiled policy
- **Used in real flow:** Attribute affects behavior in a non-test code path (demo controller counts)
- **E2E tested:** Test exercises the attribute through the full boundary (not just unit assertion)

## Status Criteria

- **GREEN:** Defined, compiled, used in real flow, E2E tested
- **YELLOW:** Defined, compiled, unit tested, but no real flow adoption OR functional MVP
- **RED/deferred:** Defined, compiled, but NOT enforced at runtime

## Details

### OnFailure — GREEN

- Defined as `#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]`
- Compiled by `CompileFailurePolicies` into `FailureAction` with `MapToResult` decision
- Used by demo controller on methods that throw mapped exceptions
- E2E test proves mapped exception returns configured status code

### ReportFailure — GREEN

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled; `channel` field read, `level` and `includeStackTrace` parsed but not yet enforced
- Used by demo controller
- E2E test proves `error_log` output when failure occurs

### Retry — YELLOW

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `retryMaxAttempts`, `retryBackoff`, `retryDelayMs`, `retryJitter`
- Functional retry loop in `RetryFailedAction` (none/linear/exponential + jitter)
- No real production flow uses it yet
- Unit tests prove retry success and exhaustion

### Fallback — YELLOW

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `fallbackClass`
- Functional fallback invocation in `RunFallbackAction`
- No real production flow uses it yet
- Unit tests prove fallback handler execution

### DeadLetter — YELLOW

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `deadLetterQueue`
- MVP implementation logs JSON via `error_log`
- No real production flow uses it yet
- Unit tests prove dead-letter serialization

### Rethrow — YELLOW

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `rethrowExcept` array
- Used by `ClassifyFailure` to determine which exceptions to always rethrow
- Default behavior for unmapped exceptions is rethrow
- Unit tests prove rethrow behavior

### Timeout — RED/deferred

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `timeoutMs` on `FailurePolicy`
- **Not enforced** — no capability acts on `timeoutMs`
- Requires fiber-level or `pcntl_alarm` support for real enforcement
- Intentionally deferred to runtime-level implementation

### RecoverWith — RED/deferred

- Defined as `#[Attribute(Attribute::TARGET_METHOD)]`
- Compiled into `recoverWithClass` on `FailurePolicy`
- **Not enforced** — no capability invokes recovery handler
- Requires well-defined recovery handler interface
- Intentionally deferred to future implementation

## Real Usage Locations

| File                                                       | Attributes Used          | Methods                                               |
|------------------------------------------------------------|--------------------------|-------------------------------------------------------|
| `examples/failure-boundary-demo/DemoFailureController.php` | OnFailure, ReportFailure | `throwsValidation`, `throwsRuntime`, `reportsFailure` |

## Conclusion

Core attributes (OnFailure, ReportFailure) are adopted and E2E tested. Retry, Fallback, DeadLetter, and Rethrow are
functional but lack real production adoption. Timeout and RecoverWith are intentionally deferred — compiled but not
enforced.
