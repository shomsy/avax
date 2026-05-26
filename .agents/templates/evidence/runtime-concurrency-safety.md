# Runtime / Concurrency Safety Evidence

## Task

<!-- What task requires this evidence? -->

## Runtime Model

<!-- PHP-FPM / Swoole / RoadRunner / FrankenPHP / ReactPHP / CLI worker / mixed -->

## Request Scope

<!-- How is per-request scope managed? -->

## Shared State

<!-- Is any mutable state shared across requests/workers? How is it protected? -->

## Reset Behavior

<!-- How is state reset between requests in long-lived workers? -->

## Concurrency Model

<!-- Single-threaded / fiber / coroutine / process / thread / event loop -->

## Timeout / Cancellation

<!-- How are timeouts handled? Can operations be cancelled? -->

## Retry / Idempotency

<!-- Are retries safe? How is idempotency guaranteed? -->

## Backpressure

<!-- How is backpressure applied when consumer is slower than producer? -->

## Runtime Adapter Boundary

<!-- Where is the boundary between runtime-agnostic code and runtime-specific code? -->

## Runtime API Leak Risk

<!-- Does any runtime-specific API leak into application/domain code? -->

## Observability

<!-- Logging, metrics, tracing for runtime/concurrency issues -->

## Tests / Evidence

<!-- Links to tests proving runtime safety properties -->

## Review Date

<!-- YYYY-MM-DD -->
