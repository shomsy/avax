# StepStarted

## Quick Summary
Event fired when a kernel pipeline step begins execution, carrying step class, timestamp, service ID, and optional trace ID. It exists to mark the start of step processing for telemetry and tracing.

### For Humans: What This Means
It’s the “step started” message with when/what/for which service and optional trace.

## Terminology
- **Step class**: The fully qualified class name of the step starting.
- **Timestamp**: High-resolution time of start.
- **Service ID**: Identifier of the service being resolved.
- **Trace ID**: Optional correlation ID for tracing.

### For Humans: What This Means
Who is starting, when it starts, for which service, and which trace it belongs to.

## Think of It
Like logging “Chef A started cooking dish X at time T, order #trace.”

### For Humans: What This Means
It notes who started working on which order and when.

## Story Example
Telemetry wants to measure step durations. On `StepStarted`, it records the start time for the step/service/trace, later matching it with `StepSucceeded` or `StepFailed`.

### For Humans: What This Means
This event starts the timer for a step so you can measure how long it takes.

## For Dummies
- Emitted when a step begins.
- Contains step class, start time, service ID, optional trace ID.

Common misconceptions: it doesn’t indicate success; it’s just the start signal.

### For Humans: What This Means
It’s the “we’ve begun” ping, not a success or failure.

## How It Works (Technical)
Immutable `StepStarted` value object with four readonly properties set via constructor.

### For Humans: What This Means
A simple data packet created at the start with fixed fields.

## Architecture Role
Part of kernel event payloads consumed by telemetry/logging. Emitted by pipeline runners/steps when step execution starts.

### For Humans: What This Means
It’s the start notice your monitoring reads; the pipeline sends it when a step begins.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $stepClass, float $timestamp, string $serviceId, ?string $traceId = null)

#### Technical Explanation
Initializes the event with step class, start timestamp, target service, and optional trace ID.

##### For Humans: What This Means
Creates the start message with who, when, for which service, and trace tag.

##### Parameters
- `string $stepClass`: Step class name starting.
- `float $timestamp`: Start time.
- `string $serviceId`: Service being resolved.
- `?string $traceId`: Correlation ID.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Emitted right before a step runs.

##### Common Mistakes
Forgetting to include trace ID when tracing is enabled.

## Risks, Trade-offs & Recommended Practices
- **Risk: Missing trace correlation**. Without trace IDs, cross-step correlation is harder; include when available.
- **Practice: High-resolution timing**. Use precise timestamps for accurate duration metrics.

### For Humans: What This Means
Add trace IDs if you have them and capture accurate times for better metrics.

## Related Files & Folders
- `docs_md/Core/Kernel/Events/index.md`: Event overview.
- `docs_md/Core/Kernel/Events/StepSucceeded.md`: Completion event.
- `docs_md/Core/Kernel/Events/StepFailed.md`: Failure event.
- `docs_md/Core/Kernel/Contracts/StepTelemetry.md`: Telemetry interface consuming this event.

### For Humans: What This Means
See the other events for the rest of the lifecycle and the telemetry contract that listens to them.
