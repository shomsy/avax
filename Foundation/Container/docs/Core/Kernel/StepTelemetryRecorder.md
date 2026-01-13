# StepTelemetryRecorder

## Quick Summary

StepTelemetryRecorder implements the observer pattern to capture detailed performance and diagnostic data from
resolution pipeline step execution. It collects timing information, success/failure status, and error details for each
step, enabling comprehensive monitoring and debugging of dependency resolution processes. This telemetry data is crucial
for optimizing container performance and diagnosing resolution issues in complex applications.

### For Humans: What This Means (Summary)

Imagine you're running a complex manufacturing operation and you want to know exactly how long each workstation takes,
which ones fail, and why. StepTelemetryRecorder is your production monitoring system that watches every step of the
assembly line, recording when work starts, how long it takes, whether it succeeds or fails, and what went wrong if it
did. It's the quality control dashboard that gives you complete visibility into your container's performance.

## Terminology (MANDATORY, EXPANSIVE)**Observer Pattern

**: A behavioral design pattern where objects (observers) listen for events from subjects and react accordingly. In this
file, the collector observes step execution events. It matters because it enables decoupled event handling and
extensibility.

**Telemetry Data**: Performance and diagnostic information collected during system operation. In this file, includes
timing, status, and error information. It matters because it enables monitoring and optimization of complex processes.

**Trace ID**: A unique identifier that groups related operations for tracking and correlation. In this file, used to
organize metrics by execution context. It matters because it enables following requests through distributed systems.

**Step Metrics**: Performance measurements for individual pipeline steps. In this file, includes start time, duration,
status, and error details. It matters because it identifies performance bottlenecks and failure points.

**Event-Driven Collection**: Gathering data through event notifications rather than direct polling. In this file,
onStepStarted/Succeeded/Failed methods handle events. It matters because it provides real-time, low-overhead monitoring.

**Pipeline Duration**: The total time taken to execute the entire resolution pipeline. In this file, calculated from
collected step metrics. It matters because it provides end-to-end performance visibility.

### For Humans: What This Means

These are the monitoring vocabulary. Observer pattern is subscribing to notifications. Telemetry is the performance data
stream. Trace ID is the transaction tracking number. Step metrics are the individual performance reports. Event-driven
collection is getting updates as they happen. Pipeline duration is the total time from start to finish.

## Think of It

Picture a sophisticated race car telemetry system that records every aspect of the vehicle's performance—speed, engine
RPM, tire pressure, brake temperature, lap times. StepTelemetryRecorder is that telemetry system for your dependency
injection pipeline, capturing data about each resolution step with precise timing and detailed diagnostics. When
something goes wrong or performs poorly, you have complete data to analyze and fix the issue.

### For Humans: What This Means (Think)

This analogy shows why StepTelemetryRecorder exists: comprehensive performance intelligence. Without it, you'd be
driving blind—knowing something is slow or broken but having no data to diagnose the problem. The recorder provides the
detailed telemetry needed to optimize and troubleshoot complex dependency resolution.

## Story Example

Before StepTelemetryRecorder existed, debugging resolution performance required manual timing and logging scattered
throughout the codebase. Identifying slow steps or failure patterns was nearly impossible. With the recorder, detailed
telemetry became automatically available. A performance issue that previously required days of debugging could now be
identified instantly by examining the collected metrics, showing exactly which step was slow and by how much.

### For Humans: What This Means (Story)

This story illustrates the observability problem StepTelemetryRecorder solves: lack of performance data. Without it,
container performance was a black box— you knew there were problems but couldn't see inside. The recorder provides the
x-ray vision needed to understand and optimize container behavior.

## For Dummies

Let's break this down like tracking package delivery:

1. **The Problem**: You need to know exactly where packages are, how long they take, and what goes wrong.

2. **StepTelemetryRecorder's Job**: The delivery tracking system that records every step of the journey.

3. **How You Use It**: It automatically collects data as steps execute, then you query it for insights.

4. **What Happens Inside**: Records start times, durations, successes, failures, and error details.

5. **Why It's Helpful**: Gives you complete visibility into container performance and issues.

Common misconceptions:

- "It's just logging" - It provides structured, queryable performance data.
- "It slows things down" - Event-driven collection has minimal overhead.
- "Only for debugging" - It's essential for production performance monitoring.

### For Humans: What This Means (Dummies)

StepTelemetryRecorder isn't just logging—it's intelligent monitoring. It takes the complexity of tracking detailed
performance and makes it systematic and accessible. You get professional-grade telemetry without becoming a monitoring
expert.

## How It Works (Technical)

