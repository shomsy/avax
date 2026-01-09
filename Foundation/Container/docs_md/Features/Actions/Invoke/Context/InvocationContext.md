# InvocationContext

## Quick Summary
- Holds state for a single callable invocation: original target, normalized target, reflection, resolved arguments, and final result.
- Is immutable (`readonly`) and uses “with*” methods to create updated copies.
- Provides `getEffectiveTarget()` to choose normalized vs original target.

### For Humans: What This Means
It’s a safe, immutable record of an invocation as it moves through the invocation pipeline.

## Terminology
- **Original target**: What the caller gave you.
- **Normalized target**: A canonical callable format (e.g., `[object, method]`) after normalization.
- **Effective target**: The target used right now (normalized if available, otherwise original).
- **Reflection**: Reflection object describing the callable.
- **Resolved arguments**: The final argument list that will be passed.

### For Humans: What This Means
Original is what you asked for; normalized is the cleaned-up form; effective is which one to use; reflection and resolved args are what make invocation possible.

## Think of It
Like a package shipping label that gets updated as it moves through checkpoints: initial address (original target), standardized address (normalized target), inspection data (reflection), packing list (resolved args), delivery confirmation (result).

### For Humans: What This Means
It tracks the invocation from request to completion without mutating the same object.

## Story Example
A caller uses `Class@method`. The executor normalizes it into `[instance, method]`, stores reflection and resolved args via `withReflection` and `withResolvedArguments`, then invokes and may store the result with `withResult`.

### For Humans: What This Means
It’s the “paper trail” of how a call was prepared and executed.

## For Dummies
- Create it once with the original target.
- Each step returns a new copy with one more field filled.
- Use `getEffectiveTarget()` so later steps always see the best target.

Common misconceptions:
- “It mutates itself.” It doesn’t; it returns new copies.

### For Humans: What This Means
It’s immutable on purpose, so state can’t be accidentally corrupted.

## How It Works (Technical)
Readonly value object with fields and helper methods `withNormalizedTarget`, `withReflection`, `withResolvedArguments`, `withResult`, plus `getEffectiveTarget`.

### For Humans: What This Means
A simple immutable data holder with convenience copy methods.

## Architecture Role
Used by `InvocationExecutor` to keep invocation steps explicit and testable. It helps avoid passing many separate variables through the executor.

### For Humans: What This Means
It keeps invocation clean and predictable.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(mixed $originalTarget, mixed $normalizedTarget = null, ?object $reflection = null, ?array $resolvedArguments = null, mixed $result = null)

#### Technical Explanation
Creates a new invocation context for a target.

##### For Humans: What This Means
Starts the invocation record.

##### Parameters
- `mixed $originalTarget`: Caller-provided target.
- `mixed $normalizedTarget`: Normalized callable.
- `?object $reflection`: Reflection object.
- `?array $resolvedArguments`: Arguments list.
- `mixed $result`: Invocation result.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Created by `InvokeAction`.

##### Common Mistakes
Treating it as mutable.

### Method: withNormalizedTarget(mixed $normalizedTarget): self

#### Technical Explanation
Returns a copy with the normalized target set.

##### For Humans: What This Means
Stores the cleaned-up callable form.

##### Parameters
- `mixed $normalizedTarget`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
After turning `Class@method` into `[instance, method]`.

##### Common Mistakes
Not using `getEffectiveTarget` later.

### Method: withReflection(object $reflection): self

#### Technical Explanation
Returns a copy with reflection set.

##### For Humans: What This Means
Stores the callable inspection results.

##### Parameters
- `object $reflection`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
After creating a reflection object.

##### Common Mistakes
Storing non-callable reflections.

### Method: withResolvedArguments(array $resolvedArguments): self

#### Technical Explanation
Returns a copy with resolved arguments set.

##### For Humans: What This Means
Stores the argument list to use.

##### Parameters
- `array $resolvedArguments`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
After dependency resolution.

##### Common Mistakes
Passing arguments in wrong order.

### Method: withResult(mixed $result): self

#### Technical Explanation
Returns a copy with the final result set.

##### For Humans: What This Means
Records what the callable returned.

##### Parameters
- `mixed $result`

##### Returns
- `self`

##### Throws
- None.

##### When to Use It
After invocation.

##### Common Mistakes
Assuming result is always non-null.

### Method: getEffectiveTarget(): mixed

#### Technical Explanation
Returns normalized target if present, otherwise original.

##### For Humans: What This Means
Always gives you the best callable to use right now.

##### Parameters
- None.

##### Returns
- `mixed`

##### Throws
- None.

##### When to Use It
Before reflection/invocation.

##### Common Mistakes
Using original target after normalization.

## Risks, Trade-offs & Recommended Practices
- **Trade-off: Copies vs mutation**. Copying is safer but allocates new objects; acceptable for clarity.
- **Practice: Treat it as a record**. Use it to make debugging easier.

### For Humans: What This Means
It’s worth the small overhead because it prevents confusing bugs and keeps state explicit.

## Related Files & Folders
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Populates and uses this context.
- `docs_md/Features/Actions/Invoke/Core/InvokeAction.md`: Creates this context.

### For Humans: What This Means
Read the executor to see how the context is filled in, and the action to see where it starts.
