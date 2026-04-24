# OpenTelemetry

The Database telemetry capability now includes an OpenTelemetry-shaped export surface under `Telemetry/OpenTelemetry`.

Included pieces:

- `SpanBuilder`
- `TraceExporter`
- `MetricsExporter`
- `OtelConfig`
- `QuerySpan`

The component stays vendor-neutral. These classes build and export trace/metric payloads without forcing one concrete
SDK into the core Database package.
