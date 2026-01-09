# EnhancedMetricsCollector

## Quick Summary
- This file provides a richer metrics collector that records resolutions, errors, performs sampling, can export telemetry, and offers analytics (top slow services, error rate, anomalies).
- It exists so you can observe container behavior with enough detail to troubleshoot performance regressions and reliability issues.
- It removes the complexity of building your own analytics from scratch by offering a single “observability brain” for the container.

### For Humans: What This Means
It’s the container’s flight recorder: it keeps enough history to explain what went wrong and where time went.

## Terminology (MANDATORY, EXPANSIVE)
- **Sampling**: Recording only some events to reduce overhead.
  - In this file: sampling is controlled by `TelemetryConfig`.
  - Why it matters: collecting everything can be too expensive.
- **Resolution stats**: Structured entries containing service id, duration, strategy, timestamp, memory peak, error status.
  - In this file: stored in `$resolutionStats`.
  - Why it matters: you need context, not just averages.
- **Error stats**: Structured entries describing failures and their context.
  - In this file: stored in `$errorStats`.
  - Why it matters: you want error patterns, not just “something failed”.
- **Telemetry export**: Converting internal metrics into an exportable payload.
  - In this file: `exportTelemetry()` returns a data structure.
  - Why it matters: you might want to ship telemetry to another system.
- **Anomaly detection**: Detecting weird performance behavior.
  - In this file: `detectPerformanceAnomalies()` applies heuristics/statistics.
  - Why it matters: regressions often show up as anomalies first.

### For Humans: What This Means
This gives you enough detail to answer: “what got slow?”, “what started failing?”, “is it getting worse?”

## Think of It
Think of it like a black box on an airplane. You hope you never need it, but when something goes wrong, it’s the fastest way to understand the story.

### For Humans: What This Means
When incidents happen, this tool can turn panic into a clear timeline of facts.

## Story Example
You deploy a change and suddenly resolution time spikes for a handful of services. `getTopSlowServices()` points you to those ids. `recordError()` shows an error pattern for one service. You export telemetry and attach it to an incident report. You now fix the regression with evidence.

### For Humans: What This Means
Instead of guessing, you can pinpoint the slowdown and the failures.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. The container calls `recordResolution()` when it resolves a service.
2. It calls `recordError()` when something fails.
3. This collector stores recent history (with limits).
4. You query stats (counts, averages, slowest, error rate).
5. You export telemetry when you need to send it elsewhere.

## How It Works (Technical)
The collector uses in-memory arrays with retention limits and optional sampling to control overhead. It logs through a PSR-3-style `ErrorLogger` created by a `LoggerFactory`. It records resolution events and error events, provides aggregate queries (counts/averages/rates), and can export telemetry data through multiple output formats depending on configuration. Analytics methods scan stored stats arrays to derive slow-service lists, recent errors, usage stats, and anomaly reports.

### For Humans: What This Means
It stores recent “what happened” data and gives you many ways to summarize it.

## Architecture Role
- Why this file lives in `Observe/Metrics`: it is observability infrastructure, not container core.
- What depends on it: logging integrations, diagnostics tooling, dashboards, and potentially CI/perf checks.
- What it depends on: telemetry configuration and logging infrastructure.
- System-level reasoning: containers are central; when they get slow or flaky, everything is slow or flaky—so you need strong observability.

### For Humans: What This Means
If the container is the heart, this is the heart monitor.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Creates a dedicated logger and stores telemetry configuration used for sampling/export behavior.

##### For Humans: What This Means
It wires the collector into logging and config so it behaves the way you want.

##### Parameters
- `$loggerFactory`: Creates a logger for metrics events.
- `$config`: Telemetry configuration (enabled, sampling rate, sink settings).

##### Returns
- Nothing.

##### Throws
- Depends on logger factory implementation.

##### When to Use It
- During container boot when you enable richer telemetry.

##### Common Mistakes
- Enabling rich telemetry in hot-path production without sampling.

### Method: recordResolution(…)

#### Technical Explanation
Stores a resolution event entry (service, duration, strategy, timestamp, memory, error flag) and logs it.

##### For Humans: What This Means
It records “we resolved X and it took Y”.

##### Parameters
- `$data`: Resolution event data array (service id, duration, strategy, and optional metadata).

##### Returns
- Nothing.

##### Throws
- Depends on implementation and logging.

##### When to Use It
- Called from resolution pipeline instrumentation.

##### Common Mistakes
- Feeding inconsistent keys/units in `$data`.

### Method: recordError(…)

#### Technical Explanation
Stores error stats with service id and throwable info, optionally including stack traces.

##### For Humans: What This Means
It records “service X failed with error Y”.

##### Parameters
- `$serviceId`: The failing service id.
- `$error`: The throwable.

##### Returns
- Nothing.

##### Throws
- None (should be resilient).

##### When to Use It
- When resolution or boot steps throw.

##### Common Mistakes
- Logging secrets in error messages; rely on sanitization policies.

