# ContainerInspectCommand

## Quick Summary

Provides a comprehensive inspection of container health, performance, services, dependencies, caches, and validation in
one CLI command. It exists to give operators a full diagnostic snapshot with optional focused sections and
machine-readable outputs.

### For Humans: What This Means (Summary)

Run this command to get an all-in-one report about how the container is doing—speed, health, services, dependencies,
caches, and validation results.

## Terminology (MANDATORY, EXPANSIVE)

- **Health check**: Overall status derived from cache backend health and service availability.
- **Performance analysis**: Metrics like resolution counts, average duration, and error rate from
  `EnhancedMetricsCollector`.
- **Dependency analysis**: Stats and cycle detection over service dependencies from `ServiceDefinitionRepository` and
  `ServiceDiscovery`.
- **Cache statistics**: Information about cache backends and prototype cache hit/miss data.
- **Validation summary**: Counts of valid/invalid services and associated errors from `ServiceValidator`.

### For Humans: What This Means (Terms)

These are the sections you can ask for: is it healthy, how fast is it, how services depend on each other, how caches
behave, and whether services pass validation.

## Think of It

Like a full physical exam for the container: vitals, bloodwork, imaging, and specialist checks all in one visit.

### For Humans: What This Means (Think)

It checks everything at once—overall health, performance, connections, and any warnings.

## Story Example

During an incident, `container:inspect --performance --dependencies --validate` shows a spike in resolution time and
circular dependencies causing failures. After fixing definitions, rerunning reports healthy status and no cycles.

### For Humans: What This Means (Story)

When things break, this command pinpoints performance slowdowns and wiring issues so you can fix them and verify the
fixes.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

- Run with no flags to get everything, or specify sections (`--performance`, `--dependencies`, `--cache`, `--validate`,
  `--services`, `--check-health`).
- Choose output format (`table`, `json`, `yaml`), and add `-v` for verbose detail.
- Read each section for status, metrics, and recommendations.

Common misconceptions: it doesn’t modify the container; it may surface sensitive metadata; verbosity can increase output
size but not change behavior.

## How It Works (Technical)

Configures options for sections and formats. Execution builds a `SymfonyStyle` instance, determines selected sections,
and calls helper methods to render each. Health and cache data come from `CacheManagerIntegration`; performance from
`EnhancedMetricsCollector`; service stats and dependency analysis from `ServiceDefinitionRepository`; validation from
`ServiceValidator`. Errors are reported with optional traces when verbose.

### For Humans: What This Means (How)

It gathers data from the container’s metrics, cache, service store, and validator, then prints it in the format you
choose.

## Architecture Role

Belongs in `Tools/Console` as the primary operational insight command. It depends on multiple subsystems (metrics,
definitions, cache, validation) and feeds troubleshooting and capacity planning. Other commands like `Diagnose` and
`Inspect` provide narrower views; this one is the umbrella.

### For Humans: What This Means (Role)

This is the big dashboard command—use it when you want the whole picture. Other commands zoom into smaller areas.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct

#### Technical Explanation (Construct)

Injects metrics, service repository, discovery, validator, and cache manager to power inspection sections.

##### For Humans: What This Means (Construct)

Stores all the helpers it needs to gather health, performance, dependencies, and cache info.

##### Parameters (__construct)

- `EnhancedMetricsCollector $metrics`: Source for performance metrics.
- `ServiceDefinitionRepository $serviceRepo`: Access to service definitions and stats.
- `ServiceDiscovery $discovery`: Dependency discovery component.
- `ServiceValidator $validator`: Validator for service integrity.
- `CacheManagerIntegration $cacheManager`: Cache health and statistics provider.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Construct before running the command; dependencies must be fully initialized.

##### Common Mistakes (__construct)

Passing partially configured services results in incomplete sections.

### Method: configure

#### Technical Explanation (Configure)

Defines command options for selecting sections, output format, and verbosity, and sets the description.

##### For Humans: What This Means (Configure)

Declares which flags you can use (format, verbose, and the section toggles).

##### Parameters (configure)

- None.

##### Returns (configure)

- `void`

##### Throws (configure)

- None.

##### When to Use It (configure)

Runs automatically by Symfony; no direct calls usually needed.

##### Common Mistakes (configure)

Forgetting to add new options here when extending the command.

### Method: execute

#### Technical Explanation (Execute)

Reads options, determines which sections to render, runs health, performance, services, dependencies, cache, and
validation sections, and optionally recommendations. Handles errors with failure codes and optional traces.

##### For Humans: What This Means (Execute)

