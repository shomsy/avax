# LoggerFactoryIntegration

## Quick Summary
- This file is the logging “hub” for container observability: it creates per-component loggers and provides structured logging helpers for resolution, registration, cache, config, performance, errors, health, and telemetry export.
- It exists so container logs are consistent, security-aware (sanitization), and easy to search.
- It removes the complexity of logging conventions by centralizing log formatting and context shaping.

### For Humans: What This Means
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

### For Humans: What This Means
This file is the “make logs useful and safe” layer.

## Think of It
Think of it like a newsroom editor. Events come in messy and inconsistent; the editor formats them so they’re readable, consistent, and safe to publish.

### For Humans: What This Means
Without this file, your logs will feel like a chaotic group chat.

## Story Example
You’re debugging a production issue. You filter logs by `container-resolution` and immediately see slow resolutions with service ids, durations, and strategies. You also check `container-errors` and see correlated exceptions with safe, redacted configuration context. You now have a complete story without leaking secrets.

### For Humans: What This Means
Good logging turns “I don’t know what happened” into “here’s exactly what happened”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. This class gives you a logger for a component.
2. It also provides methods like “log resolution” and “log error”.
3. It makes sure logs include useful context and redact secrets.

## How It Works (Technical)
The integration keeps a memoized map of component loggers to avoid repeated factory work. It produces channel names consistently and merges standard context fields (timestamps, event names, durations). It sanitizes configuration payloads before logging. Higher-level helpers (`logServiceResolution`, `logContainerError`, etc.) pick appropriate channels and severity, shaping context for predictable log output.

### For Humans: What This Means
It standardizes how the container talks in logs—so you don’t have to decode custom formats every time.

## Architecture Role
- Why this file lives in `Observe/Metrics`: logging is a key part of observability and is closely tied to telemetry configuration.
- What depends on it: secure resolvers, metrics collectors, bootstraps, and any component that logs container events.
- What it depends on: the logger factory and telemetry configuration.
- System-level reasoning: consistent logs are an operational contract; centralizing them prevents drift and leaks.

### For Humans: What This Means
Centralizing logging is how you keep it consistent when the system grows.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores the logger factory and telemetry configuration used to control logging behavior.

##### For Humans: What This Means
It gets the “how to create loggers” tool and the “what should we log” settings.

##### Parameters
- `$loggerFactory`: Creates per-channel loggers.
- `$config`: Telemetry config controlling what to log and how much detail.

##### Returns
- Nothing.

##### Throws
- Depends on underlying logger factory implementation.

##### When to Use It
- During container boot.

##### Common Mistakes
- Disabling telemetry and expecting structured logs to still be emitted.

### Method: createMetricsCollector(…)

#### Technical Explanation
Creates an `EnhancedMetricsCollector` wired with this integration’s logger factory and config.

##### For Humans: What This Means
It gives you a metrics collector that “matches” your logging setup.

##### Parameters
- None.

##### Returns
- `EnhancedMetricsCollector`.

##### Throws
- Depends on collector construction.

##### When to Use It
- When enabling rich metrics collection.

##### Common Mistakes
- Creating collectors manually with mismatched config.

### Method: logLifecycleEvent(…)

#### Technical Explanation
Logs major container lifecycle events with context.

##### For Humans: What This Means
It records “container started/shutdown/config loaded” moments.

##### Parameters
- `$event`: Event name string.
- `$context`: Extra context.

##### Returns
- Nothing.

##### Throws
- Logging backend exceptions (implementation-dependent).

##### When to Use It
- Boot/shutdown hooks.

##### Common Mistakes
- Logging secrets in `$context` without sanitization.

### Method: getComponentLogger(…)

#### Technical Explanation
Returns a memoized logger for a given component channel.

##### For Humans: What This Means
It gives each container component its own log stream.

##### Parameters
- `$component`: Component name (used to form channel).

##### Returns
- `ErrorLogger`.

##### Throws
- Depends on logger factory.

##### When to Use It
- When a component needs to log consistently.

##### Common Mistakes
- Using too many unique component names and creating noisy channel sprawl.

### Method: logServiceResolution(…)

#### Technical Explanation
Logs a service resolution event with duration, strategy, and context.

