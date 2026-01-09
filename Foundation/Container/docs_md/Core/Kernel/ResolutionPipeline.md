# ResolutionPipeline

## Quick Summary
ResolutionPipeline orchestrates the sequential execution of resolution steps that transform service requests into fully constructed, injected objects. It implements the pipeline pattern to enable modular, composable dependency resolution where each step performs a specific operation on a shared context. This design allows complex resolution logic to be broken into testable, reusable components while maintaining atomic execution semantics—if any step fails, the entire resolution fails.

### For Humans: What This Means
Imagine an assembly line where each station adds a crucial piece to build a complete product. The ResolutionPipeline is the conveyor belt and control system that moves your service request through specialized workstations. Each workstation (step) does its part—some analyze the request, others create objects, others inject dependencies—and if anything goes wrong at any station, the whole process stops to prevent defective products from shipping.

## Terminology
**Pipeline Pattern**: A design pattern where data flows through a series of processing stages, each performing a specific transformation. In this file, the pipeline executes KernelStep instances in sequence. It matters because it enables separation of concerns in complex processing workflows.

**Kernel Context**: A mutable context object that carries resolution state and metadata through the pipeline. In this file, the context is passed to each step and accumulates results. It matters because it provides the communication mechanism between pipeline stages.

**Kernel Step**: An individual processing unit that performs a specific operation on the resolution context. In this file, steps are executed in order via the `run()` method. It matters because it allows complex resolution logic to be decomposed into focused, testable components.

**Terminal Step**: A special type of step that can halt pipeline execution when a resolution is complete. In this file, TerminalKernelStep instances can break the execution loop early. It matters because it enables optimization by avoiding unnecessary processing.

**Step Telemetry**: A monitoring system that collects performance and execution data about pipeline steps. In this file, telemetry tracks step timing and events. It matters because it enables observability and performance analysis of resolution processes.

**Atomic Execution**: The property that all pipeline steps must succeed or the entire operation fails. In this file, exceptions in any step cause pipeline termination. It matters because it ensures resolution consistency and prevents partial states.

### For Humans: What This Means
These are the operational vocabulary of systematic processing. The pipeline pattern is like an assembly line workflow, kernel context is the work order that travels with the product, kernel steps are the specialized workstations, terminal steps are quality control checkpoints that can stop production, telemetry is the performance monitoring system, and atomic execution is the all-or-nothing guarantee that ensures nothing half-finished gets out.

## Think of It
Picture a high-tech manufacturing facility where raw materials enter one end and finished products emerge from the other. The ResolutionPipeline is the sophisticated control system that manages the entire production line. Sensors monitor progress, quality checks ensure standards are met, and if any machine malfunctions, the entire line stops to prevent defective products. Each station specializes in a different aspect—analysis, assembly, testing, packaging—and they work in perfect harmony.

### For Humans: What This Means
This analogy shows why ResolutionPipeline exists: to orchestrate complex, multi-stage processes reliably. Without it, each resolution would require manual coordination of multiple systems, leading to errors and inconsistencies. The pipeline provides the automated, reliable workflow that makes sophisticated dependency injection possible.

## Story Example
Before ResolutionPipeline existed, dependency resolution was implemented as monolithic methods with complex conditional logic. Adding new resolution features required modifying large, fragile functions. With the pipeline, each resolution concern became a focused step that could be developed, tested, and maintained independently. Performance optimizations and security checks became simple step additions rather than code surgery.

### For Humans: What This Means
This story illustrates the organizational problem ResolutionPipeline solves: monolithic complexity. Without it, dependency resolution was like trying to build a car in a single workshop with all tools mixed together. The pipeline creates specialized workstations where each tool has its place and purpose, making the entire process more reliable, maintainable, and extensible.

## For Dummies
Let's break this down like processing an order at a coffee shop:

1. **The Problem**: Baristas had to remember every step manually, leading to mistakes and delays.

2. **ResolutionPipeline's Job**: A standardized workflow where each person handles one specific task.

3. **How You Use It**: Configure the steps you want, pass in the order, and get the result.

4. **What Happens Inside**: Each step processes the order in sequence, with quality checks along the way.

5. **Why It's Helpful**: Ensures consistent, high-quality results every time.

Common misconceptions:
- "It's just a loop" - It's a sophisticated orchestration system with error handling and telemetry.
- "Steps can be skipped" - Pipeline execution is atomic; all steps must succeed.
- "It's slow" - The overhead is minimal compared to the resolution work it coordinates.

