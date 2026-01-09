# MetricsCollector

## Quick Summary
- This file collects simple resolution telemetry (counts and total/average duration) and exposes snapshots for dashboards or APIs.
- It exists so you can measure container resolution behavior without pulling in heavyweight observability systems.
- It removes the complexity of “how do we track basic container performance?” by providing a tiny in-memory collector.

### For Humans: What This Means
It’s the container’s fitness tracker: it counts resolutions and tracks how long they took.

## Terminology (MANDATORY, EXPANSIVE)
- **Telemetry**: Measurements about runtime behavior (counts, durations).
  - In this file: telemetry is captured via `record()` calls.
  - Why it matters: you can’t optimize what you don’t measure.
- **Snapshot**: A point-in-time report of collected data.
  - In this file: `getSnapshot()` returns aggregated numbers.
  - Why it matters: dashboards need stable, periodic summaries.
- **Sink**: A receiver of telemetry events.
  - In this file: the class implements `TelemetrySinkInterface`.
  - Why it matters: sinks let the resolution pipeline record events without knowing the collector’s internals.

### For Humans: What This Means
It gathers basic performance numbers so you can see if the container is getting slower.

## Think of It
Think of it like a stopwatch and a counter at a store entrance. Every time someone enters (resolution), you click the counter and record how long it took.

### For Humans: What This Means
It’s simple data, but it’s the kind of data you actually use.

## Story Example
You suspect your container is resolving the same service too often. You enable metrics collection and observe that one service id dominates the `counts` map. You then investigate caching/lifetime and fix the misconfiguration.

### For Humans: What This Means
It helps you spot “this is happening too often” problems.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Every time the container resolves something, it calls `record()`.
2. This class increments counters and adds to total time.
3. `getSnapshot()` returns totals and averages.

## How It Works (Technical)
`record()` increments `resolvedCount`, accumulates `totalResolutionTime`, and increments a per-abstract counter in `resolutionCounts`. `getSnapshot()` returns totals and computes `avg_resolution_ms` as `total/ count` (guarded against divide-by-zero).

### For Humans: What This Means
It’s just counters and sums—fast and reliable.

## Architecture Role
- Why this file lives in `Observe/Metrics`: it’s part of the “observe what’s happening” layer, not core resolution.
- What depends on it: diagnostics tooling and resolution pipelines that report telemetry.
- What it depends on: the sink interface contract.
- System-level reasoning: basic metrics provide early warning signs before you need heavier tooling.

### For Humans: What This Means
Start with simple measurement. Upgrade later if you need more detail.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: record(…)

#### Technical Explanation
Records a telemetry event with service identifier, duration, and strategy label.

##### For Humans: What This Means
It’s “I just resolved X and it took Y milliseconds”.

##### Parameters
- `$abstract`: Service id/abstract resolved.
- `$durationMs`: Resolution duration in milliseconds.
- `$strategy`: Resolution strategy label (e.g., singleton/scoped/transient).

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- Called by the resolution pipeline when an event occurs.

##### Common Mistakes
- Feeding seconds instead of milliseconds and skewing averages.

### Method: getSnapshot(…)

#### Technical Explanation
Returns an aggregated snapshot with totals, averages, and per-service counts.

##### For Humans: What This Means
It’s the “current stats” output you show in a dashboard.

##### Parameters
- None.

##### Returns
- An associative array of metrics.

##### Throws
- None.

##### When to Use It
- Debug endpoints, CLI tools, admin dashboards.

##### Common Mistakes
- Treating `counts` as an ordered list; it’s a map keyed by service id.

## Risks, Trade-offs & Recommended Practices
- Trade-off: In-memory only.
  - Why it matters: you lose data on restart.
  - Design stance: keep this collector lightweight; persistence belongs elsewhere.
  - Recommended practice: export snapshots periodically if you need history.

### For Humans: What This Means
It’s great for “right now”, not for long-term history—unless you export it.

## Related Files & Folders
- `docs_md/Observe/Metrics/Sink/TelemetrySinkInterface.md`: The sink contract this class implements.
- `docs_md/Observe/Metrics/EnhancedMetricsCollector.md`: A more advanced collector for richer analytics.

### For Humans: What This Means
This is the lightweight option; EnhancedMetricsCollector is the heavyweight option.