StepTelemetryRecorder implements StepTelemetry interface and maintains a hierarchical array structure organized by trace
ID, service ID, and step class. Event handlers update metrics with timing and status information. Query methods provide
access to collected data with aggregation capabilities for total duration and start times.

### For Humans: What This Means (How)

Under the hood, it's a structured data collector. It organizes performance information like a well-indexed filing
system—by trace, then service, then step. When events happen, it updates the records. When you need information, it
provides exactly what you asked for or summarizes everything. It's like having a perfect memory for everything that
happens in the pipeline.

## Architecture Role

StepTelemetryRecorder sits at the observability boundary of the kernel, providing monitoring capabilities while
maintaining separation from core resolution logic. It enables performance analysis and debugging without coupling
monitoring concerns to resolution implementation.

### For Humans: What This Means (Role)

In the kernel's architecture, StepTelemetryRecorder is the monitoring system—the dashboard that watches everything
without interfering with the actual work. It provides the visibility needed to maintain and optimize the system without
becoming part of the core functionality.

## Risks, Trade-offs & Recommended Practices

**Risk**: Collecting detailed telemetry can impact performance in high-throughput scenarios.

**Why it matters**: Every event handler adds execution time, especially with many steps.

**Design stance**: Make telemetry optional and minimize collection overhead.

**Recommended practice**: Enable detailed telemetry only in development and monitoring environments.

**Risk**: Large amounts of telemetry data can consume significant memory.

**Why it matters**: Long-running applications with many resolutions accumulate substantial data.

**Design stance**: Implement data retention policies and periodic cleanup.

**Recommended practice**: Configure telemetry retention limits and implement data archiving for analysis.

**Risk**: Telemetry data structure can become complex and hard to query.

**Why it matters**: Deeply nested arrays are difficult to analyze programmatically.

**Design stance**: Provide query APIs and consider structured data formats.

**Recommended practice**: Implement query methods and consider exporting to analysis-friendly formats.

### For Humans: What This Means (Risks)

Like any monitoring system, StepTelemetryRecorder has resource implications. It's powerful for its purpose but requires
thoughtful deployment. The key is using it strategically—maximum visibility where needed, minimal overhead where not.

## Related Files & Folders

**ResolutionPipeline**: Generates the telemetry events that the collector observes. You configure telemetry in pipeline
construction. It provides the execution context being monitored.

**StepTelemetry**: Defines the interface that the collector implements. You can implement custom telemetry collectors.
It establishes the event handling contract.