### For Humans: What This Means
ResolutionPipeline isn't complex—it's organized. It takes the chaos of multi-step processing and turns it into a predictable, reliable workflow. You don't need to understand every detail; you just need to know it makes complex operations dependable.

## How It Works (Technical)
ResolutionPipeline holds an array of KernelStep instances and executes them sequentially in the `run()` method. Each step receives the KernelContext, performs its operation, and may modify the context. Telemetry collects performance data, and exceptions cause immediate pipeline termination with enhanced error messages. Terminal steps can halt execution early when resolution is complete.

### For Humans: What This Means
Under the hood, it's a conductor with a list of performers. When you call run(), it tells each performer to do their part in order, passing the baton (context) between them. It watches for problems, records performance, and stops everything if something goes wrong. It's like a relay race where each runner must complete their leg successfully for the team to win.

## Architecture Role
ResolutionPipeline sits at the heart of the kernel's processing architecture, defining the contract for extensible resolution logic while maintaining execution consistency. It establishes the boundaries between different resolution concerns while allowing the pipeline to be customized for different use cases.

### For Humans: What This Means
In the kernel's architecture, ResolutionPipeline is the main production line—the central workflow that defines how work gets done. It sets the standards for how steps interact while remaining flexible enough to accommodate different processing needs.

## Risks, Trade-offs & Recommended Practices
**Risk**: Pipeline steps can have complex interdependencies that are hard to manage.

**Why it matters**: Steps may assume certain context state that other steps provide.

**Design stance**: Keep steps focused and well-documented with clear pre/post conditions.

**Recommended practice**: Use context metadata to communicate state between steps explicitly.

**Risk**: Long pipelines can impact resolution performance.

**Why it matters**: Each step adds execution time, especially for frequently resolved services.

**Design stance**: Optimize hot paths and consider terminal steps for early completion.

**Recommended practice**: Profile pipeline execution and optimize or reorder steps based on telemetry data.

**Risk**: Step failures can leave context in inconsistent states.

**Why it matters**: Failed steps might partially modify context before throwing exceptions.

**Design stance**: Design steps to be idempotent and handle partial state cleanup.

**Recommended practice**: Use context metadata to track step completion and enable rollback if needed.

### For Humans: What This Means
Like any production system, ResolutionPipeline has operational boundaries. It's excellent for systematic processing but requires careful design. The key is understanding that it's a precision instrument that demands thoughtful step design and monitoring.

## Related Files & Folders
**KernelStep**: Defines the interface that all pipeline steps must implement. You implement this for custom resolution logic. It establishes the contract for step behavior.

**KernelContext**: Carries the resolution state that flows through the pipeline. You examine context during debugging. It provides the data structure for inter-step communication.

**TerminalKernelStep**: Marks steps that can halt pipeline execution early. You use this for optimization steps. It enables conditional pipeline termination.

**StepTelemetryCollector**: Handles telemetry data collection and reporting. You configure this for monitoring. It provides observability into pipeline performance.

