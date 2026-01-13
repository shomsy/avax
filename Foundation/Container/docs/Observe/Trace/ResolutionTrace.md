# ResolutionTrace

## Quick Summary

- Technical: Immutable trace log for a single resolution run; records stages, outcomes, and states.
- Used for diagnostics and exception payloads.

### For Humans: What This Means

It’s the breadcrumb trail of how the resolver made (or failed) a decision.

## Terminology

- **Entry**: `{stage, outcome, state}` tuple.
- **Outcome**: Hit or miss (or terminal status).
- **State**: `ResolutionState` value representing the FSM node.

### For Humans: What This Means

Each line says “we tried X at state Y and it hit/missed.”

## Think of It

Like a black-box flight recorder for one resolution.

### For Humans: What This Means

If something goes wrong, you can replay what happened step by step.

## Story Example

Trace entries: contextual=miss, definition=miss, autowire=hit, terminal=success.

### For Humans: What This Means

The resolver skipped special cases, found a normal binding, and succeeded.

## For Dummies

1. Start with empty trace.
2. Record each stage outcome.
3. Export to array for logs/errors.

### For Humans: What This Means

You keep a running list of what happened, then read it later.

## How It Works (Technical)

- `record()` returns a new instance with appended entry.
- `toArray()` exports entries with state values as strings.

### For Humans: What This Means

It never mutates; every record call gives you a fresh copy you can serialize.

## Architecture Role

Supports diagnostics without coupling to logging/metrics implementations.

### For Humans: What This Means

Tracing is a first-class artifact you can pass to observers or errors.

## Methods

- `record(ResolutionState $state, string $stage, string $outcome): self`
- `toArray(): array`

### For Humans: What This Means

Add a line; dump the list.

## Risks & Trade-offs

- More entries mean more memory per resolution; keep stages minimal.

### For Humans: What This Means

Tracing has a cost—use it wisely in hot paths.

## Related Files & Folders

- `Observe/Trace/TraceObserverInterface.php` — consumers of traces.
- `Features/Actions/Resolve/Engine.php` — producer.

### For Humans: What This Means

Engine writes traces; observers can read them.
