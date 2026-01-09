# FilePrototypeCache

## Quick Summary
- This file implements `PrototypeCache` by storing each `ServicePrototype` as a PHP file on disk.
- It exists to provide a fast, dependency-free caching option for production.
- It removes the complexity of custom serialization by using `var_export()` + `require`.

### For Humans: What This Means
It saves blueprints as little PHP files that can be loaded instantly.

## Terminology (MANDATORY, EXPANSIVE)
- **Atomic write**: Writing in a way that avoids partially-written files being used.
  - In this file: it writes to a temp file and then renames it.
  - Why it matters: prevents corrupted cache under concurrent writes.
- **var_export() hydration**: Exporting an object to PHP code and later `require`-ing it.
  - In this file: `set()` writes `return <exported prototype>;`.
  - Why it matters: it’s fast and avoids custom serializers.
- **Cache directory**: The folder where cache files live.
  - In this file: validated/created in the constructor.
  - Why it matters: permissions and path correctness decide whether caching works.
- **Cache key to filename mapping**: Turning a class name into a safe filename.
  - In this file: replaces `\\` and `/` with `_`.
  - Why it matters: makes file names stable and filesystem-safe.

### For Humans: What This Means
It’s like saving each blueprint in its own labeled folder file, and making sure you never leave half-written notes.

## Think of It
Think of it like a photo cache on your phone: each photo (prototype) becomes its own file, and the cache folder is the album.

### For Humans: What This Means
It’s simple, portable, and you can inspect it with normal filesystem tools.

## Story Example
On the first run, the container analyzes 200 classes and caches their prototypes. The next run, it loads prototypes straight from disk without reflection. If a cache file is broken, the cache gracefully returns null and the container can re-analyze.

### For Humans: What This Means
It turns “expensive thinking” into “quick remembering”.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Create the cache with a directory path.
2. Call `set($class, $prototype)` to save.
3. Call `get($class)` to load.
4. Call `clear()` if you changed code and want a reset.

## How It Works (Technical)
`get()` computes a path, checks for existence, then `require`s the file. It returns `null` on errors. `set()` creates a temp file with a random suffix, writes the exported prototype, then renames to the final path. Other methods are filesystem utilities around that directory.

### For Humans: What This Means
It’s careful file writing plus simple file reading.

## Architecture Role
- Why it lives in this folder: it’s a Think-phase caching backend.
- What depends on it: prototype factories and runtime that want cached prototypes.
- What it depends on: filesystem access and `ServicePrototype` exportability.
- System-level reasoning: it’s the default “no extra infrastructure required” cache.

### For Humans: What This Means
You can get caching benefits without running Redis or installing extensions.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Ensures the cache directory exists (or attempts to create it).

##### For Humans: What This Means
It makes sure there’s a place to store the files.

##### Parameters
- `string $directory`: Target cache directory.

##### Returns
- Returns nothing.

##### Throws
- `RuntimeException`: If the directory can’t be created.

##### When to Use It
- When bootstrapping the container in an environment where file cache is allowed.

##### Common Mistakes
- Using a directory without write permissions.

### Method: get(…)

#### Technical Explanation
Loads and returns a cached `ServicePrototype` by requiring a generated PHP file.

##### For Humans: What This Means
It reads the saved blueprint if it exists.

##### Parameters
- `string $class`

##### Returns
- `ServicePrototype|null`

##### Throws
- It catches internal errors and returns `null` for safety.

##### When to Use It
- Before reflection analysis.

##### Common Mistakes
- Assuming a missing file is an error; it just means “not cached yet”.

### Method: set(…)

#### Technical Explanation
Writes the prototype to disk using a temp file and atomic rename.

##### For Humans: What This Means
It saves a blueprint safely, even if multiple processes are running.

##### Parameters
- `string $class`
- `ServicePrototype $prototype`

##### Returns
- Returns nothing.

##### Throws
- `RuntimeException`: If writing or rename fails.
- `RandomException`: If random bytes generation fails (PHP runtime dependent).

##### When to Use It
- After a prototype is analyzed and validated.

##### Common Mistakes
- Storing prototypes that can’t be `var_export()`-ed safely.

### Method: delete(…)

#### Technical Explanation
Deletes the cache file for a given class key.

##### For Humans: What This Means
Remove one saved blueprint.

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- No explicit exceptions (uses suppression).

##### When to Use It
- When a single prototype is known stale.

##### Common Mistakes
- Forgetting to delete after renaming classes.

### Method: clear(…)

#### Technical Explanation
Deletes all `.php` cache files in the cache directory.

##### For Humans: What This Means
Wipe the whole blueprint folder.

##### Parameters
- None.

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions (uses suppression).

##### When to Use It
- On deploy or major refactor.

##### Common Mistakes
- Clearing frequently in production, losing performance benefits.

### Method: getCachePath(…)

#### Technical Explanation
Returns the configured cache directory path.

##### For Humans: What This Means
It tells you where files are stored.

##### Parameters
- None.

##### Returns
- `string`

##### Throws
- No explicit exceptions.

##### When to Use It
- Diagnostics and debugging.

##### Common Mistakes
- Treating it as a writable path without verifying permissions.

### Method: count(…)

#### Technical Explanation
Counts cache files to report cache size.

##### For Humans: What This Means
How many blueprints are saved?

##### Parameters
- None.

##### Returns
- `int`

##### Throws
- No explicit exceptions.

##### When to Use It
- Monitoring.

##### Common Mistakes
- Interpreting it as “number of registered services”; it’s only cached prototypes.

### Method: prototypeExists(…)

#### Technical Explanation
Delegates to `has()` as a fast existence check.

##### For Humans: What This Means
“Is there a file for this class?”

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Batch checks without loading.

##### Common Mistakes
- Assuming existence implies it can be loaded successfully.

### Method: has(…)

#### Technical Explanation
Checks if the cache file exists for a given key.

##### For Humans: What This Means
Quick “is it cached?” check.

##### Parameters
- `string $class`

##### Returns
- `bool`

##### Throws
- No explicit exceptions.

##### When to Use It
- Before calling `get()` if you want to avoid I/O in some cases.

##### Common Mistakes
- Duplicating checks unnecessarily (calling `has()` and then `get()` anyway).

## Risks, Trade-offs & Recommended Practices
- Risk: Filesystem I/O can be slower than memory caches.
  - Why it matters: very high-throughput systems may prefer APCu/Redis.
  - Design stance: file cache is the default baseline; optimize if needed.
  - Recommended practice: keep cache directory on fast storage; avoid network FS if possible.

### For Humans: What This Means
This is a great default. If you’re running at massive scale, you might want a faster storage engine.

## Related Files & Folders
- `docs_md/Features/Think/Cache/PrototypeCache.md`: Interface implemented here.
- `docs_md/Features/Think/Model/ServicePrototype.md`: The cached blueprint.

### For Humans: What This Means
To understand what “a cached file contains”, look at the prototype model.

