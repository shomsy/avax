# ErrorDTO

## Quick Summary
- Represents a safe, structured error outcome without throwing.
- Carries a human-safe message, a machine-readable code, and optional context.
- Exists so guard/validation flows can return errors as data rather than exceptions.

### For Humans: What This Means (Summary)
It’s a clean error “envelope” you can pass around without crashing the flow.

## Terminology (MANDATORY, EXPANSIVE)- **DTO**: Small data object used to transport information.
- **Error code**: Stable identifier you can match on in code.
- **Context**: Extra debug info suitable for logs.
- **SensitiveParameter**: Attribute that prevents accidental exposure of sensitive values in traces.

### For Humans: What This Means
Message is for people, code is for systems, context is for debugging.

## Think of It
Like a support ticket: it has a title (message), category (code), and notes (context).

### For Humans: What This Means (Think)
It standardizes error reporting.

## Story Example
A guard policy check fails. Instead of throwing immediately, the guard returns an `ErrorDTO`. A kernel step then decides to throw a `ContainerException` with a safe message.

### For Humans: What This Means (Story)
You can keep error creation and error handling separate.

## For Dummies
- Create an `ErrorDTO` when you want to report a failure without throwing.
- Use `message` for safe text.
- Use `code` for a stable identifier.
- Use `context` for debugging.

### For Humans: What This Means (Dummies)
It’s “structured failure as data.”

## How It Works (Technical)
Readonly object with constructor-initialized fields. `code` is marked sensitive.

### For Humans: What This Means (How)
It’s immutable and safe to pass around.

## Architecture Role
Used by guard/validation subsystems to communicate failures in a structured way. Can be converted into exceptions by kernel steps.

### For Humans: What This Means (Role)
It’s the error payload that can later become an exception.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $message, string $code = 'error', array $context = [])

#### Technical Explanation (__construct)
Initializes the DTO.

##### For Humans: What This Means (__construct)
Creates the error envelope.

##### Parameters (__construct)
- `string $message`: Safe error message.
- `string $code`: Machine-readable code.
- `array $context`: Debug context.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
When returning failure results from guards/validators.

##### Common Mistakes (__construct)
Putting secrets into `message` or `context`.

## Risks, Trade-offs & Recommended Practices
- **Risk: Leaking sensitive context**. Keep context logger-friendly and sanitized.
- **Practice: Use stable codes**. Codes should be consistent for automation.

### For Humans: What This Means (Risks)
Don’t leak secrets, and keep codes predictable.

## Related Files & Folders
- `docs_md/Features/Core/DTO/index.md`: DTO overview.
- `docs_md/Features/Core/Exceptions/ContainerException.md`: Exception counterpart.

### For Humans: What This Means (Related)
DTOs report errors as data; exceptions stop execution.
