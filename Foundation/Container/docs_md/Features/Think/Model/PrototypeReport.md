# PrototypeReport

## Quick Summary
- This file generates JSON reports for `ServicePrototype` objects (single or bulk).
- It exists so you can inspect the container’s understanding of services in a tool-friendly format.
- It removes the complexity of “reading raw prototypes” by transforming them into structured summaries and statistics.

### For Humans: What This Means
It turns the container’s blueprints into a readable report you can print, log, or send to a CLI.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype report**: A structured representation of what a `ServicePrototype` contains.
  - In this file: arrays containing constructor info, properties, methods, dependencies, complexity.
  - Why it matters: you can debug DI without stepping through runtime.
- **Bulk report**: Aggregated analysis over multiple prototypes.
  - In this file: `generateBulkReport()` returns global statistics plus per-class reports.
  - Why it matters: helps you understand the system, not just one service.
- **Dependency extraction**: Finding all types referenced by injection points.
  - In this file: `extractDependencies()` collects types from constructor, properties, methods.
  - Why it matters: dependency lists enable graph tooling and cycle checks.
- **Complexity heuristic**: A simple score indicating how “heavy” a prototype is.
  - In this file: computed from number of injection points.
  - Why it matters: helps spot “god services” with too many dependencies.
- **JSON output**: A machine-friendly text format.
  - In this file: `toJson()` and `toCompactJson()`.
  - Why it matters: CLI tools and dashboards love JSON.

### For Humans: What This Means
This class helps you answer: “What does the container think this service needs?”

## Think of It
Think of it like a medical report for your service blueprint: it lists vital signs (dependencies), and gives a simple “complexity level”.

### For Humans: What This Means
It’s how you spot “this service is getting too complex” early.

## Story Example
You run a CLI inspect command. It uses `PrototypeReport` to dump a summary for all cached prototypes and prints a compact JSON for integration with other tools. When a service fails resolution, you can inspect the prototype and see exactly which dependency types are expected.

### For Humans: What This Means
You debug DI by reading a report, not by guessing.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Generate a report with `generateForPrototype()` or `generateBulkReport()`.
2. Convert it to JSON with `toJson()` or `toCompactJson()`.
3. Print a human summary with `toSummary()`.

## How It Works (Technical)
The report generator walks prototype structures:
- Methods are analyzed into parameter metadata.
- Properties are summarized by type and nullability.
- Dependencies are extracted into a unique list.
- Complexity is computed by counting injection points.
Output is plain arrays so it can be encoded to JSON easily.

### For Humans: What This Means
It’s a “prototype-to-array” converter plus a couple of convenience formatters.

## Architecture Role
- Why it lives in `Features/Think/Model`: it’s a “view” over Think-phase data.
- What depends on it: inspection tooling and CLI commands.
- What it depends on: prototype model classes.
- System-level reasoning: observability for DI needs structured exports.

### For Humans: What This Means
If you can’t inspect DI, you can’t trust DI.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: generateBulkReport(iterable $prototypes)

#### Technical Explanation
Generates per-prototype reports and aggregated statistics for an iterable of prototypes.

##### For Humans: What This Means
It gives you a “whole system” report.

##### Parameters
- `iterable<ServicePrototype> $prototypes`

##### Returns
- `array` containing `summary`, `statistics`, and `prototypes`.

##### Throws
- No explicit exceptions.

##### When to Use It
- CLI inspect and dashboards.

##### Common Mistakes
- Treating this as a live runtime truth; it reports what you feed into it.

### Method: generateForPrototype(ServicePrototype $prototype)

#### Technical Explanation
Generates a detailed report for one prototype.

##### For Humans: What This Means
It’s a “deep dive” for one service.

##### Parameters
- `ServicePrototype $prototype`

##### Returns
- `array` report for the prototype.

##### Throws
- No explicit exceptions.

##### When to Use It
- Debugging one service resolution.

##### Common Mistakes
- Confusing “prototype report” with “actual instance state”.

### Method: toJson(array $report)

#### Technical Explanation
Encodes the report with pretty-printed JSON.

##### For Humans: What This Means
Readable JSON for humans.

##### Parameters
- `array $report`

##### Returns
- `string`

##### Throws
- `json_encode` can return false; this method returns string (may be `false` coerced in older PHP), so callers should handle encoding failures if needed.

##### When to Use It
- Debug output and logs.

##### Common Mistakes
- Assuming the output is always valid JSON if input contains invalid UTF-8.

### Method: toCompactJson(array $report)

#### Technical Explanation
Encodes the report as compact JSON without pretty-printing.

##### For Humans: What This Means
Smaller JSON for machines.

##### Parameters
- `array $report`

##### Returns
- `string`

##### Throws
- Same caveat as `toJson()`.

##### When to Use It
- Telemetry pipelines and storage.

##### Common Mistakes
- Using compact JSON when you actually want readability.

### Method: toSummary(array $report)

#### Technical Explanation
Produces a human-readable plain-text summary for either a bulk report or a single report.

##### For Humans: What This Means
It prints the headline numbers you care about.

##### Parameters
- `array $report`

##### Returns
- `string`

##### Throws
- No explicit exceptions.

##### When to Use It
- CLI output.

##### Common Mistakes
- Passing an arbitrary array that doesn’t match report structure.

## Risks, Trade-offs & Recommended Practices
- Risk: “Complexity” is a heuristic, not truth.
  - Why it matters: it can misclassify some services.
  - Design stance: heuristics are hints, not decisions.
  - Recommended practice: treat complexity as a “where to look” signal.

### For Humans: What This Means
It’s a smoke alarm, not a full fire investigation.

## Related Files & Folders
- `docs_md/Features/Think/Model/ServicePrototype.md`: The data being reported.
- `docs_md/Observe/Inspect/index.md`: Where reports are typically used.

### For Humans: What This Means
PrototypeReport is for inspection tools: it’s how “blueprints become readable”.

