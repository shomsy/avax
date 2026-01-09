# StrTools

## Quick Summary
- Provides safe, predictable string utilities used in container hot paths.
- Focuses on class-name ↔ service-ID conversions, cache key sanitization, and namespace helpers.
- Exists to keep naming rules consistent across resolution, caching, and policy checks.

### For Humans: What This Means
It’s the container’s string toolbox: turning class names into IDs, making safe cache keys, and checking prefixes/suffixes.

## Terminology
- **Service ID**: String identifier used to look up a service.
- **Cache key**: Sanitized identifier safe to use in filenames/keys.
- **Namespace**: The part of a class name before the last `\\`.

### For Humans: What This Means
Service IDs are names the container uses; cache keys are safe versions; namespaces help with grouping and rules.

## Think of It
Like a label maker: it turns messy names into consistent labels.

### For Humans: What This Means
It keeps naming predictable.

## Story Example
A container needs a cache key for a class name with slashes and symbols. `toCacheKey` sanitizes it so the filesystem won’t choke.

### For Humans: What This Means
It avoids broken caches caused by unsafe characters.

## For Dummies
- Use `classToId` when you want a stable ID.
- Use `toCacheKey` when you need a safe filename/key.
- Use `extractNamespace`/`extractClassName` for class parsing.

### For Humans: What This Means
Pick the helper that matches the job.

## How It Works (Technical)
Pure static methods that transform strings, with careful rules documented in PHPDoc.

### For Humans: What This Means
No state, just predictable transformations.

## Architecture Role
Shared utility used across resolution, caching, and security policy checks.

### For Humans: What This Means
Many subsystems rely on these naming rules staying consistent.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: classToId(string $className): string

#### Technical Explanation
Converts a fully qualified class name into a dot-separated ID.

##### For Humans: What This Means
Turns `App\\Service\\Foo` into `App.Service.Foo`.

##### Parameters
- `string $className`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
When generating service IDs.

##### Common Mistakes
Using it when you actually need a filesystem-safe key.

### Method: idToClass(string $serviceId): string

#### Technical Explanation
Converts a dot-separated service ID back into a class name.

##### For Humans: What This Means
Turns `App.Service.Foo` back into `App\\Service\\Foo`.

##### Parameters
- `string $serviceId`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
When mapping IDs back to class names.

##### Common Mistakes
Assuming it validates class existence.

### Method: toCacheKey(string $input): string

#### Technical Explanation
Sanitizes a string into a safe cache key.

##### For Humans: What This Means
Turns unsafe characters into underscores so keys are safe.

##### Parameters
- `string $input`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
Building cache filenames/keys.

##### Common Mistakes
Assuming it’s reversible.

### Method: extractNamespace(string $className): string

#### Technical Explanation
Returns everything before the last namespace separator.

##### For Humans: What This Means
Gets the namespace part of a class.

##### Parameters
- `string $className`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
Policy checks, grouping.

##### Common Mistakes
Using with non-class strings.

### Method: extractClassName(string $className): string

#### Technical Explanation
Returns the short class name.

##### For Humans: What This Means
Gets the last segment.

##### Parameters
- `string $className`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
Logging, display.

##### Common Mistakes
Assuming it handles `::` method strings.

### Method: startsWithAny(string $haystack, array $prefixes): bool

#### Technical Explanation
Checks if haystack starts with any prefix.

##### For Humans: What This Means
Answers “does this start with one of these?”

##### Parameters
- `string $haystack`
- `array $prefixes`

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
Namespace allow/deny lists.

##### Common Mistakes
Forgetting it’s case-sensitive.

### Method: endsWithAny(string $haystack, array $suffixes): bool

#### Technical Explanation
Checks if haystack ends with any suffix.

##### For Humans: What This Means
Answers “does this end with one of these?”

##### Parameters
- `string $haystack`
- `array $suffixes`

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
Type classification by suffix.

##### Common Mistakes
Assuming it handles multibyte case folding.

## Risks, Trade-offs & Recommended Practices
- **Risk: Inconsistent naming rules**. If different parts of system invent different rules, bugs appear.
- **Practice: Centralize naming in this class**. Don’t reimplement ad-hoc conversions.

### For Humans: What This Means
If you need a naming rule, use this file—don’t invent your own.

## Related Files & Folders
- `docs_md/Features/Core/Utils/index.md`: Utils overview.
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Uses IDs and reflection keys.

### For Humans: What This Means
Naming utilities support invocation and other subsystems.
