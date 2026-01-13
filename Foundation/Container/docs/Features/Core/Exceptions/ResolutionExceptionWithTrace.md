# ResolutionExceptionWithTrace

## Quick Summary

Technical: Specialized resolution exception that embeds the `ResolutionTrace`, supports JSON serialization, and shortens
string output for readability.

### For Humans: What This Means

When resolution fails, this exception carries the whole story of what happened so you don’t have to parse log strings.

## Terminology

- **ResolutionTrace**: Ordered list of resolution stages and outcomes.
- **JSON serialization**: Ability to emit a structured payload for tools.
- **Terminal Transition**: A pipeline end state (success/failure/not-found).

### For Humans: What This Means

The trace is the breadcrumb trail; JSON is the shareable format; terminals are where the trail stops.

## Think of It

Like an aircraft incident report: it includes the flight path (trace) and a concise summary you can quickly read.

### For Humans: What This Means

You get both the short headline and the detailed black-box data.

## Story Example

The engine can’t find `PaymentGateway`. It records the trace and throws `ResolutionExceptionWithTrace`. Your error
handler dumps the JSON payload to a log aggregator, and the CLI prints the shortened 10-line summary for quick
debugging.

### For Humans: What This Means

You immediately know which steps were tried and failed without guessing.

## For Dummies

1. Resolution fails.
2. The engine throws `ResolutionExceptionWithTrace` with the trace inside.
3. `__toString()` shows a brief view; `jsonSerialize()` gives the full data.
4. You inspect and fix the missing binding.

### For Humans: What This Means

When things break, this exception hands you the map of the failure.

## How It Works

- Stores a `ResolutionTrace` instance on construction.
- `__toString()` truncates to 10 entries for readable logs.
- Implements `JsonSerializable` to expose the entire trace.

### For Humans: What This Means

It keeps the raw data but also gives you a quick, trimmed version for humans.

## Architecture Role

- **Lives in**: `Features/Core/Exceptions`
- **Role**: Structured failure carrier for resolution paths.
- **Collaborators**: `Engine` (producer), `ResolutionTrace` (payload), error handlers/observers (consumers).

### For Humans: What This Means

It’s the vehicle that transports the trace from the engine to whatever handles the error.

## Methods

### Method: __construct(ResolutionTrace $trace, string $message = '', int $code = 0, ?Throwable $previous = null) {#method-__construct}

Technical: Initializes the exception with a trace and optional standard exception data.

### For Humans: What This Means

You build it with the failure details and an optional previous exception.

#### Parameters

- `ResolutionTrace $trace` The recorded pipeline steps.
- `string $message` Human-readable summary.
- `int $code` Numeric code.
- `Throwable|null $previous` Chained exception.

#### Returns

- `self`

#### Throws

- None.

#### When to use it

- When propagating a resolution failure with context.

#### Common mistakes

- Dropping the original trace or not passing the previous exception when wrapping.

### Method: trace() {#method-trace}

Technical: Accessor for the stored `ResolutionTrace`.

### For Humans: What This Means

Call this to get the full breadcrumb trail.

#### Parameters

- None.

#### Returns

- `ResolutionTrace`

#### Throws

- None.

#### When to use it

- In error handlers or tests that need the raw trace.

#### Common mistakes

- Ignoring this and re-parsing `__toString()`.

### Method: __toString() {#method-__tostring}

Technical: Produces a trimmed, human-readable string limited to 10 trace entries.

### For Humans: What This Means

A quick summary you can print without overwhelming logs.

#### Parameters

- None.

#### Returns

- `string`

#### Throws

- None.

#### When to use it

- Logging or CLI display.

#### Common mistakes

- Expecting full trace coverage; for that use `jsonSerialize()`.

### Method: jsonSerialize() {#method-jsonserialize}

Technical: Emits an array with message and full trace for JSON encoding.

### For Humans: What This Means

Use this to send the trace to monitoring tools or APIs.

#### Parameters

- None.

#### Returns

- `array{message:string,trace:list<array{stage:string,outcome:string,state:string}>}`

#### Throws

- None.

#### When to use it

- Structured logging or programmatic consumption of trace data.

#### Common mistakes

- Forgetting to encode to JSON before transporting.

## Risks & Trade-offs

- **Payload size**: Full traces can be verbose; ensure logging limits are respected.
- **Overuse**: Throw only on genuine resolution failures to avoid noise.

### For Humans: What This Means

The trace is detailed; use it where it matters so logs stay useful.

## Related Files & Folders

- `Features/Actions/Resolve/Engine.php`: Throws this exception on not-found paths.
- `Observe/Trace/ResolutionTrace.php`: Data structure carried by the exception.

### For Humans: What This Means

The engine produces the exception; the trace object is the cargo inside it.
