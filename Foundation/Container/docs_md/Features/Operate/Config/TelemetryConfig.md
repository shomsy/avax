# TelemetryConfig

## Quick Summary
- This file defines configuration for telemetry/metrics collection in the container.
- It exists so observability behavior can be tuned per environment (dev vs prod).
- It removes the complexity of scattered “telemetry flags” by putting them into one config object.

### For Humans: What This Means
This is how you decide whether the container “keeps notes” about what it’s doing and where those notes go.

## Terminology (MANDATORY, EXPANSIVE)
- **Telemetry**: Structured signals about behavior and performance.
  - In this file: controlled by `enabled`, `trackedEvents`, and sink settings.
  - Why it matters: you can’t improve what you can’t see.
- **Sink**: Where telemetry is written (null, JSON file, PSR logger).
  - In this file: `sink` and `outputPath`.
  - Why it matters: storage destination defines how you debug/monitor.
- **Sampling**: Recording only some events.
  - In this file: `sampleRate` (every Nth request).
  - Why it matters: full telemetry can be expensive in production.
- **Stack traces**: Call stacks included in diagnostics.
  - In this file: `includeStackTraces`.
  - Why it matters: great for dev; risky/noisy for prod.

### For Humans: What This Means
This config controls “how chatty” your container’s monitoring is.

## Think of It
Think of telemetry like a dashcam:
- In dev, you record everything.
- In prod, you record only incidents or sampled drives.

### For Humans: What This Means
Recording everything is expensive; sampling is a practical compromise.

## Story Example
In development, you enable JSON sink with stack traces so you can debug resolution failures quickly. In production, you switch to PSR sink with low sampling and no stack traces to avoid leaking sensitive details and to control overhead.

### For Humans: What This Means
Different environments need different levels of visibility and safety.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

Use `development()` locally, `production()` in prod, or `fromArray()` when loading from a config file.

## How It Works (Technical)
The class is a `final readonly` DTO with:
- constructor storing fields
- `fromArray()` factory method
- `development()` and `production()` presets

### For Humans: What This Means
It’s just structured settings that the rest of the system can read.

## Architecture Role
- Why it lives here: it configures observability behavior during container operation.
- What depends on it: bootstrap orchestrators and metrics exporters.
- What it depends on: nothing; it’s pure data.
- System-level reasoning: observability settings should be explicit and environment-driven.

### For Humans: What This Means
Telemetry shouldn’t be an afterthought—you decide it upfront.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores telemetry settings immutably.

##### For Humans: What This Means
It locks in “how we collect and where we send telemetry”.

##### Parameters
- `bool $enabled`
- `string $sink`
- `string $outputPath`
- `int $sampleRate`
- `bool $includeStackTraces`
- `array $trackedEvents`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- Prefer presets unless you need a custom setup.

##### Common Mistakes
- Enabling stack traces in production and leaking internal details.

### Method: fromArray(array $config)

#### Technical Explanation
Builds a config instance from a raw associative array.

##### For Humans: What This Means
Load telemetry settings from a config file.

##### Parameters
- `array $config`

##### Returns
- `self`

##### Throws
- No explicit exceptions.

##### When to Use It
- File-based configuration.

##### Common Mistakes
- Providing wrong event names and expecting them to be validated here (validation belongs elsewhere).

### Method: development()

#### Technical Explanation
Returns a dev-friendly telemetry preset (enabled, JSON sink, stack traces).

##### For Humans: What This Means
Maximum visibility for debugging.

##### Returns
- `self`

### Method: production()

#### Technical Explanation
Returns a prod-friendly telemetry preset (enabled, PSR sink, sampling, no stack traces).

##### For Humans: What This Means
Visibility with controlled overhead and safer defaults.

##### Returns
- `self`

## Risks, Trade-offs & Recommended Practices
- Risk: Too much telemetry overhead.
  - Why it matters: it can slow requests and increase memory use.
  - Design stance: sample in production.
  - Recommended practice: start with low sampling, increase temporarily when investigating issues.

### For Humans: What This Means
Telemetry is a flashlight—use it when you need it, but don’t leave it on full brightness all the time.

## Related Files & Folders
- `docs_md/Observe/Metrics/index.md`: Where metrics are collected/exported.
- `docs_md/Features/Operate/Config/BootstrapProfile.md`: Bundles this config with container config.

### For Humans: What This Means
TelemetryConfig decides “should we measure?”; Observe/Metrics decides “how do we measure?”.

