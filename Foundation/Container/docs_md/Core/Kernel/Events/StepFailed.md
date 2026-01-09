# StepFailed

## Quick Summary
Event fired when a kernel pipeline step throws an exception, carrying timing, duration, step class, service ID, the exception, and optional trace ID. It exists to drive error telemetry and debugging.

### For Humans: What This Means
It’s the “step blew up” message with when it happened, how long it ran, for which service, and the exception details.

## Terminology
- **Exception**: The thrown error captured for diagnostics.
- **Duration**: Time elapsed before failure.
- **Service ID**: Service being resolved when failure occurred.
- **Trace ID**: Optional correlation ID.

### For Humans: What This Means
Includes the error object, how long the step ran before failing, which service, and the trace tag.

## Think of It
Like noting “Chef A stopped dish X after 2 minutes because the stove failed; order #trace; error details attached.”

### For Humans: What This Means
It records the failure, timing, order, and the cause.

## Story Example
On failure, telemetry uses `StepFailed` to log the exception, duration, and service/trace for alerts and debugging dashboards.

### For Humans: What This Means
This event feeds your alerts and error dashboards with context when a step fails.

## For Dummies
- Emitted when a step throws.
- Contains step class, timing, duration, service ID, exception, optional trace ID.

Common misconceptions: it doesn’t retry; it just reports the failure.

### For Humans: What This Means
It’s a report, not a recovery.

## How It Works (Technical)
Immutable value object with readonly properties for step class, startedAt, endedAt, duration, serviceId, exception, traceId set via constructor.

### For Humans: What This Means
A data packet capturing the failure details at the moment it happens.

## Architecture Role
Consumed by telemetry/logging/error handlers; emitted by pipeline runners/steps on exceptions.

### For Humans: What This Means
Monitoring uses it to record and alert on failures; the pipeline sends it when something breaks.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $stepClass, float $startedAt, float $endedAt, float $duration, string $serviceId, Throwable $exception, ?string $traceId = null)

#### Technical Explanation
Initializes the failure event with timing, identifiers, captured exception, and optional trace ID.

##### For Humans: What This Means
Creates the failure message with times, service, error object, and trace tag.

##### Parameters
- `string $stepClass`: Step class name.
- `float $startedAt`: Start timestamp.
- `float $endedAt`: End timestamp.
- `float $duration`: Duration before failure.
- `string $serviceId`: Service being resolved.
- `Throwable $exception`: The thrown error.
- `?string $traceId`: Correlation ID.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Emit when a step catches an exception to report failure.

##### Common Mistakes
Dropping the original exception; always include the real error object.

## Risks, Trade-offs & Recommended Practices
- **Risk: Sensitive data in exceptions**. Sanitize messages before logging/alerting.
- **Practice: Correlate with trace IDs**. Keep failures linkable across steps.
- **Practice: Consistent timing**. Use same clock source as start/success events.

### For Humans: What This Means
Watch for sensitive error data, keep trace IDs, and time measurements consistent.

## Related Files & Folders
- `docs_md/Core/Kernel/Events/index.md`: Event overview.
- `docs_md/Core/Kernel/Events/StepStarted.md`: Start event.
- `docs_md/Core/Kernel/Events/StepSucceeded.md`: Success event.
- `docs_md/Core/Kernel/Contracts/StepTelemetry.md`: Telemetry contract that consumes this event.

### For Humans: What This Means
See the other events for the full lifecycle and the telemetry interface that listens to them.
