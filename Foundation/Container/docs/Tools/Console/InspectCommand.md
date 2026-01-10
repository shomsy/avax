# InspectCommand

## Quick Summary

Inspects a single service to show its registration state, caching status, scope, tags, and full dependency prototype. It exists to debug service wiring without scanning the entire container.

### For Humans: What This Means (Summary)

You point it at one service and get a detailed report on how it’s registered and built.

## Terminology (MANDATORY, EXPANSIVE)

- **Service prototype**: The blueprint describing how to construct a service, including constructor, property, and method injections.
- **Scope**: Service lifetime (singleton, scoped, transient) that determines how instances are reused.
- **Tags**: Labels that group services for batch operations.
- **Inspector**: The diagnostics component that collects service details.

### For Humans: What This Means (Terms)

Prototype is the build plan, scope is how long it lives, tags are labels, inspector is the tool pulling this info.

## Think of It

It’s like an X-ray for one service—showing its bones (dependencies), labels (tags), and vital stats (scope, cache).

### For Humans: What This Means (Think)

You see inside one service without tearing apart the whole system.

## Story Example

A controller fails because a dependency is missing. Running `inspect App\Controller\UserController` shows the prototype has an undefined service. Fixing the registration and re-running confirms the prototype now resolves cleanly.

### For Humans: What This Means (Story)

When something breaks, inspect reveals what’s missing so you can fix and verify quickly.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

- Run the command with a service ID or class.
- Read whether it’s defined, cached, and its scope.
- Check tags for grouping.
- Review the printed prototype to see dependencies.

Common misconceptions: it doesn’t register services; it reads existing state. It relies on container diagnostics, so misconfigured containers may return partial data.

## How It Works (Technical)

Calls `diagnostics()->inspect()` for the given identifier to get status and metadata, then renders the prototype via `CliPrototypeDumper` using the reflection-based prototype factory. Errors during prototype generation are caught and reported without aborting the whole command.

### For Humans: What This Means (How)

It asks the container for info on one service and prints the blueprint; if blueprint generation fails, you still get status info.

## Architecture Role

Sits in `Tools/Console` as a focused debugging command. Depends on container diagnostics and prototype factories; complements broader health tools like `DiagnoseCommand`.

### For Humans: What This Means (Role)

It’s the targeted debug tool in the console toolkit; use it for single-service deep dives.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct

#### Technical Explanation (Construct)

Stores the container instance to inspect services and build prototypes.

##### For Humans: What This Means (Construct)

Keeps a handle to the container you want to query.

##### Parameters (__construct)
- `Container $container`: Container instance for inspection.

##### Returns (__construct)
- `void`

##### Throws (__construct)
- None.

##### When to Use It (__construct)
Create the command with the container before executing inspections.

##### Common Mistakes (__construct)
Passing a container without registered definitions yields empty results.

### Method: execute

#### Technical Explanation (Execute)

Prints a header, retrieves diagnostic data for the target service, outputs definition/cache/scope/tag info, attempts to build and dump the prototype, and reports any prototype errors.

##### For Humans: What This Means (Execute)

Runs the inspection, shows the service’s status, and prints how it would be constructed.

##### Parameters (execute)
- `string $abstract`: Service identifier or class to inspect.

##### Returns (execute)
- `void`

##### Throws (execute)
- Exceptions from diagnostics or prototype analysis propagate unless caught internally.

##### When to Use It (execute)
When debugging a specific service’s registration, scope, or dependency issues.

##### Common Mistakes (execute)
Inspecting the wrong identifier; forgetting that prototype generation may need autoloadable classes.

## Risks, Trade-offs & Recommended Practices

- **Risk: Sensitive output**. Reveals class names and configuration; avoid exposing in unsecured environments.
- **Trade-off: Narrow focus**. Only one service at a time; use broader diagnostics for system-wide checks.
- **Practice: Pair with fixes**. After adjusting definitions, rerun to confirm changes.
- **Practice: Capture output**. Save reports during incidents for later analysis.

### For Humans: What This Means (Risks)

Use it when you need detail on one service, keep the output private, and rerun after changes to verify fixes.

## Related Files & Folders

- `docs_md/Tools/Console/DiagnoseCommand.md`: System-wide view when you need context beyond one service.
- `docs_md/Tools/Console/index.md`: Overview of console tools.
- `docs_md/Tools/Console/ContainerInspectCommand.md`: Broader inspection with performance and validation sections.

### For Humans: What This Means (Related)

Start here for one service; switch to the broader inspection or diagnostics when you need more context.
