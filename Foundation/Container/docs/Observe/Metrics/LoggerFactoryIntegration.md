# LoggerFactoryIntegration

## Quick Summary
- This file is the logging “hub” for container observability: it creates per-component loggers and provides structured logging helpers for resolution, registration, cache, config, performance, errors, health, and telemetry export.
- It exists so container logs are consistent, security-aware (sanitization), and easy to search.
- It removes the complexity of logging conventions by centralizing log formatting and context shaping.

### For Humans: What This Means (Summary)
It’s the container’s spokesperson: it turns raw events into clean, consistent log messages.

## Terminology (MANDATORY, EXPANSIVE)
- **Logger factory**: A component that creates loggers for channels/components.
  - In this file: `LoggerFactory` is used to create `ErrorLogger` instances.
  - Why it matters: channel separation makes logs searchable and actionable.
- **Channel**: A category of logs (resolution, cache, errors, etc.).
  - In this file: channels are named like `container-resolution`, `container-errors`, etc.
  - Why it matters: it stops logs from becoming one unreadable stream.
- **Sanitization**: Removing secrets and unsafe data before logging.
  - In this file: `sanitizeConfig()` redacts sensitive keys.
  - Why it matters: logs often end up in places you don’t fully control.
- **Structured context**: Key/value context attached to log events.
  - In this file: methods accept `$context` and merge in standard fields.
  - Why it matters: structure is what makes logs queryable.
- **Telemetry config**: Settings controlling what to log and how much detail to include.
  - In this file: `TelemetryConfig` gates behavior and stack trace inclusion.
  - Why it matters: observability must be configurable for performance and security.

### For Humans: What This Means (Terms)
This file is the “make logs useful and safe” layer.

## Think of It
Think of it like a newsroom editor. Events come in messy and inconsistent; the editor formats them so they’re readable, consistent, and safe to publish.

### For Humans: What This Means (Think)
Without this file, your logs will feel like a chaotic group chat.

## Story Example
You’re debugging a production issue. You filter logs by `container-resolution` and immediately see slow resolutions with service ids, durations, and strategies. You also check `container-errors` and see correlated exceptions with safe, redacted configuration context. You now have a complete story without leaking secrets.

### For Humans: What This Means (Story)
Good logging turns “I don’t know what happened” into “here’s exactly what happened”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. This class gives you a logger for a component.
2. It also provides methods like “log resolution” and “log error”.
3. It makes sure logs include useful context and redact secrets.

## How It Works (Technical)
The integration keeps a memoized map of component loggers to avoid repeated factory work. It produces channel names consistently and merges standard context fields (timestamps, event names, durations). It sanitizes configuration payloads before logging. Higher-level helpers (`logServiceResolution`, `logContainerError`, etc.) pick appropriate channels and severity, shaping context for predictable log output.

### For Humans: What This Means (How)
It standardizes how the container talks in logs—so you don’t have to decode custom formats every time.

## Architecture Role
- Why this file lives in `Observe/Metrics`: logging is a key part of observability and is closely tied to telemetry configuration.
- What depends on it: secure resolvers, metrics collectors, bootstraps, and any component that logs container events.
- What it depends on: the logger factory and telemetry configuration.
- System-level reasoning: consistent logs are an operational contract; centralizing them prevents drift and leaks.

### For Humans: What This Means (Role)
Centralizing logging is how you keep it consistent when the system grows.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)
Stores the logger factory and telemetry configuration used to control logging behavior.

##### For Humans: What This Means (__construct)
It gets the “how to create loggers” tool and the “what should we log” settings.

##### Parameters (__construct)
- `$loggerFactory`: Creates per-channel loggers.
- `$config`: Telemetry config controlling what to log and how much detail.

##### Returns (__construct)
- Nothing.

##### Throws (__construct)
- Depends on underlying logger factory implementation.

##### When to Use It (__construct)
- During container boot.

##### Common Mistakes (__construct)
- Disabling telemetry and expecting structured logs to still be emitted.

### Method: createMetricsCollector(…)

#### Technical Explanation (createMetricsCollector)
Creates an `EnhancedMetricsCollector` wired with this integration’s logger factory and config.

##### For Humans: What This Means (createMetricsCollector)
It gives you a metrics collector that “matches” your logging setup.

##### Parameters (createMetricsCollector)
- None.

##### Returns (createMetricsCollector)
- `EnhancedMetricsCollector`.

