# TraceObserverInterface

## Quick Summary

- Technical: Contract for consumers that want to receive resolution traces.
- Allows plugging tracing into metrics/logging without coupling the engine.

### For Humans: What This Means

If you want to listen to the resolver’s breadcrumbs, implement this interface.

## Terminology

- **Trace**: `ResolutionTrace` instance for a single resolution.
- **Observer**: Consumer that records or forwards the trace.

### For Humans: What This Means

Trace is the story; observer is who reads it.

## Think of It

Like a webhook: the engine emits a trace, the observer handles it.

### For Humans: What This Means

You subscribe to resolution stories and store or display them.

## Story Example

Engine finishes a resolution and calls `observer->record($trace)`, which writes JSON to a debug log.

### For Humans: What This Means

You can log or visualize what happened each time something is resolved.

## For Dummies

1. Implement `record(ResolutionTrace $trace): void`.
2. Pass the observer to the engine when resolving.
3. The observer receives every trace.

### For Humans: What This Means

Create a listener, give it to the resolver, and it will get the breadcrumb list.

## How It Works (Technical)

- Single method `record(ResolutionTrace $trace): void`.

### For Humans: What This Means

One callback with the whole trace.

## Architecture Role

Decouples trace production (engine) from consumption (logging/metrics/UI).

### For Humans: What This Means

Engine doesn’t know where traces go; observers decide.

## Methods

- `record(ResolutionTrace $trace): void`

### For Humans: What This Means

Engine calls this with the trace.

## Risks & Trade-offs

- Observer misuse can add overhead; keep lightweight for hot paths.

### For Humans: What This Means

Be careful: heavy observers can slow resolution.

## Related Files & Folders

- `Observe/Trace/ResolutionTrace.php` — the payload.
- `Features/Actions/Resolve/Engine.php` — emits traces.

### For Humans: What This Means

Engine writes the trace; observers listen to it.
