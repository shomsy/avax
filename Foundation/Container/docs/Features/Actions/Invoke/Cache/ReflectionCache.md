# ReflectionCache

## Quick Summary

- Stores `ReflectionFunctionAbstract` objects keyed by a string.
- Used by `InvocationExecutor` to avoid repeated reflection creation.
- Lives in memory and resets with the PHP process.

### For Humans: What This Means (Summary)

It’s a small in-memory map so the invocation system doesn’t re-scan the same callable over and over.

## Terminology (MANDATORY, EXPANSIVE)- **ReflectionFunctionAbstract**: Base reflection type for functions and methods.

- **Cache key**: String that uniquely identifies a callable target.
- **In-memory cache**: Cache that exists only in the current process.

### For Humans: What This Means

It stores reflection objects by a unique name, and it disappears when the process ends.

## Think of It

Like writing down a phone number you already looked up so you don’t have to search the directory again.

### For Humans: What This Means (Think)

Once you have the reflection, you reuse it.

## Story Example

A route handler is called hundreds of times. Without caching, each invocation would create reflection. With
`ReflectionCache`, the executor creates reflection once and reuses it.

### For Humans: What This Means (Story)

Your app stays faster because it stops doing repeated introspection.

## For Dummies

- Call `get($key)` to try to fetch reflection.
- If missing, create reflection and call `set($key, $reflection)`.

### For Humans: What This Means (Dummies)

It’s just a simple map: get first, then set.

## How It Works (Technical)

Wraps an array keyed by strings. `get` returns cached reflection or null. `set` stores it.

### For Humans: What This Means (How)

A lightweight dictionary.

## Architecture Role

Supports invocation performance. Used only by invocation subsystem.

### For Humans: What This Means (Role)

It’s a performance helper for calling things.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: get(string $key): ?ReflectionFunctionAbstract

#### Technical Explanation (get)

Returns a cached reflection object for the given key if present.

##### For Humans: What This Means (get)

Gets the saved reflection if it exists.

##### Parameters (get)

- `string $key`: Cache key.

##### Returns (get)

- `?ReflectionFunctionAbstract`: Reflection or null.

##### Throws (get)

- None.

##### When to Use It (get)

Before creating new reflection.

##### Common Mistakes (get)

Using non-stable keys and losing cache hits.

### Method: set(string $key, ReflectionFunctionAbstract $reflection): void

#### Technical Explanation (set)

Stores a reflection object under the key.

##### For Humans: What This Means (set)

Saves reflection for later.

##### Parameters (set)

- `string $key`
- `ReflectionFunctionAbstract $reflection`

##### Returns (set)

- `void`

##### Throws (set)

- None.

##### When to Use It (set)

After creating reflection.

##### Common Mistakes (set)

Storing reflection under keys that collide between different callables.

## Risks, Trade-offs & Recommended Practices

- **Risk: Memory growth**. Many distinct callables mean many cached reflections; keep usage bounded.
- **Practice: Use stable keys**. Ensure the executor’s keying logic is deterministic.

### For Humans: What This Means (Risks)

It’s fast, but don’t let it grow without bounds if you have thousands of unique callables.

## Related Files & Folders

- `docs_md/Features/Actions/Invoke/Cache/index.md`: Cache folder overview.
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Main consumer.

### For Humans: What This Means (Related)

Read the executor to see how keys are built and how the cache is used.
