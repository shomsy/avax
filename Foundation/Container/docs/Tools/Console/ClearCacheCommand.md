# ClearCacheCommand

## Quick Summary

Clears container-related caches—prototypes, compiled definitions, and temporary artifacts—to keep resolutions accurate after code or configuration changes. It prevents stale data from causing incorrect resolutions.

### For Humans: What This Means (Summary)

When things feel “stuck” or out-of-date, this command wipes the container’s caches so it can rebuild fresh.

## Terminology (MANDATORY, EXPANSIVE)

- **Prototype cache**: Stored reflection/prototype analysis results used for faster resolution.
- **Compiled definitions**: Pre-generated PHP files that represent the container for fast startup.
- **Dry run**: Mode that shows what would be cleared without deleting anything.
- **Cache directory**: Filesystem location where container caches live.

### For Humans: What This Means (Terms)

Prototype cache is the container’s memory of how to build services; compiled definitions are its prebuilt plans. Dry run is a safe preview; cache directory is where the files are.

## Think of It

Like hitting “refresh” on a browser cache when a site doesn’t update—this does that for container internals.

### For Humans: What This Means (Think)

If the container keeps using old info, this command forces it to reload fresh data.

## Story Example

After changing service definitions, tests still use old dependencies. Running `clear-cache` removes prototype and compiled caches, and the next run picks up new wiring. Issues disappear.

### For Humans: What This Means (Story)

When updates don’t show up, clear the cache and the container will rebuild with your latest changes.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Run the command (optionally with `--prototypes-only`, `--cache-dir`, or `--dry-run`).
- It deletes prototype caches and compiled files (unless limited).
- On next use, the container rebuilds necessary caches.

Common misconceptions: it doesn’t remove application data; it’s safe but may slow the first run after clearing; production uses should plan recompilation afterward.

## How It Works (Technical)

Accepts flags for prototype-only, custom cache directory, and dry-run. Resolves the cache path from the container’s prototype cache when available, otherwise clears in-memory cache. Removes known compiled files, reports cleared items or errors, and prints guidance about post-clear performance.

### For Humans: What This Means (How)

It finds where caches live, deletes them (or shows what would be deleted), and tells you what happened.

## Architecture Role

Placed under `Tools/Console` because it’s an operational maintenance command. Depends on the container, prototype cache implementations, and filesystem access; used alongside compile to manage cache lifecycle.

### For Humans: What This Means (Role)

It’s part of the maintenance toolkit: use it to clean, then recompile or rerun to rebuild caches.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct

#### Technical Explanation (Construct)

Stores the container reference and a default cache directory path for use during execution.

##### For Humans: What This Means (Construct)

Keeps track of which container to clean and where caches live by default.

##### Parameters (__construct)
- `Container $container`: Container whose caches will be cleared.
- `string $defaultCacheDir`: Fallback cache directory path.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
Instantiate before running the command; default cache dir can be overridden later.

##### Common Mistakes (__construct)
Forgetting to align default cache dir with deployment layout.

### Method: execute

#### Technical Explanation (Execute)

Parses options (prototype-only, cache-dir, dry-run), locates prototype cache, clears files or simulates deletion, removes compiled definitions, and reports successes or errors.

##### For Humans: What This Means (Execute)

Runs the cleanup, optionally just showing what would be removed, and tells you the results.

##### Parameters (execute)
- `bool|null $prototypesOnly`: Limit clearing to prototype cache.
- `string|null $cacheDir`: Override cache directory.
- `bool $dryRun`: Preview without deleting.

##### Returns (execute)
- `void`

##### Throws (execute)
- `Throwable`: Propagates errors from filesystem operations or container calls.

##### When to Use It (execute)
After code changes, before tests, during debugging, or before recompiling container caches.

##### Common Mistakes (execute)
Running in dry-run and expecting caches gone; forgetting to recompile after clearing compiled definitions.

### Method: clearDirectory

#### Technical Explanation (Clear Directory)

Iterates files in a directory and unlinks each file without deleting the directory itself.

##### For Humans: What This Means (Clear Directory)

It deletes files inside a cache folder but leaves the folder in place.

##### Parameters (clearDirectory)
- `string $directory`: Directory whose files should be removed.

##### Returns (clearDirectory)
- `void`

##### Throws (clearDirectory)
- None (silently skips missing directories).

##### When to Use It (clearDirectory)
Internal helper when actually deleting prototype cache contents.

##### Common Mistakes (clearDirectory)
Expecting it to remove nested directories; it only deletes files at the first level.

## Risks, Trade-offs & Recommended Practices

- **Risk: Missing rebuild**. Clearing compiled definitions without recompiling can slow first requests; plan recompilation.
- **Risk: Wrong path**. Clearing the wrong directory could delete unrelated files; double-check `--cache-dir`.
- **Trade-off: Safety vs completeness**. Dry run is safe but doesn’t clear anything; use it before real runs in production.
- **Practice: Pair with compile**. Clear then compile to warm caches before traffic.
- **Practice: Use prototype-only in dev**. Faster iterations when you only need prototype refresh.

### For Humans: What This Means (Risks)

Clear carefully: preview first, ensure you rebuild when needed, and choose prototype-only when you just need a quick refresh.

## Related Files & Folders

- `docs_md/Tools/Console/CompileCommand.md`: Generates the compiled definitions you may clear.
- `docs_md/Tools/Console/DiagnoseCommand.md`: Use diagnostics before/after clearing to measure impact.
- `docs_md/Tools/Console/index.md`: Overview of all console tools.

### For Humans: What This Means (Related)

Clean caches, recompile if needed, and check diagnostics to confirm everything is healthy.