##### Throws (createMetricsCollector)
- Depends on collector construction.

##### When to Use It (createMetricsCollector)
- When enabling rich metrics collection.

##### Common Mistakes (createMetricsCollector)
- Creating collectors manually with mismatched config.

### Method: logLifecycleEvent(…)

#### Technical Explanation (logLifecycleEvent)
Logs major container lifecycle events with context.

##### For Humans: What This Means (logLifecycleEvent)
It records “container started/shutdown/config loaded” moments.

##### Parameters (logLifecycleEvent)
- `$event`: Event name string.
- `$context`: Extra context.

##### Returns (logLifecycleEvent)
- Nothing.

##### Throws (logLifecycleEvent)
- Logging backend exceptions (implementation-dependent).

##### When to Use It (logLifecycleEvent)
- Boot/shutdown hooks.

##### Common Mistakes (logLifecycleEvent)
- Logging secrets in `$context` without sanitization.

### Method: getComponentLogger(…)

#### Technical Explanation (getComponentLogger)
Returns a memoized logger for a given component channel.

##### For Humans: What This Means (getComponentLogger)
It gives each container component its own log stream.

##### Parameters (getComponentLogger)
- `$component`: Component name (used to form channel).

##### Returns (getComponentLogger)
- `ErrorLogger`.

##### Throws (getComponentLogger)
- Depends on logger factory.

##### When to Use It (getComponentLogger)
- When a component needs to log consistently.

##### Common Mistakes (getComponentLogger)
- Using too many unique component names and creating noisy channel sprawl.

### Method: logServiceResolution(…)

#### Technical Explanation (logServiceResolution)
Logs a service resolution event with duration, strategy, and context.

##### For Humans: What This Means (logServiceResolution)
It records “resolved X in Y ms using strategy Z”.

##### Parameters (logServiceResolution)
- Depends on signature; conceptually service id, duration, strategy, context.

##### Returns (logServiceResolution)
- Nothing.

##### Throws (logServiceResolution)
- Depends on logger backend.

##### When to Use It (logServiceResolution)
- Resolution pipeline instrumentation.

##### Common Mistakes (logServiceResolution)
- Mixing units for duration.

### Method: logServiceRegistration(…)

#### Technical Explanation (logServiceRegistration)
Logs service registration/binding events.

##### For Humans: What This Means (logServiceRegistration)
It records “we registered this service with these attributes”.

##### Parameters (logServiceRegistration)
- Depends on signature; typically service id, class, lifetime, tags, etc.

##### Returns (logServiceRegistration)
- Nothing.

##### Throws (logServiceRegistration)
- Depends on logger backend.

##### When to Use It (logServiceRegistration)
- During boot registration.

##### Common Mistakes (logServiceRegistration)
- Logging raw config without sanitization.

### Method: logCacheOperation(…)

#### Technical Explanation (logCacheOperation)
Logs cache hits/misses/sets with timing and context.

##### For Humans: What This Means (logCacheOperation)
It records “cache did something” so you can debug performance.

##### Parameters (logCacheOperation)
- Depends on signature; conceptually operation name + context.

##### Returns (logCacheOperation)
- Nothing.

##### Throws (logCacheOperation)
- Depends on logger backend.

##### When to Use It (logCacheOperation)
- Scope cache operations and prototype caches.

##### Common Mistakes (logCacheOperation)
- Logging too frequently and creating log spam.

### Method: logConfigurationEvent(…)

#### Technical Explanation (logConfigurationEvent)
Logs configuration-related events with sanitized payload.

##### For Humans: What This Means (logConfigurationEvent)
It records “config loaded/changed/validated” without leaking secrets.

##### Parameters (logConfigurationEvent)
- `$event`: Config event name.
- `$config`: Config payload.

##### Returns (logConfigurationEvent)
- Nothing.

##### Throws (logConfigurationEvent)
- Depends on logger backend.

##### When to Use It (logConfigurationEvent)
- When configuration is loaded or validated.

##### Common Mistakes (logConfigurationEvent)
- Passing deeply nested configs without understanding log volume.

### Method: logPerformanceWarning(…)

#### Technical Explanation (logPerformanceWarning)
Logs a warning when a service resolution exceeds thresholds or shows performance risk.

##### For Humans: What This Means (logPerformanceWarning)
It tells you “this service is slow” proactively.

##### Parameters (logPerformanceWarning)
- Depends on signature; typically service id, duration, threshold, context.

