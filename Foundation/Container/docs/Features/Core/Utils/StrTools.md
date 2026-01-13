# StrTools

## Quick Summary

- Provides safe, predictable string utilities used in container hot paths.
- Focuses on class-name ↔ service-ID conversions, cache key sanitization, and namespace helpers.
- Exists to keep naming rules consistent across resolution, caching, and policy checks.

### For Humans: What This Means (Summary)

It’s the container’s string toolbox: turning class names into IDs, making safe cache keys, and checking
prefixes/suffixes.

## Terminology (MANDATORY, EXPANSIVE)- **Service ID**: String identifier used to look up a service.

- **Cache key**: Sanitized identifier safe to use in filenames/keys.
- **Namespace**: The part of a class name before the last `\\`.

### For Humans: What This Means

Service IDs are names the container uses; cache keys are safe versions; namespaces help with grouping and rules.

## Think of It

Like a label maker: it turns messy names into consistent labels.

### For Humans: What This Means (Think)

It keeps naming predictable.

## Story Example

A container needs a cache key for a class name with slashes and symbols. `toCacheKey` sanitizes it so the filesystem
won’t choke.

### For Humans: What This Means (Story)

It avoids broken caches caused by unsafe characters.

## For Dummies

- Use `classToId` when you want a stable ID.
- Use `toCacheKey` when you need a safe filename/key.
- Use `extractNamespace`/`extractClassName` for class parsing.

### For Humans: What This Means (Dummies)

Pick the helper that matches the job.

## How It Works (Technical)

Pure static methods that transform strings, with careful rules documented in PHPDoc.

### For Humans: What This Means (How)

No state, just predictable transformations.

## Architecture Role

Shared utility used across resolution, caching, and security policy checks.

### For Humans: What This Means (Role)

Many subsystems rely on these naming rules staying consistent.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: classToId(string $className): string

#### Technical Explanation (classToId)

Converts a fully qualified class name into a dot-separated ID.

##### For Humans: What This Means (classToId)

Turns `App\\Service\\Foo` into `App.Service.Foo`.

##### Parameters (classToId)

- `string $className`

##### Returns (classToId)

- `string`

##### Throws (classToId)

- None.

##### When to Use It (classToId)

When generating service IDs.

##### Common Mistakes (classToId)

Using it when you actually need a filesystem-safe key.

### Method: idToClass(string $serviceId): string

#### Technical Explanation (idToClass)

Converts a dot-separated service ID back into a class name.

##### For Humans: What This Means (idToClass)

Turns `App.Service.Foo` back into `App\\Service\\Foo`.

##### Parameters (idToClass)

- `string $serviceId`

##### Returns (idToClass)

- `string`

##### Throws (idToClass)

- None.

##### When to Use It (idToClass)

When mapping IDs back to class names.

##### Common Mistakes (idToClass)

Assuming it validates class existence.

### Method: toCacheKey(string $input): string

#### Technical Explanation (toCacheKey)

Sanitizes a string into a safe cache key.

##### For Humans: What This Means (toCacheKey)

Turns unsafe characters into underscores so keys are safe.

##### Parameters (toCacheKey)

- `string $input`

##### Returns (toCacheKey)

- `string`

##### Throws (toCacheKey)

- None.

##### When to Use It (toCacheKey)

Building cache filenames/keys.

##### Common Mistakes (toCacheKey)

Assuming it’s reversible.

### Method: extractNamespace(string $className): string

#### Technical Explanation (extractNamespace)

Returns everything before the last namespace separator.

##### For Humans: What This Means (extractNamespace)

Gets the namespace part of a class.

##### Parameters (extractNamespace)

- `string $className`

##### Returns (extractNamespace)

- `string`

##### Throws (extractNamespace)

- None.

##### When to Use It (extractNamespace)

Policy checks, grouping.

##### Common Mistakes (extractNamespace)

Using with non-class strings.

### Method: extractClassName(string $className): string

#### Technical Explanation (extractClassName)

Returns the short class name.

##### For Humans: What This Means (extractClassName)

Gets the last segment.

##### Parameters (extractClassName)

- `string $className`

##### Returns (extractClassName)

- `string`

##### Throws (extractClassName)

- None.

##### When to Use It (extractClassName)

Logging, display.

##### Common Mistakes (extractClassName)

Assuming it handles `::` method strings.

### Method: startsWithAny(string $haystack, array $prefixes): bool

#### Technical Explanation (startsWithAny)

Checks if haystack starts with any prefix.

##### For Humans: What This Means (startsWithAny)

Answers “does this start with one of these?”

##### Parameters (startsWithAny)

- `string $haystack`
- `array $prefixes`

##### Returns (startsWithAny)

- `bool`

##### Throws (startsWithAny)

- None.

##### When to Use It (startsWithAny)

Namespace allow/deny lists.

##### Common Mistakes (startsWithAny)

Forgetting it’s case-sensitive.

### Method: endsWithAny(string $haystack, array $suffixes): bool

#### Technical Explanation (endsWithAny)

Checks if haystack ends with any suffix.

##### For Humans: What This Means (endsWithAny)

Answers “does this end with one of these?”

##### Parameters (endsWithAny)

- `string $haystack`
- `array $suffixes`

##### Returns (endsWithAny)

- `bool`

##### Throws (endsWithAny)

- None.

##### When to Use It (endsWithAny)

Type classification by suffix.

##### Common Mistakes (endsWithAny)

Assuming it handles multibyte case folding.

## Risks, Trade-offs & Recommended Practices

- **Risk: Inconsistent naming rules**. If different parts of system invent different rules, bugs appear.
- **Practice: Centralize naming in this class**. Don’t reimplement ad-hoc conversions.

### For Humans: What This Means (Risks)

If you need a naming rule, use this file—don’t invent your own.

## Related Files & Folders

- `docs_md/Features/Core/Utils/index.md`: Utils overview.
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Uses IDs and reflection keys.

### For Humans: What This Means (Related)

Naming utilities support invocation and other subsystems.
