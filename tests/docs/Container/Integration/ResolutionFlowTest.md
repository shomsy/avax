# ResolutionFlowTest

## Quick Summary

Technical: Integration test proving the resolution trace records evaluate and instantiate stages during autowiring.

### For Humans: What This Means

Checks that the full pipeline is reflected in the trace when resolving a simple class.

## Terminology

- **Trace**: Recorded sequence of FSM stages during resolution.
- **Autowire**: Automatic instantiation of a class with no explicit binding.

### For Humans: What This Means

The trace is the breadcrumb trail; autowire is resolving a class without a manual definition.

## Think of It

Like a flight recorder confirming every takeoff and landing step was logged.

### For Humans: What This Means

We verify that building an object logs both the “prep” (evaluate) and “build” (instantiate) stages.

## Story Example

Resolve `stdClass` with autowiring enabled; the observer captures a trace that includes `evaluate` and `instantiate`
stages, proving the FSM covers construction.

### For Humans: What This Means

Even simple resolutions show the full pipeline in the logs.

## For Dummies

1. Build a container in non-strict mode (autowire on).
2. Resolve `stdClass` via the engine with a trace observer.
3. Confirm the trace contains `evaluate` and `instantiate`.

### For Humans: What This Means

If these stages are missing, the pipeline isn’t being recorded correctly.

## How It Works (Technical)

- Uses `ContainerBuilder` (debug=false) to allow autowiring.
- Extracts the engine via reflection.
- Resolves `stdClass` with a `TraceObserverInterface` implementation.
- Asserts the trace stages include `evaluate` and `instantiate`.

### For Humans: What This Means

The test drives a real resolution and inspects the recorded stages.

## Architecture Role

- **Lives in**: `tests/Integration`
- **Role**: Guards trace completeness for the FSM-backed resolution pipeline.

### For Humans: What This Means

Ensures the trace mirrors the pipeline when autowiring.

## Methods

### Method: testTraceIncludesEvaluateAndInstantiateStages() {#method-testtraceincludesevaluateandinstantiatestages}

Technical: Resolves `stdClass` and asserts the trace contains `evaluate` and `instantiate`.

### For Humans: What This Means

Confirms construction stages are logged in the trace.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None (aside from assertion failures).

#### When to use it

- Run in suite to guard against regressions in trace coverage.

#### Common mistakes

- Changing FSM stages without updating trace expectations.

## Risks & Trade-offs

- Uses reflection to extract the engine; update if internals change.

### For Humans: What This Means

If engine wiring changes, refresh the extractor helper.

## Related Files & Folders

- `Features/Actions/Resolve/Engine.php`
- `Observe/Trace/ResolutionTrace.php`
- `Core/Kernel/ResolutionPipelineController.php`

### For Humans: What This Means

The test verifies trace output from the resolution engine and FSM controller.
