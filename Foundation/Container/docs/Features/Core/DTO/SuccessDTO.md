# SuccessDTO

## Quick Summary
- Represents a structured success outcome.
- Carries a message and an optional payload.
- Exists to make “success as data” symmetrical with `ErrorDTO`.

### For Humans: What This Means (Summary)
It’s a simple success envelope: “it worked” plus optional details.

## Terminology (MANDATORY, EXPANSIVE)- **Payload**: Optional value carrying extra result data.

### For Humans: What This Means
Payload is the “extra info” that comes with success.

## Think of It
Like a receipt: it says what happened and optionally includes details.

### For Humans: What This Means (Think)
It standardizes success reporting.

## Story Example
A validation operation completes and returns `SuccessDTO('All services valid')`. A CLI tool prints the message.

### For Humans: What This Means (Story)
You can report success in a consistent, structured way.

## For Dummies
- Use when you want success results without throwing or returning raw arrays.

### For Humans: What This Means (Dummies)
It’s a clean “OK” object.

## How It Works (Technical)
Readonly DTO with two fields.

### For Humans: What This Means (How)
It’s simple and immutable.

## Architecture Role
Used wherever operations want to return structured “success” in the container ecosystem.

### For Humans: What This Means (Role)
It’s the success counterpart to error DTOs.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(string $message, mixed $payload = null)

#### Technical Explanation (__construct)
Initializes the DTO.

##### For Humans: What This Means (__construct)
Creates the success envelope.

##### Parameters (__construct)
- `string $message`
- `mixed $payload`

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
When reporting successful outcomes from validation/inspection.

##### Common Mistakes (__construct)
Using payload for huge objects; keep it lightweight.

## Risks, Trade-offs & Recommended Practices
- **Practice: Keep payload small**. Large payloads become hard to log and inspect.

### For Humans: What This Means (Risks)
Don’t stuff everything inside the payload.

## Related Files & Folders
- `docs_md/Features/Core/DTO/ErrorDTO.md`: Error counterpart.

### For Humans: What This Means (Related)
Together they form a consistent success/error pair.