**Steps/**: Contains concrete implementations of resolution steps. You examine these for specific resolution behaviors. They provide the actual processing logic.

### For Humans: What This Means
ResolutionPipeline works with a complete ecosystem. The step interface defines the rules, context carries the data, terminal steps provide control points, telemetry enables monitoring, and the steps folder contains the actual workers. Understanding this ecosystem helps you know how to customize and troubleshoot the resolution process.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: run(KernelContext $context): void

#### Technical Explanation
Executes the complete resolution pipeline by running all configured steps in sequence, passing the shared context through each step with comprehensive telemetry collection and error handling. Pipeline execution is atomic—if any step fails, the entire resolution terminates with enhanced error context.

##### For Humans: What This Means
This is the main execution method that starts the entire resolution process. It takes your service request and runs it through all the processing steps, collecting performance data and making sure everything works or fails cleanly. It's like pressing the "start" button on the assembly line.

##### Parameters
- `KernelContext $context`: The resolution context containing service request details and accumulating results

##### Returns
- `void`: Pipeline execution doesn't return a value; results are stored in the context

##### Throws
- `ContainerException`: When pipeline configuration is invalid
- `Throwable`: When any step fails (wrapped with pipeline execution context)

##### When to Use It
- When executing the complete service resolution process
- In kernel resolution methods that need full pipeline execution
- When you want comprehensive telemetry and error handling

##### Common Mistakes
- Modifying context after pipeline execution (context may be in final state)
- Ignoring telemetry data that could help with debugging
- Assuming pipeline always completes (it may terminate early on terminal steps)

### Method: count(): int

#### Technical Explanation
Returns the total number of steps configured in this pipeline instance, providing metadata about pipeline size for validation, debugging, and analysis purposes.

##### For Humans: What This Means
This tells you how many processing steps are configured in the pipeline. It's useful for understanding pipeline complexity and debugging configuration issues.

##### Parameters
- None.

##### Returns
- `int`: The number of steps in the pipeline

##### Throws
- None. Counting is always safe.

##### When to Use It
- For pipeline validation and size checks
- In debugging and logging scenarios
- When analyzing pipeline performance characteristics

##### Common Mistakes
- Using count() in performance-critical code (it's fast but unnecessary if you already know)
- Assuming count() includes only active steps (it includes all configured steps)
- Not using the count for validation (empty pipelines are invalid)

### Method: getStep(int $index): KernelStep

#### Technical Explanation
Retrieves a specific step from the pipeline by its zero-based index position, enabling inspection, testing, and analysis of individual pipeline components without executing the pipeline.

##### For Humans: What This Means
This lets you look at a specific step in the pipeline by its position. It's like being able to examine each workstation on the assembly line individually.

##### Parameters
- `int $index`: Zero-based index of the step to retrieve

##### Returns
- `KernelStep`: The step instance at the specified position

##### Throws
- `ContainerException`: When the index is out of bounds

##### When to Use It
- For pipeline inspection and debugging
- When testing individual steps in isolation
- For pipeline analysis and optimization

##### Common Mistakes
- Using negative indices (they're zero-based)
- Assuming indices are stable (pipeline construction can change ordering)
- Not handling out-of-bounds exceptions in validation code

### Method: withStep(KernelStep $step): self

#### Technical Explanation
Creates an immutable copy of this pipeline with the specified step appended to the end, enabling fluent pipeline construction and extension without modifying the original pipeline.

##### For Humans: What This Means
This adds a new processing step to the end of the pipeline and gives you a new pipeline. The original pipeline stays unchanged. It's like extending an assembly line with an additional workstation.

##### Parameters
- `KernelStep $step`: The step to add to the end of the pipeline

##### Returns
- `self`: A new pipeline instance with the additional step

##### Throws
- None. Pipeline extension is safe.

##### When to Use It
- When building pipelines incrementally
- For adding post-processing or cleanup steps
- In fluent pipeline construction patterns

##### Common Mistakes
- Assuming the original pipeline is modified (it creates a new instance)
- Not capturing the returned pipeline (the new one is what you want to use)
- Adding invalid step types (they're validated in constructor)

### Method: withStepFirst(KernelStep $step): self

#### Technical Explanation
Creates an immutable copy of this pipeline with the specified step prepended to the beginning, useful for adding high-priority operations like caching or validation that should run before other steps.

##### For Humans: What This Means
This adds a new processing step to the beginning of the pipeline. It's perfect for steps that need to run first, like checking if something is already cached.

##### Parameters
- `KernelStep $step`: The step to add to the beginning of the pipeline

##### Returns
- `self`: A new pipeline instance with the step at the beginning

##### Throws
- None. Pipeline modification is safe.

##### When to Use It
- When adding high-priority steps like caching or validation
- For pre-processing operations that should run before main logic
- In pipeline customization scenarios

##### Common Mistakes
- Using withStepFirst() when withStep() would be more appropriate
- Not understanding the performance implications of prepended steps
- Assuming prepend order is preserved with multiple calls (each creates a new pipeline)

### Method: __toString(): string

#### Technical Explanation
Provides a human-readable string representation of the pipeline structure, showing the step count and sequence of step class names for debugging and logging purposes.

##### For Humans: What This Means
This gives you a nice summary of what the pipeline looks like, showing how many steps it has and what order they're in. It's great for debugging and understanding pipeline configuration.

##### Parameters
- None.

##### Returns
- `string`: A formatted string showing pipeline structure

##### Throws
- None. String conversion is always safe.

##### When to Use It
- For debugging pipeline configuration
- In logging and monitoring scenarios
- When displaying pipeline information to developers

##### Common Mistakes
- Assuming the format is stable for parsing (it's for human consumption)
- Not using it in debug output (it's very helpful for understanding pipelines)
- Expecting detailed step information (it only shows class names)
