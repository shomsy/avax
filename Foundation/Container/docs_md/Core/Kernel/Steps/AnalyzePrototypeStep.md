# AnalyzePrototypeStep

## Quick Summary
- Analyzes the service prototype via reflection when it has not yet been inspected.
- Stores the prototype metadata and analysis timestamp on the `KernelContext`.
- Prevents repeated reflection by checking existing metadata and records failures for diagnostics.

### For Humans: What This Means
It’s the reflection stage that inspects a service only once, writes the prototype into the context, and stops retrying if the analysis already happened.

## Terminology
- **Service prototype**: Reflection-based blueprint of constructor, dependencies, and injection points; this step generates it once per service.
- **Analysis metadata**: Flags stored on the `KernelContext` (`analysis.prototype`, `analysis.completed_at`, etc.) to avoid re-running the expensive inspection.
- **Strict mode**: A constructor flag that could tighten exception handling (the class accepts it but currently only records errors in metadata).

### For Humans: What This Means
Prototype is the build plan; metadata says “we already looked at it”; strict mode would make it more sensitive to failures.

## Think of It
Like a quality inspector who studies a new machine before the assembly line runs. Once the inspector documents the machine, everyone else sees that note and skips re-inspection.

### For Humans: What This Means
It’s the one-time inspection before the rest of the pipeline runs.

## Story Example
Before this step existed, every resolution re-reflected the class, wasting time. Now `AnalyzePrototypeStep` fires once, caches the prototype, and future resolutions see the prototype metadata and skip the work.

### For Humans: What This Means
You stop doing the expensive reflection twice and keep the pipeline fast.

## For Dummies
1. Check whether prototype analysis already ran by looking at `analysis.prototype` metadata.
2. Determine which class should be analyzed—service ID, definition concrete, or returning null for closures.
3. Ask `ServicePrototypeFactory` to build the prototype and store it on the context along with a timestamp.
4. If an exception happens, note it in metadata before propagating the error.

### For Humans: What This Means
It’s a checklist so the context only gets inspected once and keeps a record of success or failure.

## How It Works (Technical)
The `__invoke` method guards re-analysis with metadata, resolves which class to analyze, invokes `ServicePrototypeFactory`, and records prototype and timestamps on the context. A failing prototype stores failure flags and rethrows the exception.

### For Humans: What This Means
It copies the prototype into the context so the rest of the pipeline can rely on it without redoing reflection.

## Architecture Role
Belongs in kernel steps to provide prototype data before dependency injection and guard checks. Depends on `ServicePrototypeFactory` and feeds metadata into later steps.

### For Humans: What This Means
It’s the step that primes the pipeline with the service blueprint the later steps use to wire dependencies.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __invoke(KernelContext $context)

#### Technical Explanation
Performs reflection-based prototype analysis if the context lacks it, stores the resulting prototype and timestamps as metadata, and records failures with error details.

##### For Humans: What This Means
It inspects the service once, saves the blueprint, and records whether it succeeded.

##### Parameters
- `KernelContext $context`: The shared state used across pipeline steps.

##### Returns
- `void`: Data is stored by side effect on the context.

##### Throws
- `Throwable`: Propagates reflection or analysis errors after noting them in metadata.

##### When to Use It
Always invoked by the resolution pipeline before injection and guard steps.

##### Common Mistakes
- Re-running analysis without checking existing metadata.
- Assuming closures have prototypes (they return `null`).

### Method: determineClassToAnalyze(string $serviceId, ServiceDefinition|null $definition)

#### Technical Explanation
Chooses the concrete class to inspect: uses the definition’s concrete target, closure awareness, or the service ID if no definition exists.

##### For Humans: What This Means
It decides whether reflection applies and what class to reflect.

##### Parameters
- `string $serviceId`: Identifier being resolved.
- `ServiceDefinition|null $definition`: Optional service definition with concrete target.

##### Returns
- `string|null`: Class name to reflect or `null` if not applicable.

##### Throws
- None.

##### When to Use It
Called internally by `__invoke` before prototype creation.

##### Common Mistakes
- Treating closures as classes—they return `null` so the step is skipped.

## Risks, Trade-offs & Recommended Practices
- **Risk: Reflection performance**. Running analysis for every resolution is costly; metadata guards prevent repeated work.
- **Trade-off: Strict mode sensitivity**. If enabled, the step could fail fast on prototype errors; currently it prefers recording failure metadata.
- **Practice: Capture failure context**. Always annotate metadata with failure flags so diagnostics know why analysis failed.

### For Humans: What This Means
Only analyze once, track failures, and avoid redoing heavy reflection.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Folder overview.
- `docs_md/Core/Kernel/Contracts/KernelContext.md`: Context storing prototype metadata.
- `docs_md/Features/Think/Prototype/ServicePrototypeFactory.md`: Factory this step uses.

### For Humans: What This Means
See the context used, the factory invoked, and the step list for how this fits into the pipeline.