Runs the selected inspections and prints the reports; returns success or failure.

##### Parameters (execute)

- `InputInterface $input`: CLI input.
- `OutputInterface $output`: CLI output.

##### Returns (execute)

- `int`: Command status code.

##### Throws (execute)

- Exceptions surface in error output; method returns failure code when caught.

##### When to Use It (execute)

Any time you need a container-wide diagnostic snapshot.

##### Common Mistakes (execute)

Requesting no sections and forgetting the command defaults to all; ignoring failure exit codes in scripts.

### Method: showHealthCheck

#### Technical Explanation (Show Health)

Runs cache-manager health checks, prints status indicators, and optionally detailed checks when verbose.

##### For Humans: What This Means (Show Health)

Tells you if the container is healthy, degraded, or unhealthy, with details when you ask for them.

##### Parameters (showHealthCheck)

- `SymfonyStyle $io`: Output helper.
- `string $format`: Output format.
- `bool $verbose`: Whether to show detailed checks.

##### Returns (showHealthCheck)

- `void`

##### Throws (showHealthCheck)

- None.

##### When to Use It (showHealthCheck)

Part of execute when health is requested or implied.

##### Common Mistakes (showHealthCheck)

Skipping verbose mode when you need exact failing checks.

### Method: displayHealthDetails

#### Technical Explanation (Display Health)

Renders individual health check results in JSON or simple text depending on format.

##### For Humans: What This Means (Display Health)

Prints the details behind the health status.

##### Parameters (displayHealthDetails)

- `SymfonyStyle $io`
- `array<string,mixed> $healthData`
- `string $format`

##### Returns (displayHealthDetails)

- `void`

##### Throws (displayHealthDetails)

- None.

##### When to Use It (displayHealthDetails)

Internal helper when verbose health output is requested.

##### Common Mistakes (displayHealthDetails)

Assuming table output; health details use JSON or plain lines.

### Method: showPerformanceAnalysis

#### Technical Explanation (Show Performance)

Calculates and displays resolution metrics (count, average time, error rate) and optional anomaly details when verbose.

##### For Humans: What This Means (Show Performance)

Shows how fast resolutions are and flags unusually slow services.

##### Parameters (showPerformanceAnalysis)

- `SymfonyStyle $io`
- `string $format`
- `bool $verbose`

##### Returns (showPerformanceAnalysis)

- `void`

##### Throws (showPerformanceAnalysis)

- None.

##### When to Use It (showPerformanceAnalysis)

When performance data is requested or included in full runs.

##### Common Mistakes (showPerformanceAnalysis)

Ignoring anomalies list when debugging slow services.

### Method: displayAnomalies

#### Technical Explanation (Display Anomalies)

Formats detected performance anomalies into JSON or table rows with service, duration, and strategy.

##### For Humans: What This Means (Display Anomalies)

Lists the slow or problematic services.

##### Parameters (displayAnomalies)

- `SymfonyStyle $io`
- `array<int,mixed> $anomalies`
- `string $format`

##### Returns (displayAnomalies)

- `void`

##### Throws (displayAnomalies)

- None.

##### When to Use It (displayAnomalies)

Internal helper after anomaly detection.

##### Common Mistakes (displayAnomalies)

None; ensure anomalies are checked before assuming performance is fine.

### Method: showServicesOverview

#### Technical Explanation (Show Overview)

Fetches all services and stats, outputs totals and lifetime counts, and optionally full details when verbose.

##### For Humans: What This Means (Show Overview)

Gives a summary of how many services exist, their lifetimes, and more detail if you ask for it.

##### Parameters (showServicesOverview)

- `SymfonyStyle $io`
- `string $format`
- `bool $verbose`

##### Returns (showServicesOverview)

- `void`

##### Throws (showServicesOverview)

- `Exception` when repository calls fail.

##### When to Use It (showServicesOverview)

When service inventory visibility is needed.

##### Common Mistakes (showServicesOverview)

Skipping verbose mode when you need individual service info.

### Method: showServiceDetails

#### Technical Explanation (Show Details)

Formats each service’s ID, class, lifetime, dependency count, and active flag into JSON or table form.

##### For Humans: What This Means (Show Details)

Lists the services one by one with their key properties.

##### Parameters (showServiceDetails)

- `SymfonyStyle $io`
- `Arrhae $services`
- `string $format`

##### Returns (showServiceDetails)

- `void`

##### Throws (showServiceDetails)

- None.

##### When to Use It (showServiceDetails)

Internal helper when verbose service details are requested.

##### Common Mistakes (showServiceDetails)

Interpreting dependency count without considering optional dependencies.

