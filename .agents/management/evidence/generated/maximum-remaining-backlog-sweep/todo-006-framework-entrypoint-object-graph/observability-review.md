# Observability Review

## Metrics

`RunApplication` keeps the existing optional `MetricsCollector` dependency and continues to record request count, error count, and latency when present.

## Logs

No new logs are introduced.

## Sensitive Data

No new data is logged, returned, or written to evidence.

## Debuggability

Debuggability improves because the default dispatch graph has an explicit Configuration owner: `BuildRunApplication`.

## Decision

No observability regression found.
