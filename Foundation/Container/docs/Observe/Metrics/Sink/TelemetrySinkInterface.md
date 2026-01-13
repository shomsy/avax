# TelemetrySinkInterface

## Quick Summary

- This file defines the minimal contract for recording telemetry events during container resolution.
- It exists so the container can “send telemetry somewhere” without caring what the sink does with it.
- It removes the complexity of coupling resolution code to specific metrics implementations.

### For Humans: What This Means (Summary)

It’s a mailbox: the container drops telemetry letters in it, and the sink decides what to do.

## Terminology (MANDATORY, EXPANSIVE)

- **Sink**: A receiver/collector of telemetry events.
    - In this file: the sink receives `record()` calls.
    - Why it matters: sinks decouple event production from event storage/export.
- **Abstract**: The service id being resolved.
    - In this file: `$abstract` identifies which service the event is about.
    - Why it matters: without ids, telemetry can’t be grouped.
- **Duration**: How long resolution took (in milliseconds).
    - In this file: `$durationMs` is the time measurement.
    - Why it matters: durations are the primary performance signal.
- **Strategy**: A label describing how resolution happened (singleton/scoped/transient/etc.).
    - In this file: `$strategy` is recorded as metadata.
    - Why it matters: strategy explains performance and caching behavior.

### For Humans: What This Means (Terms)

This interface makes it easy to record “what was resolved, how long it took, and how”.

## Think of It

Think of it like a receipt printer. Every time the container resolves a service, it prints a little receipt (abstract,
duration, strategy). The sink stores or forwards those receipts.

### For Humans: What This Means (Think)

You get a trail of evidence about performance, without changing core logic.

## Story Example

You have a simple in-memory sink in development and a more advanced sink in production. The resolution pipeline calls
`record()` the same way in both cases. You swap sinks based on configuration, not code changes.

### For Humans: What This Means (Story)

You can change where telemetry goes without touching resolution code.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. A sink is “where telemetry is recorded”.
2. The container calls `record()` for each event.
3. The sink stores it, aggregates it, or exports it.

## How It Works (Technical)

The interface defines a single method: `record(string $abstract, float $durationMs, string $strategy): void`.
Implementations include no-op sinks (for performance), in-memory collectors, and exporters to external systems.

### For Humans: What This Means (How)

One method keeps the contract simple and easy to implement.

## Architecture Role

- Why this file lives in `Observe/Metrics/Sink`: it’s the boundary between core telemetry events and concrete
  storage/export implementations.
- What depends on it: resolution pipelines and metrics collectors.
- What it depends on: nothing; it’s a contract.
- System-level reasoning: keeping the interface tiny makes it easy to add new sinks without rewriting the system.

### For Humans: What This Means (Role)

Small interface = easy integrations.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: record(…)

#### Technical Explanation (record)

Records a single telemetry event for a resolved service.

##### For Humans: What This Means (record)

It’s “log one resolution event”.

##### Parameters (record)

- `$abstract`: The service id that was resolved.
- `$durationMs`: How long it took (ms).
- `$strategy`: How it was resolved (strategy label).

##### Returns (record)

- Nothing.

##### Throws (record)

- Depends on sink implementation.

##### When to Use It (record)

- Called by resolution pipeline telemetry hooks.

##### Common Mistakes (record)

- Inconsistent units (ms vs seconds) across call sites.

## Risks, Trade-offs & Recommended Practices

- Risk: Doing heavy work inside `record()` slows down resolution.
    - Why it matters: telemetry should not become a bottleneck.
    - Design stance: keep `record()` fast; batch/export asynchronously when possible.
    - Recommended practice: use no-op or lightweight sinks in hot paths; offload heavy export.

### For Humans: What This Means (Risks)

Telemetry should be cheap; otherwise it becomes the problem you’re measuring.

## Related Files & Folders

- `docs_md/Observe/Metrics/MetricsCollector.md`: A concrete sink that aggregates basic metrics.
- `docs_md/Observe/Metrics/Sink/NullTelemetrySink.md`: A no-op sink used when telemetry is disabled.

### For Humans: What This Means (Related)

This is the contract; collectors and null sinks are implementations.