**Events/**: Contains the event classes (StepStarted, StepSucceeded, StepFailed) that the collector handles. You examine
these for available telemetry data. It defines the event data structures.

**ContainerKernel**: Uses telemetry for monitoring and diagnostics. You access collected data through kernel methods. It
provides the runtime integration point.

### For Humans: What This Means (Related)

StepTelemetryRecorder works with a complete monitoring ecosystem. The pipeline generates events, the interface defines
contracts, events carry data, and the kernel provides access. Understanding this ecosystem helps you implement
comprehensive container observability.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: onStepStarted(StepStarted $event): void

#### Technical Explanation (onStepStarted)

Handles the step started event by recording initial telemetry data including timestamp, service ID, and execution
context for the beginning of a pipeline step's execution.

##### For Humans: What This Means (onStepStarted)

This method is called when a pipeline step begins executing. It records the starting time and context information so you
can later analyze how long the step took and what it was doing.

##### Parameters (onStepStarted)

- `StepStarted $event`: Event object containing step execution start details

##### Returns (onStepStarted)

- `void`: Event handling doesn't return data

##### Throws (onStepStarted)

- None. Event handling is safe and doesn't throw exceptions.

##### When to Use It (onStepStarted)

- Automatically called by the pipeline during step execution
- Not typically called directly by user code

##### Common Mistakes (onStepStarted)

- This is an internal event handler, not meant for direct calling

### Method: onStepSucceeded(StepSucceeded $event): void

#### Technical Explanation (onStepSucceeded)

Processes successful step completion events, updating stored metrics with end time, duration, and success status for
completed pipeline steps.

##### For Humans: What This Means (onStepSucceeded)

When a pipeline step finishes successfully, this method records the completion time, calculates how long it took, and
marks it as successful in the telemetry data.

##### Parameters (onStepSucceeded)

- `StepSucceeded $event`: Event object containing step completion details

##### Returns (onStepSucceeded)

- `void`: Event handling doesn't return data

##### Throws (onStepSucceeded)

- None. Event handling is safe and doesn't throw exceptions.

##### When to Use It (onStepSucceeded)

- Automatically called by the pipeline when steps complete successfully
- Not typically called directly by user code

##### Common Mistakes (onStepSucceeded)

- This is an internal event handler, not meant for direct calling

### Method: onStepFailed(StepFailed $event): void

#### Technical Explanation (onStepFailed)

Handles step failure events by recording completion timing, duration, failure status, and error information including
exception messages and types for failed pipeline steps.

##### For Humans: What This Means (onStepFailed)

When a pipeline step fails, this method records all the details about what went wrong—the timing, the error message, and
the type of exception—so you can debug the issue later.

##### Parameters (onStepFailed)

- `StepFailed $event`: Event object containing step failure details and exception

##### Returns (onStepFailed)

- `void`: Event handling doesn't return data

##### Throws (onStepFailed)

- None. Event handling is safe and doesn't throw exceptions.

##### When to Use It (onStepFailed)

- Automatically called by the pipeline when steps fail
- Not typically called directly by user code

##### Common Mistakes (onStepFailed)

- This is an internal event handler, not meant for direct calling

### Method: getStepMetrics(?string $traceId = null): array

#### Technical Explanation (getStepMetrics)

Retrieves collected telemetry data, optionally filtered by trace ID, providing access to detailed step execution metrics
organized by trace, service, and step class.

##### For Humans: What This Means (getStepMetrics)

This method gives you access to all the performance data that has been collected. You can get everything or filter it to
a specific trace (like a specific request) to see how it performed.

##### Parameters (getStepMetrics)

- `?string $traceId`: Optional trace identifier to filter results to a specific execution context

##### Returns (getStepMetrics)

- `array`: Hierarchical array of step metrics organized by trace, service, and step

##### Throws (getStepMetrics)

- None. Data retrieval is always safe.

##### When to Use It (getStepMetrics)

- For performance analysis and debugging
- When building monitoring dashboards
- For generating performance reports

##### Common Mistakes (getStepMetrics)

- Assuming the array structure without checking documentation
- Not handling empty results when no telemetry has been collected
- Using this in performance-critical code paths

### Method: asArray(): array

#### Technical Explanation (asArray)

Exports all collected telemetry data in a structured, serializable format including both detailed traces and computed
summary statistics for easy storage and transmission.

##### For Humans: What This Means (asArray)

This method packages all the telemetry data into a clean, structured format that's easy to save, send over the network,
or analyze. It includes both the detailed step-by-step data and summary information.

##### Parameters (asArray)

- None.

##### Returns (asArray)

- `array`: Structured telemetry data with 'traces' and 'summary' sections

##### Throws (asArray)

- None. Data export is always safe.

##### When to Use It (asArray)

- When sending telemetry data to monitoring systems
- For storing performance data in databases or files
- When creating API responses with telemetry information

##### Common Mistakes (asArray)

- Assuming the structure is fixed (it may evolve)
- Not handling large data sets that could impact memory
- Using this for real-time queries (prefer getStepMetrics)

### Method: getTotalDuration(): float

#### Technical Explanation (getTotalDuration)

Calculates the total time span from the earliest recorded step start to the latest step end across all collected
telemetry data, providing end-to-end pipeline duration.

##### For Humans: What This Means (getTotalDuration)

This tells you how long the entire resolution process took, from the very first step that started to the very last step
that finished. It's the total elapsed time for the whole operation.

##### Parameters (getTotalDuration)

- None.

##### Returns (getTotalDuration)

- `float`: Total duration in seconds, or 0.0 if no data is available

##### Throws (getTotalDuration)

- None. Calculation is always safe.

##### When to Use It (getTotalDuration)

- For measuring overall pipeline performance
- In performance monitoring and alerting
- For generating summary reports

##### Common Mistakes (getTotalDuration)

- Assuming this represents CPU time (it's wall-clock time)
- Not considering that multiple traces may be included
- Using this for real-time performance checks (it's computed from stored data)

### Method: getPipelineStartTime(): ?float

#### Technical Explanation (getPipelineStartTime)

Determines the earliest recorded step start time across all collected telemetry data, useful for establishing baseline
timestamps and calculating relative timings.

##### For Humans: What This Means (getPipelineStartTime)

This finds when the very first step in any pipeline started executing. It's useful for understanding when the whole
process began and for calculating relative timings of subsequent steps.

##### Parameters (getPipelineStartTime)

- None.

##### Returns (getPipelineStartTime)

- `?float`: Earliest step start time as a Unix timestamp, or null if no data

##### Throws (getPipelineStartTime)

- None. Calculation is always safe.

##### When to Use It (getPipelineStartTime)

- For establishing timeline baselines in performance analysis
- When correlating telemetry with external timing data
- For generating timeline visualizations

##### Common Mistakes (getPipelineStartTime)

- Assuming this represents pipeline creation time (it's first step execution)
- Not handling null return values
- Using this when you need current time (it's historical data)
