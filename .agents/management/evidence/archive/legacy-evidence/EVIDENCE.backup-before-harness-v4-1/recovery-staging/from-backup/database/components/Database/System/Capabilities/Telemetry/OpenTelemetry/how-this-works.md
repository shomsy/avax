# How This Works

`Telemetry/OpenTelemetry` owns OpenTelemetry-shaped export artifacts without coupling the component to one vendor SDK.

- `SpanBuilder.php` creates `QuerySpan` values.
- `TraceExporter.php` stores/export trace-like payloads.
- `MetricsExporter.php` exports aggregated database metrics.
- `OtelConfig.php` keeps exporter toggles and resource attributes.
- `QuerySpan.php` remains the central span value object for one executed query.

This slice focuses on translation/export surfaces; core telemetry events remain in `Telemetry/Events`.

