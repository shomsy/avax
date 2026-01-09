# DiagnosticsManager

## Quick Summary
- This file provides a small facade over inspection and telemetry utilities: an `Inspector`, optional `MetricsCollector`, and optional `ResolutionTimeline`.
- It exists so callers can access diagnostics from one place without needing to know which tools are enabled.
- It removes the complexity of “do we have metrics/timeline enabled?” by returning null when a tool isn’t present.

### For Humans: What This Means
It’s a remote control for diagnostics: one object that gives you the inspector and (maybe) telemetry.

## Terminology (MANDATORY, EXPANSIVE)
- **Facade**: A simplified interface over multiple components.
  - In this file: diagnostics tools are exposed via a tiny API.
  - Why it matters: it keeps call sites clean and stable.
- **Inspector**: A component that inspects container definitions and state.
  - In this file: `inspect()` delegates to the underlying `Inspector`.
  - Why it matters: it provides the “tell me about this service” functionality.
- **Metrics collector**: A component that records and reports telemetry.
  - In this file: `metrics()` returns the metrics collector or `null`.
  - Why it matters: telemetry shouldn’t be mandatory for the container to work.
- **Timeline**: A chronological trace of resolution events.
  - In this file: `timeline()` returns the timeline or `null`.
  - Why it matters: timelines help debug “what happened when” in resolution.

### For Humans: What This Means
It makes diagnostics optional, without making your code messy.

## Think of It
Think of it like a car dashboard. Some cars have advanced sensors; some don’t. The dashboard still exists, and it shows what’s available.

### For Humans: What This Means
Your code can ask for diagnostics without crashing when certain tools are turned off.

## Story Example
In development you enable metrics and timeline tracking. In production you disable them for performance. Your admin tooling calls `DiagnosticsManager::metrics()` and gracefully handles `null` instead of crashing, while still letting you inspect single services via `inspect($id)` in both environments.

### For Humans: What This Means
You get a nice dev experience without forcing production overhead.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You want inspection and telemetry.
2. Sometimes telemetry is disabled.
3. This class gives you inspection always, telemetry only if available.

## How It Works (Technical)
The manager is constructed with an `Inspector` and optional telemetry tools. `inspect()` returns either the raw inspector (when no id is provided) or delegates to `Inspector::inspect()` (when an id is provided). `metrics()` and `timeline()` simply return stored nullable references.

### For Humans: What This Means
It’s a tiny wrapper whose job is “make diagnostics easy to access and safe to call”.

## Architecture Role
- Why this file lives in `Observe/Inspect`: it’s a convenience entry point for diagnostics usage.
- What depends on it: tooling, debug UIs, CLI commands, admin panels.
- What it depends on: inspector and optional telemetry implementations.
- System-level reasoning: observability is best when it’s accessible, optional, and consistent.

### For Humans: What This Means
Diagnostics should feel like a feature you can turn on, not a dependency you can’t avoid.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Stores the inspector and optional telemetry tools.

##### For Humans: What This Means
It wires up the diagnostics “toolbox”.

##### Parameters
- `$inspector`: The inspector instance.
- `$metrics`: Optional metrics collector.
- `$timeline`: Optional resolution timeline tracker.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- When wiring diagnostics at boot.

##### Common Mistakes
- Treating `metrics()` and `timeline()` as always available.

### Method: inspect(…)

#### Technical Explanation
Returns the inspector (no id) or a single inspection result array (with id).

##### For Humans: What This Means
You can either grab the inspector tool, or ask it to inspect a specific service.

##### Parameters
- `$id`: Optional service id to inspect.

##### Returns
- An `Inspector` (when `$id` is null) or an array of inspection details (when `$id` is provided).

##### Throws
- Depends on the underlying inspector implementation.

##### When to Use It
- Tooling that sometimes needs an inspector object, sometimes just data.

##### Common Mistakes
- Forgetting to branch on return type (`Inspector|array`).

### Method: metrics(…)

#### Technical Explanation
Returns the metrics collector if telemetry is enabled; otherwise null.

##### For Humans: What This Means
If metrics exist, you can use them. If not, no problem.

##### Parameters
- None.

##### Returns
- A `MetricsCollector` or `null`.

##### Throws
- None.

##### When to Use It
- When you want telemetry but can tolerate it being disabled.

##### Common Mistakes
- Calling methods on null without checking.

### Method: timeline(…)

#### Technical Explanation
Returns the resolution timeline tracker if enabled; otherwise null.

##### For Humans: What This Means
Same as metrics: use it if it’s there.

##### Parameters
- None.

##### Returns
- A `ResolutionTimeline` or `null`.

##### Throws
- None.

##### When to Use It
- When diagnosing resolution sequencing and performance.

##### Common Mistakes
- Treating it as always-on; timeline tracking can be expensive.

## Risks, Trade-offs & Recommended Practices
- Trade-off: Union return types can complicate call sites (`Inspector|array`).
  - Why it matters: callers must handle both.
  - Design stance: convenience is useful, but type clarity matters.
  - Recommended practice: keep call sites explicit, or provide separate methods (`getInspector()` and `inspectOne()`).

### For Humans: What This Means
Convenience is nice, but don’t let it make your code confusing.

## Related Files & Folders
- `docs_md/Observe/Inspect/Inspector.md`: Does the real inspection work.
- `docs_md/Observe/Metrics/MetricsCollector.md`: A simple metrics collector used by diagnostics.
- `docs_md/Observe/Timeline/ResolutionTimeline.md`: Timeline tool for resolution tracing.

### For Humans: What This Means
This file is a “glue facade” over the actual diagnostic tools.