##### For Humans: What This Means
It records “resolved X in Y ms using strategy Z”.

##### Parameters
- Depends on signature; conceptually service id, duration, strategy, context.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- Resolution pipeline instrumentation.

##### Common Mistakes
- Mixing units for duration.

### Method: logServiceRegistration(…)

#### Technical Explanation
Logs service registration/binding events.

##### For Humans: What This Means
It records “we registered this service with these attributes”.

##### Parameters
- Depends on signature; typically service id, class, lifetime, tags, etc.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- During boot registration.

##### Common Mistakes
- Logging raw config without sanitization.

### Method: logCacheOperation(…)

#### Technical Explanation
Logs cache hits/misses/sets with timing and context.

##### For Humans: What This Means
It records “cache did something” so you can debug performance.

##### Parameters
- Depends on signature; conceptually operation name + context.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- Scope cache operations and prototype caches.

##### Common Mistakes
- Logging too frequently and creating log spam.

### Method: logConfigurationEvent(…)

#### Technical Explanation
Logs configuration-related events with sanitized payload.

##### For Humans: What This Means
It records “config loaded/changed/validated” without leaking secrets.

##### Parameters
- `$event`: Config event name.
- `$config`: Config payload.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- When configuration is loaded or validated.

##### Common Mistakes
- Passing deeply nested configs without understanding log volume.

### Method: logPerformanceWarning(…)

#### Technical Explanation
Logs a warning when a service resolution exceeds thresholds or shows performance risk.

##### For Humans: What This Means
It tells you “this service is slow” proactively.

##### Parameters
- Depends on signature; typically service id, duration, threshold, context.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- When monitoring slow resolution hotspots.

##### Common Mistakes
- Setting thresholds too low and creating noisy warnings.

### Method: logContainerError(…)

#### Technical Explanation
Logs container errors with safe context, optionally including stack traces based on config.

##### For Humans: What This Means
It records failures in a way that’s useful and safe.

##### Parameters
- Depends on signature; typically component name, throwable, context.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- Exception handling around resolution and boot operations.

##### Common Mistakes
- Swallowing errors without logging enough context for debugging.

### Method: getLoggingStats(…)

#### Technical Explanation
Returns stats about logging (e.g., created loggers, usage counts, etc.).

##### For Humans: What This Means
It helps you understand how logging is being used.

##### Parameters
- None.

##### Returns
- Stats array.

##### Throws
- None.

##### When to Use It
- Diagnostics and admin tooling.

##### Common Mistakes
- Treating stats as performance metrics; they’re logging metrics.

### Method: flushAll(…)

#### Technical Explanation
Flushes buffered logs/telemetry if underlying loggers support it.

##### For Humans: What This Means
It forces logs to be written out.

##### Parameters
- None.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- Shutdown hooks.

##### Common Mistakes
- Forgetting to flush in environments where buffering is enabled.

### Method: logHealthCheck(…)

#### Technical Explanation
Logs health check results with status context.

##### For Humans: What This Means
It records “container health is OK/warn/critical”.

##### Parameters
- `$healthData`: Health report payload.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- Periodic health checks and boot validations.

##### Common Mistakes
- Logging sensitive details from health payloads.

### Method: logTelemetryExport(…)

#### Technical Explanation
Logs that telemetry data was exported to a sink, including number of datapoints.

##### For Humans: What This Means
It records “we shipped telemetry out”.

##### Parameters
- `$sink`: Sink identifier/name.
- `$dataPoints`: Number of data points exported.

##### Returns
- Nothing.

##### Throws
- Depends on logger backend.

##### When to Use It
- When exporting telemetry to files/monitoring systems.

##### Common Mistakes
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

### For Humans: What This Means
Logs are only useful if they’re safe and readable. This file tries to keep them that way.

## Related Files & Folders
- `docs_md/Observe/Metrics/EnhancedMetricsCollector.md`: Uses logging integration for rich telemetry.
- `docs_md/Guard/Enforce/SecureServiceResolver.md`: Logs security-relevant resolution events.
- `docs_md/Features/Operate/Config/TelemetryConfig.md`: Controls what gets logged and exported.

### For Humans: What This Means
This file is the “log language” of the container; other parts use it to tell their story.

