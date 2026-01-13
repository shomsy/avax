# CollectMetrics

## Quick Summary

- This file collects ad-hoc metric events in memory and optionally merges them with a `MetricsCollector` snapshot.
- It exists so you can quickly instrument container flows and retrieve a single combined snapshot for debugging.
- It removes the complexity of needing a full telemetry stack just to see “what happened recently”.

### For Humans: What This Means (Summary)

It’s a notebook where the container writes down events, and then you can ask “what did we record?”

## Terminology (MANDATORY, EXPANSIVE)

- **Event**: A named occurrence plus context data.
    - In this file: an event is `{event, data, time}`.
    - Why it matters: events let you understand sequences and triggers.
- **Snapshot**: A combined view of stored events and optional collector stats.
    - In this file: `collect()` returns `events` plus `MetricsCollector::getSnapshot()` when available.
    - Why it matters: one response payload is easier for tooling.

### For Humans: What This Means (Terms)

It’s a simple debug feed you can inspect when something feels off.

## Think of It

Think of it like leaving sticky notes on a timeline: “resolved X”, “cache miss”, “error happened”. Later you gather the
notes into a report.

### For Humans: What This Means (Think)

It helps you reconstruct what happened without attaching a full debugger.

## Story Example

During a boot sequence you suspect the container is doing too many resolutions. You record events on each resolution
step. Then you call `collect()` and you get both the raw events and basic aggregated metrics (if a `MetricsCollector`
was provided).

### For Humans: What This Means (Story)

You get “detailed breadcrumbs + summary stats” in one place.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. Call `record('something', ['details' => ...])`.
2. Later call `collect()` to get all events (and maybe metrics).
3. Use the output to debug patterns.

## How It Works (Technical)

The class stores an in-memory `$events` list. `record()` appends an event with `microtime(true)` timestamp. `collect()`
checks if `$metrics` is an instance of `MetricsCollector` and merges its snapshot into the result, then adds `events`.

### For Humans: What This Means (How)

It’s intentionally lightweight: add events, then read them back.

## Architecture Role

- Why this file lives in `Observe/Metrics`: it supports observability and tooling without requiring complex
  integrations.
- What depends on it: debug tooling and possibly CLI inspection flows.
- What it depends on: optionally the `MetricsCollector` class.
- System-level reasoning: lightweight instrumentation is valuable during development and incident response.

### For Humans: What This Means (Role)

Sometimes you don’t need a whole monitoring stack—you just need a quick, clear snapshot.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)

Stores an optional metrics collector dependency for merging snapshots.

##### For Humans: What This Means (__construct)

If you pass a metrics collector, you’ll get richer output.

##### Parameters (__construct)

- `$metrics`: Optional metrics collector (or any value).

##### Returns (__construct)

- Nothing.

##### Throws (__construct)

- None.

##### When to Use It (__construct)

- When wiring metrics tooling in dev/admin contexts.

##### Common Mistakes (__construct)

- Passing a wrong type and expecting `collect()` to include metrics; it checks instance type.

### Method: record(…)

#### Technical Explanation (record)

Appends a new event entry with name, data, and timestamp.

##### For Humans: What This Means (record)

It adds a breadcrumb.

##### Parameters (record)

- `$event`: Event name.
- `$data`: Event context data.

##### Returns (record)

- Nothing.

##### Throws (record)

- None.

##### When to Use It (record)

- When you want a quick event trail for debugging.

##### Common Mistakes (record)

- Recording huge payloads and bloating memory.

### Method: collect(…)

#### Technical Explanation (collect)

Builds and returns a snapshot, optionally including aggregated metrics.

##### For Humans: What This Means (collect)

It returns “everything we recorded so far”.

##### Parameters (collect)

- None.

##### Returns (collect)

- An array snapshot including `events` and optionally aggregated metrics keys.

##### Throws (collect)

- None.

##### When to Use It (collect)

- Debug endpoints, CLI reports, incident snapshots.

##### Common Mistakes (collect)

- Treating the result as stable history; it’s in-memory only.

## Risks, Trade-offs & Recommended Practices

- Trade-off: Memory grows with recorded events.
    - Why it matters: long-running processes can accumulate lots of events.
    - Design stance: keep this tool for debugging and short-lived sessions.
    - Recommended practice: cap event count or periodically reset for long-running environments.

### For Humans: What This Means (Risks)

It’s a notebook, not a database—don’t keep infinite notes in memory.

## Related Files & Folders

- `docs_md/Observe/Metrics/MetricsCollector.md`: Provides the aggregated snapshot this file can include.
- `docs_md/Observe/Inspect/DiagnosticsManager.md`: Can expose metrics tooling via a facade.

### For Humans: What This Means (Related)

This is the “event notebook” and MetricsCollector is the “stats calculator”.

