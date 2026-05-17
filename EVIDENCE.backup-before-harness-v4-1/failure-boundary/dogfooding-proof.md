# Dogfooding Proof

Date: 2026-05-12

## Principle

FailureBoundary must reuse existing AvaX capabilities where they exist. No local duplicates of retry engines, loggers,
or dead-letter stores when a canonical component already provides the behavior.

## Current State

| Behavior                    | Existing AvaX Component                                    | Reused?        | Local Implementation?                     | Status                                                    |
|-----------------------------|------------------------------------------------------------|----------------|-------------------------------------------|-----------------------------------------------------------|
| Response building           | `ResponseFactory` (components/HTTP/Response/)              | YES            | No                                        | GREEN                                                     |
| Middleware contract         | `MiddlewareInterface` (components/HTTP/Middleware/)        | YES            | No                                        | GREEN                                                     |
| HTTP request/response types | `RequestInterface`, `ResponseInterface` (components/HTTP/) | YES            | No                                        | GREEN                                                     |
| Metrics collection          | `MetricsCollector` (components/Operations/Observability/)  | YES (scope)    | No                                        | GREEN                                                     |
| Retry engine                | No Resilience component exists yet                         | N/A            | Yes — `RetryFailedAction`                 | YELLOW — MVP, standalone, designed for future replacement |
| Error reporting             | No Observability logging component wired                   | N/A            | Yes — `ReportFailure` uses `error_log()`  | YELLOW — MVP, designed for Observability integration      |
| Dead-letter queue           | No Queue component exists yet                              | N/A            | Yes — `SendFailureToDeadLetter` logs JSON | YELLOW — MVP, designed for Queue integration              |
| Result mapping              | Uses `ResponseFactory.createErrorResponse()`               | YES            | No                                        | GREEN                                                     |
| Policy compilation          | Follows DataShape attribute reading pattern                | Pattern reused | Yes — `CompileFailurePolicies`            | GREEN — follows established pattern                       |
| Static cache                | Follows DataShape static cache pattern                     | Pattern reused | Yes — `CompiledPolicyCache`               | GREEN — follows established pattern                       |

## Analysis

### GREEN Items (proper dogfooding)

- **Response building:** Uses `ResponseFactory` from `components/HTTP/Response/` for HTTP error responses. No custom
  response building.
- **Middleware contract:** Implements `MiddlewareInterface` from `components/HTTP/Middleware/`. No custom middleware
  interface.
- **HTTP types:** Uses AvaX `RequestInterface` and `ResponseInterface`. No PSR-7 leakage.
- **Compilation pattern:** Follows the same attribute-reading pattern as DataShape. Uses `\ReflectionClass` and
  `\ReflectionAttribute` consistently.
- **Cache pattern:** Static array cache matches DataShape's approach.

### YELLOW Items (MVP placeholders)

- **Retry:** No AvaX Resilience component exists yet. `RetryFailedAction` implements a standalone retry loop with
  none/linear/exponential backoff + jitter. It is designed as a capability that can be replaced when a Resilience
  component is built. This is acceptable MVP behavior, not a duplication violation.
- **ReportFailure:** Uses `error_log()` with structured context. This is an MVP placeholder. When the Observability
  component provides a canonical logging/reporting interface, `ReportFailure` should be updated to use it.
- **DeadLetter:** Logs JSON via `error_log()`. This is an MVP placeholder. When a Queue component with dead-letter
  support exists, `SendFailureToDeadLetter` should be updated to use it.

### No Duplicated Components

- No duplicate logger (ReportFailure uses PHP's built-in error_log, not a custom logger)
- No duplicate retry engine elsewhere in the codebase
- No duplicate dead-letter implementation
- No duplicate response building

## Conclusion

FailureBoundary properly dogsfoods AvaX components where they exist (Response, Middleware, HTTP types, compilation
patterns). MVP placeholders for Retry, ReportFailure, and DeadLetter are intentional and documented. They are designed
for future replacement, not permanent duplication.
