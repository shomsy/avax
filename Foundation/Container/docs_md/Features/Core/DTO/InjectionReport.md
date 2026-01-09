# InjectionReport

## Quick Summary
- Captures what happened during injection analysis/execution for an object.
- Records injected properties, injected methods, and encountered errors.
- Exists to make injection introspection explainable and testable.

### For Humans: What This Means
It’s a diagnostic report that tells you what the container injected into an object.

## Terminology
- **Injection report**: Structured summary of injection points and outcomes.
- **Injected properties**: Map of property name to type.
- **Injected methods**: Map of method name to parameter descriptions.
- **Errors**: List of problems encountered.

### For Humans: What This Means
It’s a checklist of what got injected and what went wrong.

## Think of It
Like a mechanic’s inspection sheet after servicing a car: what parts were checked, what was replaced, and what issues remain.

### For Humans: What This Means
It’s your post-injection paperwork.

## Story Example
A developer calls `inspectInjection($object)` and gets an `InjectionReport` showing two properties were injectable, one method injection was possible, and one error occurred due to a missing service.

### For Humans: What This Means
You can see exactly why injection failed without guessing.

## For Dummies
- Read `success`.
- If `hasErrors()` is true, inspect `errors`.
- Use `properties` and `methods` to understand injection points.

### For Humans: What This Means
It’s a clear success/failure + details summary.

## How It Works (Technical)
Readonly DTO with a helper `hasErrors()`.

### For Humans: What This Means
A simple data object with a convenience check.

## Architecture Role
Used by internal container inspection APIs and tools to expose injection diagnostics.

### For Humans: What This Means
It powers “tell me what injection would do” features.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(bool $success, string $class, array $properties, array $methods, array $errors = [])

#### Technical Explanation
Initializes the report fields.

##### For Humans: What This Means
Creates the report.

##### Parameters
- `bool $success`
- `string $class`
- `array $properties`
- `array $methods`
- `array $errors`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Created by injection inspection logic.

##### Common Mistakes
Treating `properties`/`methods` as runtime proof; it’s a diagnostic summary.

### Method: hasErrors(): bool

#### Technical Explanation
Returns true when the error list is non-empty.

##### For Humans: What This Means
Tells you quickly if something went wrong.

##### Parameters
- None.

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
When you want a quick guard before printing errors.

##### Common Mistakes
Assuming `success` implies no errors; check both.

## Risks, Trade-offs & Recommended Practices
- **Practice: Prefer structured reporting**. Keep diagnostics in this report instead of log-only messages.

### For Humans: What This Means
Reports make debugging easier than hunting logs.

## Related Files & Folders
- `docs_md/Features/Core/DTO/index.md`: DTO overview.
- `docs_md/Features/Core/Contracts/ContainerInternalInterface.md`: Declares inspection API.

### For Humans: What This Means
This report is produced by internal inspection capabilities.