### Method: reset(…)

#### Technical Explanation
Clears stored metrics state.

##### For Humans: What This Means
It wipes the slate clean.

##### Parameters
- None.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- Tests, dev sessions, manual diagnostics resets.

##### Common Mistakes
- Resetting in production and losing useful history mid-incident.

### Method: flush(…)

#### Technical Explanation
Forces writing/exporting buffered telemetry according to configuration.

##### For Humans: What This Means
It says “push out what we’ve collected”.

##### Parameters
- None.

##### Returns
- Nothing.

##### Throws
- Depends on sink implementation.

##### When to Use It
- Shutdown hooks or periodic telemetry export tasks.

##### Common Mistakes
- Calling flush too often and creating I/O overhead.

### Method: exportTelemetry(…)

#### Technical Explanation
Builds an export payload containing resolution stats, errors, and aggregated numbers.

##### For Humans: What This Means
It makes your telemetry portable.

##### Parameters
- None.

##### Returns
- An array payload ready to ship to another system.

##### Throws
- Depends on implementation.

##### When to Use It
- Admin endpoints, incident reports, external monitoring export.

##### Common Mistakes
- Treating exported payload as stable schema without versioning.

### Method: getResolutionCount(…)

#### Technical Explanation
Returns the total number of recorded resolution events.

##### For Humans: What This Means
It answers: “how many resolves did we see?”

##### Parameters
- None.

##### Returns
- Integer count.

##### Throws
- None.

##### When to Use It
- Basic dashboard stats.

##### Common Mistakes
- Confusing sampled count with actual total count if sampling is enabled.

### Method: getAverageResolutionTime(…)

#### Technical Explanation
Computes average resolution time across recorded events.

##### For Humans: What This Means
It answers: “on average, how slow is resolution?”

##### Parameters
- None.

##### Returns
- Average duration as float.

##### Throws
- None.

##### When to Use It
- Performance monitoring.

##### Common Mistakes
- Using average alone; always also inspect percentiles/slowest when available.

### Method: getErrorRate(…)

#### Technical Explanation
Computes error rate based on errors and resolution events.

##### For Humans: What This Means
It answers: “how often do we fail?”

##### Parameters
- None.

##### Returns
- Float error rate.

##### Throws
- None.

##### When to Use It
- Reliability monitoring.

##### Common Mistakes
- Treating a small sample as statistically stable.

### Method: getTopSlowServices(…)

#### Technical Explanation
Returns the slowest services (by duration) up to a limit.

##### For Humans: What This Means
It answers: “what’s the slowest stuff we’re resolving?”

##### Parameters
- `$limit`: Maximum number to return.

##### Returns
- An `Arrhae` collection of slow entries.

##### Throws
- None.

##### When to Use It
- Bottleneck diagnosis.

##### Common Mistakes
- Treating slowest list as permanent truth; it changes with workload.

### Method: detectPerformanceAnomalies(…)

#### Technical Explanation
Scans stats to detect abnormal performance patterns and returns anomaly reports.

##### For Humans: What This Means
It tries to spot “something weird started happening”.

##### Parameters
- None.

##### Returns
- An array of anomaly findings.

##### Throws
- None.

##### When to Use It
- Regression detection.

##### Common Mistakes
- Using anomaly detection as a substitute for proper profiling.

### Method: getRecentErrors(…)

#### Technical Explanation
Returns a limited list of recent errors.

##### For Humans: What This Means
It answers: “what failed recently?”

##### Parameters
- `$limit`: Maximum number to return.

##### Returns
- An `Arrhae` of recent error entries.

##### Throws
- None.

##### When to Use It
- Debug panels and incident triage.

##### Common Mistakes
- Assuming it includes all errors if retention is limited.

### Method: getServiceUsageStats(…)

#### Technical Explanation
Aggregates usage counts and patterns per service.

##### For Humans: What This Means
It tells you which services are “hot”.

##### Parameters
- None.

##### Returns
- An array usage stats.

##### Throws
- None.

##### When to Use It
- Optimizing lifetimes and caching strategies.

##### Common Mistakes
- Optimizing based on dev usage rather than production reality.

## Risks, Trade-offs & Recommended Practices
- Risk: Metrics collection overhead.
  - Why it matters: storing arrays and logging events adds work.
  - Design stance: make telemetry configurable and sample by default.
  - Recommended practice: enable richer telemetry in dev/staging; sample and cap retention in production.

### For Humans: What This Means
Don’t let observability become the thing that makes you slow.

## Related Files & Folders
- `docs_md/Observe/Metrics/LoggerFactoryIntegration.md`: Creates and logs metrics-related events.
- `docs_md/Features/Operate/Config/TelemetryConfig.md`: Controls telemetry behavior.
- `docs_md/Observe/Metrics/TelemetryExporter.md`: Interface for exporting data out of process.

### For Humans: What This Means
Config decides what to collect; collector stores it; integration logs it; exporters ship it out.

