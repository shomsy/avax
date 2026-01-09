# CompileCommand

## Quick Summary
Compiles container definitions into optimized PHP files so production startup skips reflection and analysis. It exists to speed boot times and stabilize deployments by freezing container wiring into cacheable code.

### For Humans: What This Means
This command pre-builds the container so production can start quickly without doing heavy work on each run.

## Terminology
- **Compiled container**: Pre-generated PHP representation of the container configuration.
- **Cache directory**: Location where compiled output and temp files are written.
- **Force flag**: Option to overwrite existing compiled output even if present.
- **Prototype dumper**: Component that serializes definitions (`CompiledPrototypeDumper`).

### For Humans: What This Means
Think of the compiled container as a frozen snapshot of your wiring, stored in a cache directory. The force flag lets you rebuild it even if a file already exists.

## Think of It
Like preheating an oven: you pay the cost once so cooking (startup) is faster when serving requests.

### For Humans: What This Means
You do the slow prep step ahead of time so the app starts fast later.

## Story Example
During deployment, `compile` generates `/cache/container.php`. The app boots in half the time because it includes that file instead of recalculating dependencies. A later code change uses `--force` to regenerate the cache safely.

### For Humans: What This Means
You compile before shipping; when code changes, recompile with force to keep the cache accurate.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Choose an output file (default `/cache/container.php`).
- Optionally set `--cache-dir` and `--force`.
- Run the command; it exports definitions and writes the compiled file.
- Include the compiled file in production startup.

Common misconceptions: it does not run in every request—only when you execute it; force is required to overwrite existing output; compilation doesn’t replace validation or tests.

## How It Works (Technical)
Validates the output path, creates directories if needed, checks writability, and skips overwrite unless forced. Uses `CompiledPrototypeDumper` to serialize the definition store, writes the result, sets permissions, and reports size and success. Errors propagate with context.

### For Humans: What This Means
It checks it can write, builds the frozen container, saves it, and tells you if anything failed.

## Architecture Role
Lives under `Tools/Console` as a build-time utility. Depends on the container’s definition store and the prototype dumper; complements `ClearCacheCommand` and deployment scripts.

### For Humans: What This Means
It’s part of the build pipeline: compile after clearing caches to ship a ready-to-run container.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct

#### Technical Explanation
Captures the container and a default cache directory used when no override is provided.

##### For Humans: What This Means
Keeps references to the container to compile and where to write caches by default.

##### Parameters
- `Container $container`: Container whose definitions will be compiled.
- `string $defaultCacheDir`: Default directory for compilation artifacts.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Create the command before execution; adjust default cache dir to match environment layout.

##### Common Mistakes
Using a cache dir without write permissions.

### Method: execute

#### Technical Explanation
Resolves options (output file, force flag, cache dir), checks for existing output, ensures directories exist and are writable, invokes `CompiledPrototypeDumper` to produce code, writes the compiled file, adjusts permissions, and reports success or failure.

##### For Humans: What This Means
Runs the compilation: decides where to write, builds the frozen container, saves it, and prints the result.

##### Parameters
- `string|null $outputFile`: Target file path.
- `bool|null $force`: Whether to overwrite existing output.
- `string|null $cacheDir`: Alternate cache directory.

##### Returns
- `void`

##### Throws
- `Throwable`: For filesystem or compilation errors.

##### When to Use It
During CI/CD, before production deploys, or anytime you need a fresh compiled container.

##### Common Mistakes
Skipping `--force` when output exists; writing to unwritable locations; forgetting to deploy the new compiled file.

## Risks, Trade-offs & Recommended Practices
- **Risk: Out-of-date cache**. Compiling once and forgetting to rebuild after changes leads to stale wiring; always recompile after definition changes.
- **Risk: Permission issues**. Writing to protected paths fails silently if not checked; always validate writability.
- **Trade-off: Build time vs runtime**. Compilation adds a build step but dramatically speeds startup; accept the build cost for production.
- **Practice: Pair with cache clearing**. Clear then compile to avoid mixing old artifacts.
- **Practice: Track size**. Monitor compiled file size to catch unexpected growth.

### For Humans: What This Means
Compile whenever wiring changes, ensure the path is writable, and expect a small build delay in exchange for faster runtime.

## Related Files & Folders
- `docs_md/Tools/Console/ClearCacheCommand.md`: Clears compiled outputs before rebuilding.
- `docs_md/Tools/Console/index.md`: Overview of console utilities.
- `docs_md/Tools/Console/DiagnoseCommand.md`: Use diagnostics to confirm performance improvements.

### For Humans: What This Means
Clear caches, compile, then verify with diagnostics that startup improved.
