# StepSucceeded

## Quick Summary
Event fired when a kernel pipeline step completes successfully, carrying timing, duration, step class, service ID, and optional trace ID. It exists to finalize telemetry for successful steps.

### For Humans: What This Means
It’s the “step finished fine” message with how long it took and which service/trace it belongs to.

## Terminology
- **StartedAt/EndedAt**: Timestamps marking start/end of the step.
- **Duration**: Computed elapsed time for the step.
- **Service ID**: Identifier for the service being resolved.
- **Trace ID**: Optional correlation ID.

### For Humans: What This Means
Start, end, and total time for the step, plus which service/trace it was for.

## Think of It
Like noting “Chef A finished dish X at time T2; total time T2-T1, order #trace.”

### For Humans: What This Means
It records the completion and timing of the work for that order.

## Story Example
Telemetry records start on `StepStarted`. On `StepSucceeded`, it calculates duration and logs success metrics, tied to service and trace.

### For Humans: What This Means
This event stops the timer and marks success so you can measure how long it took.

## For Dummies
- Emitted when a step ends successfully.
- Contains start/end timestamps, duration, step class, service ID, optional trace ID.

Common misconceptions: it doesn’t include error info; that’s `StepFailed`.

### For Humans: What This Means
This is the success ping, not the failure one.

## How It Works (Technical)
Immutable value object with readonly properties set in constructor: step class, startedAt, endedAt, duration, serviceId, traceId.

### For Humans: What This Means
A fixed data packet created at success with timing and identifiers.

## Architecture Role
Consumed by telemetry/logging to record successful step metrics; emitted by pipeline runners/steps after successful execution.

### For Humans: What This Means
Monitoring uses it to log success and duration; the pipeline sends it when a step finishes okay.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $stepClass, float $startedAt, float $endedAt, float $duration, string $serviceId, ?string $traceId = null)

#### Technical Explanation
Initializes the event with timing data, identifiers, and optional trace ID for correlation.

##### For Humans: What This Means
Creates the success message with times, service, and trace tag.

##### Parameters
- `string $stepClass`: Step class name.
- `float $startedAt`: Start timestamp.
- `float $endedAt`: End timestamp.
- `float $duration`: Duration in seconds.
- `string $serviceId`: Target service.
- `?string $traceId`: Correlation ID.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Emit right after a step finishes without exceptions.

##### Common Mistakes
Incorrect duration calculations; ensure consistent clocks with `StepStarted`.

## Risks, Trade-offs & Recommended Practices
- **Risk: Clock skew**. Inconsistent time sources distort duration; use the same timer for start/end.
- **Practice: Include trace IDs**. Preserve correlation across steps.

### For Humans: What This Means
Use one clock for timing and keep trace IDs to link events.

## Related Files & Folders
- `docs_md/Core/Kernel/Events/index.md`: Event overview.
- `docs_md/Core/Kernel/Events/StepStarted.md`: Start event.
- `docs_md/Core/Kernel/Events/StepFailed.md`: Failure event.
- `docs_md/Core/Kernel/Contracts/StepTelemetry.md`: Telemetry contract consuming this event.

### For Humans: What This Means
Check the start/failure events and telemetry contract to see the whole lifecycle picture.
