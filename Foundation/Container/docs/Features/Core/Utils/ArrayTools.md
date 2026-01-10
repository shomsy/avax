# ArrayTools

## Quick Summary
- Provides small array helpers used across container configuration and metadata handling.
- Includes recursive merges, dot-notation get/set, flattening, and recursive filtering.
- Exists to keep array behavior consistent across the container.

### For Humans: What This Means (Summary)
It’s a toolbox for array manipulation so the container doesn’t repeat the same array tricks everywhere.

## Terminology (MANDATORY, EXPANSIVE)- **Dot notation**: Keys like `a.b.c` used to access nested arrays.
- **Flattening**: Turning nested arrays into a single-level map with dot keys.
- **Recursive merge**: Merging nested arrays without losing structure.

### For Humans: What This Means
It helps you read/write nested arrays easily and merge configs predictably.

## Think of It
Like a Swiss Army knife for arrays.

### For Humans: What This Means (Think)
It’s small, sharp tools.

## Story Example
The container merges configuration from defaults and user overrides. `mergeRecursive` combines them deeply while preserving structure.

### For Humans: What This Means (Story)
You can combine configs without writing custom merging code each time.

## For Dummies
- Use `mergeRecursive` to combine arrays.
- Use `getNested`/`setNested` with dot keys.
- Use `flatten` to turn nested arrays into dot-key maps.

### For Humans: What This Means (Dummies)
It gives you predictable array helpers.

## How It Works (Technical)
Static pure methods operating on arrays, sometimes by reference for setters.

### For Humans: What This Means (How)
No state, no surprises.

## Architecture Role
Utility dependency for configuration, metadata, and definition processing.

### For Humans: What This Means (Role)
It supports many parts of the container quietly.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: mergeRecursive(array ...$arrays): array

#### Technical Explanation (mergeRecursive)
Deep-merges arrays, merging nested arrays by key and overwriting scalars.

##### For Humans: What This Means (mergeRecursive)
Combines configs without losing nested structure.

##### Parameters (mergeRecursive)
- `array ...$arrays`: Arrays to merge.

##### Returns (mergeRecursive)
- `array`

##### Throws (mergeRecursive)
- None.

##### When to Use It (mergeRecursive)
Merging defaults with overrides.

##### Common Mistakes (mergeRecursive)
Expecting numeric keys to append; this overwrites by key.

### Method: isAssociative(array $array): bool

#### Technical Explanation (isAssociative)
Returns true when keys are not `0..n-1`.

##### For Humans: What This Means (isAssociative)
Tells if an array is a “map” vs a “list.”

##### Parameters (isAssociative)
- `array $array`

##### Returns (isAssociative)
- `bool`

##### Throws (isAssociative)
- None.

##### When to Use It (isAssociative)
When logic depends on list vs map.

##### Common Mistakes (isAssociative)
Assuming empty array is associative (it returns false).

### Method: getNested(array $array, string $key, mixed $default = null): mixed

#### Technical Explanation (getNested)
Traverses nested arrays using dot notation, returning default on missing path.

##### For Humans: What This Means (getNested)
Reads a nested value safely.

##### Parameters (getNested)
- `array $array`
- `string $key`
- `mixed $default`

##### Returns (getNested)
- `mixed`

##### Throws (getNested)
- None.

##### When to Use It (getNested)
Reading config.

##### Common Mistakes (getNested)
Using dot keys when array keys actually contain dots.

### Method: setNested(array &$array, string $key, mixed $value): void

#### Technical Explanation (setNested)
Creates intermediate arrays as needed and sets a nested value by dot key.

##### For Humans: What This Means (setNested)
Writes a nested value without manually creating levels.

##### Parameters (setNested)
- `array &$array`
- `string $key`
- `mixed $value`

##### Returns (setNested)
- `void`

##### Throws (setNested)
- None.

##### When to Use It (setNested)
Building nested config programmatically.

##### Common Mistakes (setNested)
Forgetting it mutates by reference.

### Method: flatten(array $array, string $prefix = ''): array

#### Technical Explanation (flatten)
Recursively flattens nested arrays into dot-keyed map.

##### For Humans: What This Means (flatten)
Turns nested config into a flat “address book.”

##### Parameters (flatten)
- `array $array`
- `string $prefix`

##### Returns (flatten)
- `array`

##### Throws (flatten)
- None.

##### When to Use It (flatten)
Search/index operations.

##### Common Mistakes (flatten)
Using it on very large arrays without considering cost.

### Method: filterRecursive(array $array, bool $removeEmpty = true): array

#### Technical Explanation (filterRecursive)
Recursively removes null values (and optionally empty values).

##### For Humans: What This Means (filterRecursive)
Cleans config arrays.

##### Parameters (filterRecursive)
- `array $array`
- `bool $removeEmpty`

##### Returns (filterRecursive)
- `array`

##### Throws (filterRecursive)
- None.

##### When to Use It (filterRecursive)
Before exporting config.

##### Common Mistakes (filterRecursive)
Removing `0` or `false` when `$removeEmpty` is true (because `empty()` treats them as empty).

### Method: intersectByValue(array $array1, array $array2): array

#### Technical Explanation (intersectByValue)
Intersect arrays by serialized values.

##### For Humans: What This Means (intersectByValue)
Finds common items even for complex values.

##### Parameters (intersectByValue)
- `array $array1`
- `array $array2`

##### Returns (intersectByValue)
- `array`

##### Throws (intersectByValue)
- None.

##### When to Use It (intersectByValue)
Comparing complex arrays.

##### Common Mistakes (intersectByValue)
Assuming returned array contains original values (it returns serialized intersection results).

## Risks, Trade-offs & Recommended Practices
- **Risk: empty() semantics**. `filterRecursive` can drop `0`/`false` when removeEmpty is true.
- **Practice: Use intentionally**. Know what “empty” means in PHP.

### For Humans: What This Means (Risks)
Be careful: PHP’s idea of “empty” is broader than you might expect.

## Related Files & Folders
- `docs_md/Config/Settings.md`: Dot-notation config patterns.

### For Humans: What This Means (Related)
If you like dot keys, Settings and ArrayTools are related ideas.
