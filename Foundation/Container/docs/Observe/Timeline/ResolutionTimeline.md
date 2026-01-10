# ResolutionTimeline

## Quick Summary
- This file records resolution events over time in a bounded in-memory buffer, including timestamps, durations, memory deltas, and nesting depth.
- It exists so you can debug and profile the container’s resolution flow as a timeline (like a waterfall chart).
- It removes the complexity of “what happened when?” by giving you a chronological event list with performance and memory context.

### For Humans: What This Means (Summary)
It’s a timeline recorder: start an event, end an event, then look at the story.

## Terminology (MANDATORY, EXPANSIVE)
- **Timeline event**: One resolution operation with start/end and metadata.
  - In this file: events store `abstract`, timestamps, `duration_ms`, memory fields, and depth.
  - Why it matters: performance and debugging depend on knowing sequencing.
- **Circular buffer / retention**: Keeping only the most recent events to avoid memory leaks.
  - In this file: `maxEvents` caps storage and slices older entries.
  - Why it matters: long-running processes must not accumulate infinite diagnostics data.
- **Nesting depth**: How deep in the resolution chain you are.
  - In this file: `$depth` is incremented on start and decremented on end.
  - Why it matters: depth helps you visualize dependency chains.
- **Memory delta**: Memory change during an event.
  - In this file: `memory_delta` compares usage at end vs start.
  - Why it matters: it helps identify heavy resolutions or leaks.

### For Humans: What This Means (Terms)
It helps you see not just “slow”, but “slow and deep” or “slow and memory-hungry”.

## Think of It
Think of it like a stopwatch that also records a breadcrumb trail and how much your backpack weighs before and after each step.

### For Humans: What This Means (Think)
You get timing plus context, which is what makes profiling useful.

## Story Example
A request feels slow. You enable timeline tracking and see that resolving `UserService` triggers a deep chain and one dependency takes 200ms. You also see memory spikes during that event. You now know exactly where to profile and optimize.

### For Humans: What This Means (Story)
Instead of profiling everything, you profile the one slow piece the timeline points to.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Call `start('service')` before resolution.
2. Call `end($id)` after resolution.
3. Later call `getEvents()` or `getSlowest()` to see what happened.

## How It Works (Technical)
`start()` increments depth, assigns an event id, performs retention cleanup when max capacity is reached, then appends an event record with timestamps and initial memory usage. `end()` closes the event and computes duration in milliseconds and memory delta. `getEvents()` returns all records, `getSlowest()` returns the top N by duration, `clear()` resets state, and `setMaxEvents()` configures retention.

### For Humans: What This Means (How)
It’s like a tiny profiler you can turn on for container resolution.

## Architecture Role
- Why this file lives in `Observe/Timeline`: it’s a timeline tool in the observability layer.
- What depends on it: diagnostics and any tooling that needs resolution tracing.
- What it depends on: basic PHP timing and memory functions.
- System-level reasoning: timeline profiling helps you optimize and debug without invasive instrumentation.

### For Humans: What This Means (Role)
If you can see the timeline, you can fix the right thing faster.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: start(…)

#### Technical Explanation (start)
Starts tracking an event and returns an id that must be ended later.

##### For Humans: What This Means (start)
It’s “begin timing this resolution step”.

##### Parameters (start)
- `$abstract`: Service id being resolved.

##### Returns (start)
- An integer event id.

##### Throws (start)
- None.

##### When to Use It (start)
- At the beginning of a resolution step.

##### Common Mistakes (start)
- Forgetting to call `end()` and leaving events open.

### Method: end(…)

#### Technical Explanation (end)
Closes an event, computes duration and memory delta, and updates depth.

##### For Humans: What This Means (end)
It’s “stop timing and store results”.

##### Parameters (end)
- `$id`: The id returned by `start()`.

##### Returns (end)
- Nothing.

##### Throws (end)
- None.

##### When to Use It (end)
- At the end of a resolution step.

##### Common Mistakes (end)
- Ending with the wrong id and skewing your data.

### Method: getEvents(…)

#### Technical Explanation (getEvents)
Returns all stored events in chronological order.

##### For Humans: What This Means (getEvents)
It gives you the full timeline.

##### Parameters (getEvents)
- None.

##### Returns (getEvents)
- Array of event records.

##### Throws (getEvents)
- None.

##### When to Use It (getEvents)
- Diagnostics panels and exporting timeline data.

##### Common Mistakes (getEvents)
- Treating event record shape as stable API without versioning.

### Method: getSlowest(…)

#### Technical Explanation (getSlowest)
Sorts events by duration and returns the slowest N events.

##### For Humans: What This Means (getSlowest)
It finds the biggest time sinks.

##### Parameters (getSlowest)
- `$limit`: Max number of events.

##### Returns (getSlowest)
- Array of slowest events.

##### Throws (getSlowest)
- None.

##### When to Use It (getSlowest)
- Bottleneck hunting.

##### Common Mistakes (getSlowest)
- Ignoring depth; the slowest event might be slow because it triggers many nested events.

### Method: clear(…)

#### Technical Explanation (clear)
Clears all stored timeline events and resets depth.

##### For Humans: What This Means (clear)
It resets the recorder.

##### Parameters (clear)
- None.

##### Returns (clear)
- Nothing.

##### Throws (clear)
- None.

##### When to Use It (clear)
- Tests or starting a fresh diagnostics session.

##### Common Mistakes (clear)
- Clearing mid-incident and losing valuable trace history.

### Method: setMaxEvents(…)

#### Technical Explanation (setMaxEvents)
Configures the retention cap; enforces a minimum to avoid pathological settings.

##### For Humans: What This Means (setMaxEvents)
It controls how much history you keep.

##### Parameters (setMaxEvents)
- `$max`: New maximum event count (minimum 100).

##### Returns (setMaxEvents)
- Nothing.

##### Throws (setMaxEvents)
- None.

##### When to Use It (setMaxEvents)
- Tuning memory usage vs observability detail.

##### Common Mistakes (setMaxEvents)
- Setting max too low and losing useful history.

## Risks, Trade-offs & Recommended Practices
- Trade-off: Keeping timelines costs memory and CPU.
  - Why it matters: sorting and storing events has overhead.
  - Design stance: keep it optional and bounded.
  - Recommended practice: enable in dev/staging; in production use bounded retention and sampling strategies.

### For Humans: What This Means (Risks)
It’s incredibly useful, but you should treat it like a “debug mode tool”, not a default on everything.

## Related Files & Folders
- `docs_md/Observe/Inspect/DiagnosticsManager.md`: Exposes timeline via a facade.
- `docs_md/Observe/Metrics/MetricsCollector.md`: Captures aggregated metrics alongside timeline detail.

### For Humans: What This Means (Related)
Timeline gives you sequence; metrics give you totals—together they tell the story.