### Method: showDependencyAnalysis

#### Technical Explanation (Show Dependencies)

Retrieves dependency stats, prints totals and cycles, flags circular dependencies, and optionally renders detailed
lists.

##### For Humans: What This Means (Show Dependencies)

Shows how services depend on each other and whether cycles exist.

##### Parameters (showDependencyAnalysis)

- `SymfonyStyle $io`
- `string $format`
- `bool $verbose`

##### Returns (showDependencyAnalysis)

- `void`

##### Throws (showDependencyAnalysis)

- `Exception` for repository errors.

##### When to Use It (showDependencyAnalysis)

When exploring dependency health or investigating cycles.

##### Common Mistakes (showDependencyAnalysis)

Ignoring cycle warnings; they can break resolution.

### Method: showDependencyDetails

#### Technical Explanation (Show Dep Details)

Renders “most depended” services in JSON or table format to highlight critical services.

##### For Humans: What This Means (Show Dep Details)

Lists the services most relied on by others.

##### Parameters (showDependencyDetails)

- `SymfonyStyle $io`
- `array<string,mixed> $dependencyStats`
- `string $format`

##### Returns (showDependencyDetails)

- `void`

##### Throws (showDependencyDetails)

- None.

##### When to Use It (showDependencyDetails)

Internal helper when deeper dependency insight is needed.

##### Common Mistakes (showDependencyDetails)

Overlooking high-dependence services when planning changes.

### Method: showCacheStatistics

#### Technical Explanation (Show Cache)

Displays cache backend info and availability, optionally prototype cache stats with hits/misses when verbose.

##### For Humans: What This Means (Show Cache)

Shows what cache backend is used and how well the prototype cache performs.

##### Parameters (showCacheStatistics)

- `SymfonyStyle $io`
- `string $format`
- `bool $verbose`

##### Returns (showCacheStatistics)

- `void`

##### Throws (showCacheStatistics)

- None.

##### When to Use It (showCacheStatistics)

When diagnosing cache behavior or backend health.

##### Common Mistakes (showCacheStatistics)

Assuming prototype stats exist for all cache implementations.

### Method: showValidationResults

#### Technical Explanation (Show Validation)

Pulls validation summary, prints counts of valid/invalid services and errors, and optionally lists per-service
validation issues when verbose.

##### For Humans: What This Means (Show Validation)

Tells you how many services pass validation and what errors exist.

##### Parameters (showValidationResults)

- `SymfonyStyle $io`
- `string $format`
- `bool $verbose`

##### Returns (showValidationResults)

- `void`

##### Throws (showValidationResults)

- `Exception` from validator calls.

##### When to Use It (showValidationResults)

When checking configuration integrity or after changes to service definitions.

##### Common Mistakes (showValidationResults)

Ignoring invalid counts; they signal broken services.

### Method: showRecommendations

#### Technical Explanation (Show Recommendations)

Derives simple recommendations from health, performance anomalies, dependency cycles, and validation results, printing
success when none exist.

##### For Humans: What This Means (Show Recommendations)

Summarizes next steps based on issues it finds.

##### Parameters (showRecommendations)

- `SymfonyStyle $io`
- `string $format`

##### Returns (showRecommendations)

- `void`

##### Throws (showRecommendations)

- `Exception` if upstream checks fail.

##### When to Use It (showRecommendations)

At the end of a full inspection to decide what to fix first.

##### Common Mistakes (showRecommendations)

Treating “no recommendations” as a guarantee—still monitor periodically.

## Risks, Trade-offs & Recommended Practices

- **Risk: Sensitive output**. Contains service identifiers and dependency graphs; restrict access.
- **Risk: Large output**. Verbose mode can be noisy; choose formats intentionally.
- **Trade-off: Breadth vs time**. Full inspections take longer; target specific sections in frequent runs.
- **Practice: Script in CI**. Run with JSON/YAML in pipelines to enforce health gates.
- **Practice: Baseline and compare**. Keep historical outputs to spot regressions.

### For Humans: What This Means (Risks)

Protect the output, choose the right amount of detail, and automate checks where possible so you notice regressions
early.

## Related Files & Folders

- `docs_md/Tools/Console/DiagnoseCommand.md`: Faster high-level health check.
- `docs_md/Tools/Console/InspectCommand.md`: Single-service inspection.
- `docs_md/Tools/Console/ClearCacheCommand.md`: Clear caches when cache stats reveal issues.

### For Humans: What This Means (Related)

Pick the tool that matches your need: broad (this command), quick (diagnose), or focused (inspect), and clear caches
when needed.
