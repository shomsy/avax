# StepTelemetry

## Quick Summary
Defines callbacks for step lifecycle events (started, succeeded, failed) so telemetry collectors can observe pipeline execution. It exists to standardize observability hooks for kernel steps.

### For Humans: What This Means
It’s the contract for listening to step events—start, success, failure—so you can log, trace, or measure them consistently.

## Terminology
- **StepStarted**: Event fired when a pipeline step begins.
- **StepSucceeded**: Event fired when a pipeline step completes successfully.
- **StepFailed**: Event fired when a pipeline step throws an error.
- **Telemetry**: Metrics/logs/traces collected to observe behavior.

### For Humans: What This Means
These are the three signals you can hook: started, success, and failure. Telemetry is the data you gather from them.

## Think of It
Like sensors on a production line: one triggers when work starts, one when it finishes well, and one when it fails. This interface defines the three sensor hooks.

### For Humans: What This Means
You get three spots to attach gauges and alarms around each step.

## Story Example
A team needs performance metrics. Implementing `StepTelemetry` lets them measure durations between `onStepStarted` and `onStepSucceeded`, and log errors via `onStepFailed`. The pipeline emits events, the telemetry collects them.

### For Humans: What This Means
With this contract, you can plug in metrics and logging around every step without changing the steps themselves.

## For Dummies
- Implement this interface in a telemetry collector.
- The pipeline emits events for start, success, failure.
- Your implementation records metrics/logs/traces accordingly.

Common misconceptions: it doesn’t emit events; steps or pipeline do. This just defines how listeners receive them.

### For Humans: What This Means
You write the listener; the pipeline calls it. The interface only defines the listener shape.

## How It Works (Technical)
Declares three methods: `onStepStarted(StepStarted $event)`, `onStepSucceeded(StepSucceeded $event)`, and `onStepFailed(StepFailed $event)`. Implementations handle telemetry side effects.

### For Humans: What This Means
You provide three functions that run when steps start, succeed, or fail.

## Architecture Role
Part of Contracts to decouple telemetry from step logic. Events depend on it, telemetry collectors implement it, pipeline components call it.

### For Humans: What This Means
It lets you observe steps without changing them—plug in any telemetry system that follows this contract.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: onStepStarted(StepStarted $event): void

#### Technical Explanation
Handles the start event; typically records timestamp, logs initiation, or opens spans.

##### For Humans: What This Means
Runs when a step begins so you can note the start.

##### Parameters
- `StepStarted $event`: Contains context about the starting step.

##### Returns
- `void`

##### Throws
- Implementation-specific exceptions if telemetry fails.

##### When to Use It
Implement to begin timers or traces.

##### Common Mistakes
Doing heavy work that delays step execution.

### Method: onStepSucceeded(StepSucceeded $event): void

#### Technical Explanation
Handles success events; often computes duration, increments metrics, logs completion.

##### For Humans: What This Means
Runs when a step finishes successfully so you can record success and timing.

##### Parameters
- `StepSucceeded $event`: Contains context and results.

##### Returns
- `void`

##### Throws
- Implementation-specific exceptions if telemetry fails.

##### When to Use It
Implement to finalize spans or log completion.

##### Common Mistakes
Ignoring context fields needed for correct metrics.

### Method: onStepFailed(StepFailed $event): void

#### Technical Explanation
Handles failure events; records error details, triggers alerts, and correlates with context/step metadata.

##### For Humans: What This Means
Runs when a step throws so you can log/alert on the failure.

##### Parameters
- `StepFailed $event`: Contains error details and context.

##### Returns
- `void`

##### Throws
- Implementation-specific exceptions if telemetry handling fails.

##### When to Use It
Implement to capture errors and trigger alerts.

##### Common Mistakes
Swallowing errors without recording enough diagnostic data.

## Risks, Trade-offs & Recommended Practices
- **Risk: Telemetry overhead**. Heavy telemetry can slow pipelines; keep handlers lightweight.
- **Risk: Handler failures**. Telemetry errors shouldn’t break resolution; guard against exceptions.
- **Practice: Non-blocking I/O**. Use async/queueing where possible for logs/metrics.
- **Practice: Correlate with trace IDs**. Include trace/context IDs from events for observability.

### For Humans: What This Means
Keep telemetry fast and safe; don’t let monitoring break your resolutions; include IDs for tracing.

## Related Files & Folders
- `docs_md/Core/Kernel/Contracts/index.md`: Contract overview.
- `docs_md/Core/Kernel/Events/StepStarted.md`: Event payload for start.
- `docs_md/Core/Kernel/Events/StepSucceeded.md`: Event payload for success.
- `docs_md/Core/Kernel/Events/StepFailed.md`: Event payload for failures.

### For Humans: What This Means
Read the event docs to know what data you get in each callback.
