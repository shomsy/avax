# NullTelemetrySink

## Quick Summary

- This file provides a no-op `TelemetrySinkInterface` implementation that records nothing.
- It exists so you can keep telemetry hooks in place without paying overhead when telemetry is disabled.
- It removes the complexity of “do we need null checks everywhere?” by giving you a sink that safely does nothing.

### For Humans: What This Means (Summary)

It’s an “off switch” that still fits into the same plug.

## Terminology (MANDATORY, EXPANSIVE)

- **No-op**: An operation that intentionally does nothing.
    - In this file: `record()` returns immediately without side effects.
    - Why it matters: no-op implementations preserve interfaces without cost.
- **Disabled telemetry**: A state where you don’t want measurement overhead.
    - In this file: the null sink represents that disabled state.
    - Why it matters: observability must be optional in performance-sensitive environments.

### For Humans: What This Means (Terms)

Your code can keep calling `record()`, and nothing happens—on purpose.

## Think of It

Think of it like shouting into a soundproof room. You can still “say the words”, but nobody hears them, and you don’t
bother anyone.

### For Humans: What This Means (Think)

It keeps your code structure consistent without producing noise or overhead.

## Story Example

In development, you use a real metrics collector. In production, you disable telemetry for latency reasons. Instead of
putting `if ($telemetryEnabled)` checks everywhere, you inject `NullTelemetrySink`. The pipeline still calls `record()`,
but it’s effectively free.

### For Humans: What This Means (Story)

You can turn telemetry off without rewriting code.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. Sometimes you want telemetry; sometimes you don’t.
2. This sink does nothing when `record()` is called.
3. Your code doesn’t need special branches.

## How It Works (Technical)

The class implements `TelemetrySinkInterface` and defines `record()` as a no-op. That’s it. The power comes from using
the Null Object pattern to simplify call sites.

### For Humans: What This Means (How)

It’s boring code that saves you from messy null checks everywhere.

## Architecture Role

- Why this file lives in `Observe/Metrics/Sink`: it’s a telemetry sink implementation.
- What depends on it: container setups that want telemetry hooks without overhead.
- What it depends on: the sink interface.
- System-level reasoning: the Null Object pattern keeps instrumentation code clean and safe.

### For Humans: What This Means (Role)

It’s the “do nothing, but don’t crash” component.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: record(…)

#### Technical Explanation (record)

Accepts telemetry event parameters and intentionally performs no work.

##### For Humans: What This Means (record)

It’s a sink that discards events.

##### Parameters (record)

- `$abstract`: Service id (ignored).
- `$durationMs`: Duration (ignored).
- `$strategy`: Strategy label (ignored).

##### Returns (record)

- Nothing.

##### Throws (record)

- None.

##### When to Use It (record)

- When telemetry is disabled, but you want to keep the same pipeline structure.

##### Common Mistakes (record)

- Expecting this sink to preserve data; it will not.

## Risks, Trade-offs & Recommended Practices

- Trade-off: You lose observability.
    - Why it matters: when telemetry is off, debugging production incidents can be harder.
    - Design stance: provide knobs to enable telemetry temporarily when diagnosing.
    - Recommended practice: consider sampling-based telemetry rather than a full shutdown when possible.

### For Humans: What This Means (Risks)

Turning telemetry off makes things faster, but it can make incidents harder to understand.

## Related Files & Folders

- `docs_md/Observe/Metrics/Sink/TelemetrySinkInterface.md`: The contract this implements.
- `docs_md/Observe/Metrics/MetricsCollector.md`: The “real” lightweight collector alternative.

### For Humans: What This Means (Related)

Null sink is “off”; MetricsCollector is “basic on”.

