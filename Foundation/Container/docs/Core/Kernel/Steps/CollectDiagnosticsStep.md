# CollectDiagnosticsStep

## Quick Summary

- Collects resolution diagnostics (timings, duration, instance type, success) from `StepTelemetryRecorder`.
- Writes a compact diagnostics payload into `KernelContext` metadata so other parts of the system can inspect what
  happened.
- Standardizes “end of pipeline” bookkeeping by recording pipeline start/end times.

### For Humans: What This Means (Summary)

It’s the step that writes a final report card into the context: how long resolution took, which steps ran, what the
resolved instance type was, and whether it succeeded.

## Terminology (MANDATORY, EXPANSIVE)- **Diagnostics metadata**: Context metadata under

`diagnostics.*` containing structured info about a resolution.

- **Step metrics**: Per-step timing and status data keyed by step class.
- **Trace ID**: Correlation ID used to group metrics for one resolution request.
- **Pipeline start time**: The moment the overall resolution began.

### For Humans: What This Means

Diagnostics metadata is your report; step metrics are per-step timings; trace ID ties the report together; pipeline
start tells you when it all began.

## Think of It

Like a flight data recorder: it doesn’t fly the plane, but it captures what happened during the flight so you can
analyze performance and failures later.

### For Humans: What This Means (Think)

It doesn’t resolve services—it records what the resolution did.

## Story Example

A service takes too long to resolve in production. After resolution, `CollectDiagnosticsStep` stores step timings and
total duration in metadata. A diagnostics dashboard reads that metadata and shows exactly which step was slow.

### For Humans: What This Means (Story)

Instead of guessing “what was slow”, you can read the recorded step timing breakdown.

## For Dummies

1. Ask the telemetry collector for metrics (using the current trace ID).
2. Compute total duration and turn it into milliseconds.
3. Determine instance type safely (object class, scalar type, or `null`).
4. Store a diagnostics payload into `KernelContext` metadata.
5. Mark the pipeline start/end timestamps.

Common misconceptions:

- “This step fixes performance.” It only reports it.
- “It always has per-service metrics.” It depends on how telemetry is recorded.

### For Humans: What This Means (Dummies)

It’s a reporter, not a fixer. It writes whatever telemetry exists into metadata for later inspection.

## How It Works (Technical)

`__invoke` reads step metrics and pipeline timing from `StepTelemetryRecorder`, formats step timings into a normalized
array, and stores everything in `KernelContext` metadata. It also writes `pipeline.started_at` and
`pipeline.completed_at`.

### For Humans: What This Means (How)

It pulls timing data from the collector, reshapes it into a predictable structure, and saves it into the context.

## Architecture Role

Placed late in the pipeline to summarize what happened during the resolution. Depends on `StepTelemetryRecorder` (which
aggregates step events) and on `KernelContext` metadata as the transport mechanism to downstream diagnostics.

### For Humans: What This Means (Role)

It’s the “final summary” step that other tools and dashboards can read.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Collects step metrics and total duration, determines instance type, formats timings, and stores diagnostics and pipeline
timing into context metadata.

##### For Humans: What This Means (__invoke)

It writes the resolution report into the context so you can inspect it later.

##### Parameters (__invoke)

- `KernelContext $context`: The shared resolution state (contains trace ID, instance, success flag).

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- None (errors are expected to be handled by the telemetry collector implementation).

##### When to Use It (__invoke)

Invoked automatically near the end of the resolution pipeline.

##### Common Mistakes (__invoke)

- Assuming `duration_ms` is exact; it’s clamped to at least 1ms.
- Treating `instance_type` as always a class name; scalars use `gettype`.

### Method: formatStepTimings(array $stepMetrics, string $serviceId)

#### Technical Explanation (formatStepTimings)

Normalizes step metrics to a consistent shape (`duration_ms`, `status`, `started_at`, `ended_at`, `error`), selecting
metrics either keyed by service ID or using the whole input.

##### For Humans: What This Means (formatStepTimings)

It cleans up raw step data so every step timing looks the same.

##### Parameters (formatStepTimings)

- `array $stepMetrics`: Raw telemetry collector metrics.
- `string $serviceId`: Current service ID to select per-service metrics.

##### Returns (formatStepTimings)

- `array`: Normalized step timing data.

##### Throws (formatStepTimings)

- None.

##### When to Use It (formatStepTimings)

Internal helper used by `__invoke`.

##### Common Mistakes (formatStepTimings)

Passing metrics in an unexpected shape; keep telemetry collector output consistent.

## Risks, Trade-offs & Recommended Practices

- **Risk: Missing or inconsistent telemetry**. If telemetry isn’t recorded, diagnostics will be incomplete; ensure step
  events are collected.
- **Trade-off: Metadata size**. Step timing arrays can be large; consider trimming or sampling in production.
- **Practice: Correlate with trace IDs**. Use trace IDs end-to-end to stitch reports across systems.

### For Humans: What This Means (Risks)

If you don’t collect telemetry, you’ll get empty reports. Also, too much detail can be heavy—balance visibility with
cost.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Core/Kernel/StepTelemetryRecorder.md`: Collector providing metrics.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: Metadata storage used for the diagnostics payload.

### For Humans: What This Means (Related)

Read the collector to understand where metrics come from and the context docs to see where this step stores the report.

### Method: __construct(...)

#### Technical Explanation (__construct)

This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the
container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)

When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having
to manually wire the details.

##### Parameters (__construct)

- See the PHP signature in the source file for exact types and intent.

##### Returns (__construct)

- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (__construct)

- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (__construct)

- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (__construct)

- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
