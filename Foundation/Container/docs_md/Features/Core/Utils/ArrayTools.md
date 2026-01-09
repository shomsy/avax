# ArrayTools

## Quick Summary
- Provides small array helpers used across container configuration and metadata handling.
- Includes recursive merges, dot-notation get/set, flattening, and recursive filtering.
- Exists to keep array behavior consistent across the container.

### For Humans: What This Means
It’s a toolbox for array manipulation so the container doesn’t repeat the same array tricks everywhere.

## Terminology
- **Dot notation**: Keys like `a.b.c` used to access nested arrays.
- **Flattening**: Turning nested arrays into a single-level map with dot keys.
- **Recursive merge**: Merging nested arrays without losing structure.

### For Humans: What This Means
It helps you read/write nested arrays easily and merge configs predictably.

## Think of It
Like a Swiss Army knife for arrays.

### For Humans: What This Means
It’s small, sharp tools.

## Story Example
The container merges configuration from defaults and user overrides. `mergeRecursive` combines them deeply while preserving structure.

### For Humans: What This Means
You can combine configs without writing custom merging code each time.

## For Dummies
- Use `mergeRecursive` to combine arrays.
- Use `getNested`/`setNested` with dot keys.
- Use `flatten` to turn nested arrays into dot-key maps.

### For Humans: What This Means
It gives you predictable array helpers.

## How It Works (Technical)
Static pure methods operating on arrays, sometimes by reference for setters.

### For Humans: What This Means
No state, no surprises.

## Architecture Role
Utility dependency for configuration, metadata, and definition processing.

### For Humans: What This Means
It supports many parts of the container quietly.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: mergeRecursive(array ...$arrays): array

#### Technical Explanation
Deep-merges arrays, merging nested arrays by key and overwriting scalars.

##### For Humans: What This Means
Combines configs without losing nested structure.

##### Parameters
- `array ...$arrays`: Arrays to merge.

##### Returns
- `array`

##### Throws
- None.

##### When to Use It
Merging defaults with overrides.

##### Common Mistakes
Expecting numeric keys to append; this overwrites by key.

### Method: isAssociative(array $array): bool

#### Technical Explanation
Returns true when keys are not `0..n-1`.

##### For Humans: What This Means
Tells if an array is a “map” vs a “list.”

##### Parameters
- `array $array`

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
When logic depends on list vs map.

##### Common Mistakes
Assuming empty array is associative (it returns false).

### Method: getNested(array $array, string $key, mixed $default = null): mixed

#### Technical Explanation
Traverses nested arrays using dot notation, returning default on missing path.

##### For Humans: What This Means
Reads a nested value safely.

##### Parameters
- `array $array`
- `string $key`
- `mixed $default`

##### Returns
- `mixed`

##### Throws
- None.

##### When to Use It
Reading config.

##### Common Mistakes
Using dot keys when array keys actually contain dots.

### Method: setNested(array &$array, string $key, mixed $value): void

#### Technical Explanation
Creates intermediate arrays as needed and sets a nested value by dot key.

##### For Humans: What This Means
Writes a nested value without manually creating levels.

##### Parameters
- `array &$array`
- `string $key`
- `mixed $value`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Building nested config programmatically.

##### Common Mistakes
Forgetting it mutates by reference.

### Method: flatten(array $array, string $prefix = ''): array

#### Technical Explanation
Recursively flattens nested arrays into dot-keyed map.

##### For Humans: What This Means
Turns nested config into a flat “address book.”

##### Parameters
- `array $array`
- `string $prefix`

##### Returns
- `array`

##### Throws
- None.

##### When to Use It
Search/index operations.

##### Common Mistakes
Using it on very large arrays without considering cost.

### Method: filterRecursive(array $array, bool $removeEmpty = true): array

#### Technical Explanation
Recursively removes null values (and optionally empty values).

##### For Humans: What This Means
Cleans config arrays.

##### Parameters
- `array $array`
- `bool $removeEmpty`

##### Returns
- `array`

##### Throws
- None.

##### When to Use It
Before exporting config.

##### Common Mistakes
Removing `0` or `false` when `$removeEmpty` is true (because `empty()` treats them as empty).

### Method: intersectByValue(array $array1, array $array2): array

#### Technical Explanation
Intersect arrays by serialized values.

##### For Humans: What This Means
Finds common items even for complex values.

##### Parameters
- `array $array1`
- `array $array2`

##### Returns
- `array`

##### Throws
- None.

##### When to Use It
Comparing complex arrays.

##### Common Mistakes
Assuming returned array contains original values (it returns serialized intersection results).

## Risks, Trade-offs & Recommended Practices
- **Risk: empty() semantics**. `filterRecursive` can drop `0`/`false` when removeEmpty is true.
- **Practice: Use intentionally**. Know what “empty” means in PHP.

### For Humans: What This Means
Be careful: PHP’s idea of “empty” is broader than you might expect.

## Related Files & Folders
- `docs_md/Config/Settings.md`: Dot-notation config patterns.

### For Humans: What This Means
If you like dot keys, Settings and ArrayTools are related ideas.
