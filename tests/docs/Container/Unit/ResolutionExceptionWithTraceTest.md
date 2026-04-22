# ResolutionExceptionWithTraceTest

## Quick Summary

Technical: Verifies that `ResolutionExceptionWithTrace` retains its trace, renders a readable string, and serializes to
JSON-friendly data.

### For Humans: What This Means

This test proves the new trace-carrying exception actually keeps and exposes the breadcrumb trail.

## Terminology

- **Trace**: The recorded sequence of resolution steps.
- **Serialization**: Converting the exception payload into JSON-ready arrays.
- **Assertion**: A PHPUnit check that must hold true.

### For Humans: What This Means

Trace is the story, serialization is how we share it, assertions are the promises this test enforces.

## Think of It

Like checking a black-box recorder: the test confirms you can read a quick summary and also extract the full log.

### For Humans: What This Means

We make sure the “flight recorder” is readable and exportable.

## Story Example

The test builds a small trace, throws the exception, and inspects both the string output and the serialized trace count
to ensure nothing is lost.

### For Humans: What This Means

It recreates a tiny failure and confirms the exception contains everything you’d need to debug it.

## For Dummies

1. Build a trace with two entries.
2. Create the exception with that trace.
3. Check that the trace is accessible and the string mentions a stage.
4. Serialize and confirm the trace entries are present.

### For Humans: What This Means

The test just confirms “trace in, trace out” plus a readable summary.

## How It Works

- Uses `ResolutionTrace` to create entries.
- Constructs `ResolutionExceptionWithTrace`.
- Asserts accessor, string casting, and `jsonSerialize()` output.

### For Humans: What This Means

It pokes every public surface of the exception to ensure it behaves.

## Architecture Role

- **Lives in**: `tests/Unit`
- **Role**: Guardrail ensuring trace-carrying exceptions remain usable.
- **Collaborators**: `ResolutionExceptionWithTrace`, `ResolutionTrace`.

### For Humans: What This Means

This is the safety net for the new exception’s behavior.

## Methods

### Method: testCarriesTraceAndSerializes() {#method-testcarriestraceandserializes}

Technical: Builds a trace, constructs the exception, and asserts trace retention, string summary content, and
JSON-friendly output.

### For Humans: What This Means

It proves the exception keeps the trace and exposes it in both human and machine formats.

#### Parameters

- None.

#### Returns

- `void`

#### Throws

- None (apart from PHPUnit failures).

#### When to use it

- Run automatically with the test suite.

#### Common mistakes

- Forgetting to update this test when the exception surface changes.

## Risks & Trade-offs

- **Surface drift**: If exception behavior changes, this test must evolve or it will fail.
- **String brittleness**: Assertions rely on partial string content; keep messages stable.

### For Humans: What This Means

Changing the exception without updating the test will break the suite; keep outputs predictable.

## Related Files & Folders

- `Features/Core/Exceptions/ResolutionExceptionWithTrace.php`: The class under test.
- `Observe/Trace/ResolutionTrace.php`: Trace data structure used in the test.

### For Humans: What This Means

The test exists to ensure the exception and its trace helper keep working together.
