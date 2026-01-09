# PropertyResolution

## Quick Summary
- Encapsulates the outcome of resolving an injectable property.
- Uses two states: `resolved=true` with a value, or `resolved=false` with no value.
- Prevents normal injection flow from relying on exceptions for expected “can’t resolve” cases.

### For Humans: What This Means
It’s a tiny yes/no envelope that says, “Should we inject this property?” and, if yes, “What value should we inject?”

## Terminology
- **Resolved**: Whether the injector found a value to set.
- **Value**: The value to inject when resolved.
- **Unresolved**: Meaning “don’t inject” (leave default/unchanged), not necessarily “error.”

### For Humans: What This Means
Resolved means “set it.” Unresolved means “leave it alone.” Unresolved isn’t always a failure.

## Think of It
Like a sticky note you attach to a part: “Install this part (value)” or “Skip this slot (unresolved).”

### For Humans: What This Means
It’s a clear instruction card for whether to inject a property.

## Story Example
A property has a default value. `PropertyInjector` decides not to override it and returns `PropertyResolution::unresolved()`. InjectDependencies sees unresolved and doesn’t set the property, leaving the default intact.

### For Humans: What This Means
Unresolved is how the injector politely says, “Don’t touch it.”

## For Dummies
- Use `PropertyResolution::resolved($value)` when you have a value.
- Use `PropertyResolution::unresolved()` when you don’t.
- The caller checks `$resolution->resolved` to decide whether to set the property.

### For Humans: What This Means
It’s just a clean, explicit return value instead of lots of conditionals or magic nulls.

## How It Works (Technical)
Immutable value object with readonly properties `resolved` and `value`. Uses static constructors `resolved()` and `unresolved()` to create instances.

### For Humans: What This Means
It’s a simple, safe data packet that can’t be modified after creation.

## Architecture Role
Used by property injection to communicate outcomes between `PropertyInjector` and `InjectDependencies` without throwing for non-error cases.

### For Humans: What This Means
It keeps injection logic readable: success/failure is returned as data.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(bool $resolved, mixed $value)

#### Technical Explanation
Private constructor used by named constructors to enforce valid state creation.

##### For Humans: What This Means
You don’t create it directly; you use the helper methods.

##### Parameters
- `bool $resolved`: Whether resolution succeeded.
- `mixed $value`: Value when resolved.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Internal only.

##### Common Mistakes
Trying to instantiate directly (it’s private).

### Method: resolved(mixed $value): self

#### Technical Explanation
Creates a resolved result carrying the value to inject.

##### For Humans: What This Means
Use this when you know what value should be injected.

##### Parameters
- `mixed $value`: Value to inject.

##### Returns
- `self`: Resolved outcome.

##### Throws
- None.

##### When to Use It
When overrides or container resolution produced a value.

##### Common Mistakes
Using it for defaults when you actually want to preserve the default.

### Method: unresolved(): self

#### Technical Explanation
Creates an unresolved result indicating no injection should happen.

##### For Humans: What This Means
Use this when you want to leave the property untouched.

##### Parameters
- None.

##### Returns
- `self`: Unresolved outcome.

##### Throws
- None.

##### When to Use It
When there’s no resolvable value or you want to preserve defaults.

##### Common Mistakes
Treating unresolved as an error; required failures should throw higher-level exceptions.

## Risks, Trade-offs & Recommended Practices
- **Risk: Misinterpreting unresolved**. Keep the semantics consistent: unresolved means “don’t inject,” not “inject null.”
- **Practice: Throw only for required failures**. Use unresolved for optional cases.

### For Humans: What This Means
Don’t confuse “leave it” with “set it to null.” Use exceptions only when it truly can’t work.

## Related Files & Folders
- `docs_md/Features/Actions/Inject/Resolvers/index.md`: Folder overview.
- `docs_md/Features/Actions/Inject/PropertyInjector.md`: Producer of this result.
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Consumer that applies it.

### For Humans: What This Means
See who creates it (PropertyInjector) and who uses it (InjectDependencies) to understand the full flow.
