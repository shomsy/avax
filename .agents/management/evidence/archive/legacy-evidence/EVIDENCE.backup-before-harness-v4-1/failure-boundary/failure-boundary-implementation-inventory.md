# FailureBoundary Implementation Inventory

Date: 2026-05-12
Scope: V5.6 Declarative Failure Boundary

## Component Inventory

| Area                    | Exists? | File(s)                                                                 | Real usage?                       | Tested?          | Notes                                                                        |
|-------------------------|---------|-------------------------------------------------------------------------|-----------------------------------|------------------|------------------------------------------------------------------------------|
| FailureBoundary owner   | yes     | `framework/System/Capabilities/FailureBoundary/` (39 PHP files)         | yes (component itself)            | yes              | Canonical owner. ErrorHandling removed.                                      |
| RunProtectedAction      | yes     | `Flows/RunProtectedAction/RunProtectedAction.php`                       | yes (middleware, facade, builder) | yes              | Main try/catch/finally flow. Unwraps FailurePipelineResult.                  |
| FailurePipeline         | yes     | `Capabilities/RunFailurePipeline/RunFailurePipeline.php`                | yes (RunProtectedAction)          | yes              | 6-decision routing: Retry/Fallback/MapToResult/DeadLetter/Rethrow/ReportOnly |
| OnFailure attribute     | yes     | `Foundation/Attributes/OnFailure.php`                                   | yes (compiled, tested)            | yes              | Repeatable. Maps to MapToResult decision.                                    |
| ReportFailure attribute | yes     | `Foundation/Attributes/ReportFailure.php`                               | yes (compiled, tested)            | yes              | channel parsed; level/includeStackTrace not yet enforced.                    |
| Retry attribute         | yes     | `Foundation/Attributes/Retry.php`                                       | yes (compiled, tested)            | yes              | none/linear/exponential + jitter.                                            |
| Fallback attribute      | yes     | `Foundation/Attributes/Fallback.php`                                    | yes (compiled, tested)            | yes              | Invokable handler class.                                                     |
| DeadLetter attribute    | yes     | `Foundation/Attributes/DeadLetter.php`                                  | yes (compiled, tested)            | yes              | Queue name used. MVP: logs JSON via error_log.                               |
| Compiled metadata       | yes     | `CompileFailurePolicies`, `CompiledPolicyCache`, `ResolveFailurePolicy` | yes (runtime resolution)          | yes              | Reflection once, cache forever. Staleness via mtime.                         |
| HTTP middleware         | yes     | `Integration/HttpFailureBoundaryMiddleware.php`                         | no (not registered in kernel yet) | yes              | Wraps $next in RunProtectedAction. Needs kernel wiring.                      |
| Real app adoption       | partial | See adoption-scan.md                                                    | no production routes yet          | yes (unit tests) | Demo controller + E2E tests prove behavior.                                  |
| Timeout attribute       | yes     | `Foundation/Attributes/Timeout.php`                                     | no                                | no               | Compiled but not enforced at runtime. Deferred.                              |
| RecoverWith attribute   | yes     | `Foundation/Attributes/RecoverWith.php`                                 | no                                | no               | Compiled but not enforced at runtime. Deferred.                              |

## Status Summary

- **Fully implemented and tested:** RunProtectedAction, FailurePipeline, OnFailure, ReportFailure, Retry, Fallback,
  DeadLetter, Rethrow, Compiled metadata, HTTP middleware
- **MVP (functional but limited):** ReportFailure (error_log), DeadLetter (JSON log), Retry (standalone)
- **Deferred (compiled but not enforced):** Timeout, RecoverWith
- **Not yet integrated:** HTTP middleware in kernel, real production route usage