##### Returns (logPerformanceWarning)
- Nothing.

##### Throws (logPerformanceWarning)
- Depends on logger backend.

##### When to Use It (logPerformanceWarning)
- When monitoring slow resolution hotspots.

##### Common Mistakes (logPerformanceWarning)
- Setting thresholds too low and creating noisy warnings.

### Method: logContainerError(…)

#### Technical Explanation (logContainerError)
Logs container errors with safe context, optionally including stack traces based on config.

##### For Humans: What This Means (logContainerError)
It records failures in a way that’s useful and safe.

##### Parameters (logContainerError)
- Depends on signature; typically component name, throwable, context.

##### Returns (logContainerError)
- Nothing.

##### Throws (logContainerError)
- Depends on logger backend.

##### When to Use It (logContainerError)
- Exception handling around resolution and boot operations.

##### Common Mistakes (logContainerError)
- Swallowing errors without logging enough context for debugging.

### Method: getLoggingStats(…)

#### Technical Explanation (getLoggingStats)
Returns stats about logging (e.g., created loggers, usage counts, etc.).

##### For Humans: What This Means (getLoggingStats)
It helps you understand how logging is being used.

##### Parameters (getLoggingStats)
- None.

##### Returns (getLoggingStats)
- Stats array.

##### Throws (getLoggingStats)
- None.

##### When to Use It (getLoggingStats)
- Diagnostics and admin tooling.

##### Common Mistakes (getLoggingStats)
- Treating stats as performance metrics; they’re logging metrics.

### Method: flushAll(…)

#### Technical Explanation (flushAll)
Flushes buffered logs/telemetry if underlying loggers support it.

##### For Humans: What This Means (flushAll)
It forces logs to be written out.

##### Parameters (flushAll)
- None.

##### Returns (flushAll)
- Nothing.

##### Throws (flushAll)
- Depends on logger backend.

##### When to Use It (flushAll)
- Shutdown hooks.

##### Common Mistakes (flushAll)
- Forgetting to flush in environments where buffering is enabled.

### Method: logHealthCheck(…)

#### Technical Explanation (logHealthCheck)
Logs health check results with status context.

##### For Humans: What This Means (logHealthCheck)
It records “container health is OK/warn/critical”.

##### Parameters (logHealthCheck)
- `$healthData`: Health report payload.

##### Returns (logHealthCheck)
- Nothing.

##### Throws (logHealthCheck)
- Depends on logger backend.

##### When to Use It (logHealthCheck)
- Periodic health checks and boot validations.

##### Common Mistakes (logHealthCheck)
- Logging sensitive details from health payloads.

### Method: logTelemetryExport(…)

#### Technical Explanation (logTelemetryExport)
Logs that telemetry data was exported to a sink, including number of datapoints.

##### For Humans: What This Means (logTelemetryExport)
It records “we shipped telemetry out”.

##### Parameters (logTelemetryExport)
- `$sink`: Sink identifier/name.
- `$dataPoints`: Number of data points exported.

##### Returns (logTelemetryExport)
- Nothing.

##### Throws (logTelemetryExport)
- Depends on logger backend.

##### When to Use It (logTelemetryExport)
- When exporting telemetry to files/monitoring systems.

##### Common Mistakes (logTelemetryExport)
- Treating export logs as proof that monitoring ingested the data; it only proves export was attempted.

## Risks, Trade-offs & Recommended Practices
- Risk: Logging secrets by accident.
  - Why it matters: logs are often widely accessible.
  - Design stance: sanitize by default; be explicit about what is safe.
  - Recommended practice: keep sanitization rules updated and treat config logs as sensitive.
- Trade-off: Too much logging can become noise.
  - Why it matters: noisy logs hide real incidents.
  - Design stance: log with intent; prefer structured, filterable context.
  - Recommended practice: sample high-frequency events and provide knobs via `TelemetryConfig`.

### For Humans: What This Means (Risks)
Logs are only useful if they’re safe and readable. This file tries to keep them that way.

## Related Files & Folders
- `docs_md/Observe/Metrics/EnhancedMetricsCollector.md`: Uses logging integration for rich telemetry.
- `docs_md/Guard/Enforce/SecureServiceResolver.md`: Logs security-relevant resolution events.
- `docs_md/Features/Operate/Config/TelemetryConfig.md`: Controls what gets logged and exported.

### For Humans: What This Means (Related)
This file is the “log language” of the container; other parts use it to tell their story.

