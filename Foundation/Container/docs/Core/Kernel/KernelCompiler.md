# KernelCompiler

## Quick Summary
KernelCompiler handles the build-time processing of service definitions, performing validation, optimization, and caching to prepare the container for efficient runtime operation. It analyzes all registered services, generates optimized prototypes, validates configurations, and provides comprehensive compilation statistics. This compilation phase ensures that potential issues are caught early and that runtime resolution is as fast as possible through pre-computed optimizations.

### For Humans: What This Means (Summary)
Think of KernelCompiler as the quality control and preparation team that checks and optimizes everything before the container opens for business. While the runtime handles customer orders, the compiler makes sure all the recipes are correct, ingredients are available, and everything is arranged for maximum efficiency. It's the behind-the-scenes preparation that makes the container run smoothly and catch problems before they affect users.

## Terminology (MANDATORY, EXPANSIVE)**Service Compilation**: The process of analyzing and optimizing service definitions for runtime performance. In this file, the `compile()` method performs this analysis. It matters because it transforms human-readable configurations into optimized runtime structures.

**Prototype Generation**: Creating pre-analyzed representations of service structures and dependencies. In this file, `ServicePrototypeFactory` creates these prototypes. It matters because it avoids repeated analysis during runtime resolution.

**Validation Phase**: Checking service definitions for correctness and potential runtime issues. In this file, `VerifyPrototype` performs validation. It matters because it catches configuration errors before they cause runtime failures.

**Compilation Statistics**: Metrics about the compilation process including success rates and performance data. In this file, `compile()` returns detailed statistics. It matters because it provides visibility into the container's preparation phase.

**Cache Management**: Handling the storage and invalidation of compiled artifacts. In this file, `clearCache()` manages prototype cache. It matters because it enables cache invalidation for development and testing.

**Reflection Analysis**: Runtime examination of PHP classes to understand their structure. In this file, used in `resolveDefinitionClass()` for class validation. It matters because it enables dynamic analysis of service implementations.

### For Humans: What This Means
These are the preparation vocabulary. Service compilation is optimizing the kitchen setup. Prototype generation is pre-measuring ingredients. Validation is checking recipes for errors. Statistics are the prep report. Cache management is cleaning storage. Reflection analysis is examining equipment capabilities.

## Think of It
Imagine a restaurant preparing for dinner service: checking all recipes, prepping ingredients, validating that all equipment works, and organizing the kitchen for maximum efficiency. KernelCompiler is that preparation phase where the chef reviews every dish on the menu, tests cooking methods, verifies ingredient availability, and arranges workstations. Only after this thorough preparation does the restaurant open its doors, confident that orders can be fulfilled quickly and correctly.

### For Humans: What This Means (Think)
This analogy captures why KernelCompiler exists: comprehensive preparation. Without it, the container would discover problems only when services are requested, leading to failures and poor performance. KernelCompiler creates the foundation of reliability and speed by doing the hard work upfront.

## Story Example
Before KernelCompiler existed, containers validated services only during first resolution, causing slow startup and runtime errors. Developers discovered configuration mistakes only when users encountered them. With KernelCompiler, all services are validated and optimized during container construction. A container that previously failed mysteriously now provides clear validation errors and optimized performance from the start.

### For Humans: What This Means (Story)
This story shows the reliability problem KernelCompiler solves: delayed error discovery. Without it, container setup was like launching a rocket without pre-flight checks—hoping everything works. KernelCompiler adds the rigorous testing and optimization that ensures successful launches every time.

## For Dummies
Let's break this down like preparing a meal plan:

1. **The Problem**: You discover missing ingredients or wrong recipes only when cooking starts.

2. **KernelCompiler's Job**: The prep chef who checks all recipes, gathers ingredients, and organizes the kitchen beforehand.

3. **How You Use It**: Run compilation during container setup to catch issues early.

4. **What Happens Inside**: Analyzes services, creates optimized blueprints, validates configurations, and caches results.

5. **Why It's Helpful**: Ensures the container is ready to serve requests quickly and reliably.

Common misconceptions:
- "It's just validation" - It also performs optimization and caching for performance.
- "It's slow" - Compilation happens once at startup, making runtime faster.
- "I can skip it" - Skipping compilation reduces performance and hides configuration errors.

### For Humans: What This Means (Dummies)
KernelCompiler isn't optional—it's essential. It takes the complexity of preparation and makes it systematic, ensuring your container is battle-ready. You get better performance and fewer surprises by investing in proper preparation.

## How It Works (Technical)
KernelCompiler iterates through all service definitions, resolves concrete classes, generates prototypes using the factory, validates each prototype, and collects comprehensive statistics. It handles exceptions gracefully, recording errors in metrics while continuing compilation. Cache management provides control over stored artifacts.

### For Humans: What This Means (How)
Under the hood, it's a thorough inspector. For each service, it figures out what class to use, creates an optimized blueprint, checks for problems, and keeps score. If something goes wrong, it notes the issue but keeps going. It's like a building inspector who checks every room, notes problems, but completes the inspection regardless.

## Architecture Role
KernelCompiler sits at the build-time boundary of the container architecture, performing preparation work that enables efficient runtime operation. It transforms declarative service configurations into optimized runtime structures while maintaining separation between compilation and execution phases.

### For Humans: What This Means (Role)
In the container's architecture, KernelCompiler is the construction phase—the preparation that happens before the building is used. It creates the optimized foundation that runtime components build upon, ensuring the container is ready for production use.

## Risks, Trade-offs & Recommended Practices
**Risk**: Compilation can be slow for large numbers of services.

**Why it matters**: Analyzing hundreds of services takes time during startup.

**Design stance**: Optimize compilation performance and consider incremental compilation.

**Recommended practice**: Profile compilation time and optimize prototype generation for large service registries.

**Risk**: Validation errors during compilation can prevent container startup.

**Why it matters**: Strict validation might reject valid but complex configurations.

**Design stance**: Provide configurable validation levels and clear error messages.

**Recommended practice**: Use compilation statistics to monitor validation failures and adjust validation strictness.

**Risk**: Cached prototypes can become stale if class definitions change.

**Why it matters**: Development changes might not be reflected in cached prototypes.

**Design stance**: Clear cache during development and provide cache invalidation APIs.

**Recommended practice**: Integrate cache clearing into development workflows and deployment scripts.

### For Humans: What This Means (Risks)
Like any preparation phase, KernelCompiler has timing and strictness trade-offs. It's powerful for catching issues but requires tuning for different environments. The key is balancing thoroughness with practicality.

## Related Files & Folders
**ServicePrototypeFactory**: Creates the prototypes that KernelCompiler validates and caches. You configure it for prototype generation strategies. It provides the core optimization logic.

**VerifyPrototype**: Performs the validation that KernelCompiler orchestrates. You encounter it when validation errors occur. It defines what constitutes a valid service prototype.

**DefinitionStore**: Provides the service definitions that KernelCompiler processes. You register services that get compiled. It supplies the raw materials for compilation.

**ContainerBuilder**: Uses KernelCompiler during the build process. You access compilation indirectly through builder operations. It integrates compilation into the container construction workflow.

### For Humans: What This Means (Related)
KernelCompiler works with a complete preparation ecosystem. The prototype factory does the detailed work, verification checks quality, the definition store provides materials, and the builder orchestrates the process. Understanding this ecosystem helps you optimize the container preparation phase.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: compile(): array

#### Technical Explanation (compile)
This method serves as the primary entry point for build-time optimization, iterating through all registered service definitions to generate optimized prototypes and validate configurations. It collects comprehensive statistics about the compilation process, including success rates, performance metrics, and error counts. The method handles exceptions gracefully, recording validation failures in metrics while continuing compilation.

##### For Humans: What This Means (compile)
When you call compile(), you're telling the container to do all its homework upfront—analyze every service, create optimized blueprints, check for problems, and give you a report on how it went. It's like having the kitchen staff prepare and validate all recipes before the dinner rush begins.

##### Parameters (compile)
- None.

##### Returns (compile)
- `array`: A statistics array containing compiled_services (count of successfully processed services), cache_size (number of cached prototypes), compilation_time (time taken in seconds), and validation_errors (count of services that failed validation).

##### Throws (compile)
- `\Throwable`: When prototype creation or validation fails (handled internally with error counting).

##### When to Use It (compile)
- During container initialization to prepare services for runtime use
- In development to catch configuration errors early
- When deploying applications to ensure all services are valid

##### Common Mistakes (compile)
- Calling compile() multiple times unnecessarily (it's expensive)
- Ignoring the returned statistics (they provide valuable debugging information)
- Expecting compilation to fail fast on errors (it continues processing for comprehensive reporting)

### Method: validate(): void

#### Technical Explanation (validate)
This method performs lightweight validation of all service definitions without the performance cost of full compilation and caching. It creates prototypes and runs validation checks, but skips the expensive caching operations. This enables quick feedback during development and testing without the overhead of optimization.

##### For Humans: What This Means (validate)
Validate() is like a quick health check of your container configuration. It verifies that all your services are properly configured and can be resolved, but it doesn't spend time creating optimized blueprints. It's the difference between a full medical exam and a quick vital signs check.

##### Parameters (validate)
- None.

##### Returns (validate)
- `void`: This method doesn't return anything; it throws exceptions if validation fails.

##### Throws (validate)
- `\Avax\Container\Features\Core\Exceptions\ResolutionException`: When service validation fails due to configuration errors or dependency issues.

##### When to Use It (validate)
- During development to quickly verify configuration changes
- In automated tests to ensure container setup is valid
- Before deployment as a final configuration check

##### Common Mistakes (validate)
- Using validate() in production code (it's primarily a development tool)
- Expecting validate() to perform the same checks as compile() (it skips optimization steps)
- Not handling ResolutionException in calling code

### Method: clearCache(): void

#### Technical Explanation (clearCache)
This method removes all cached prototypes and compilation artifacts from the internal cache, forcing fresh analysis on subsequent operations. It provides a clean slate for development workflows and testing scenarios where cached data might become stale.

##### For Humans: What This Means (clearCache)
Clearing the cache is like telling the container to forget everything it learned about services and start fresh. This is useful when you've changed service definitions or class structures, and you want to ensure the container sees the latest versions.

##### Parameters (clearCache)
- None.

##### Returns (clearCache)
- `void`: This method doesn't return anything; it just clears the cache.

##### Throws (clearCache)
- None.

##### When to Use It (clearCache)
- During development after making changes to service classes
- In testing scenarios to ensure clean state between tests
- When deploying updated code to clear potentially stale caches

##### Common Mistakes (clearCache)
- Calling clearCache() in performance-critical production code
- Forgetting that clearing cache will slow down the next container operations
- Not understanding that cache is rebuilt automatically on next use

### Method: stats(?array $compilationStats): array

#### Technical Explanation (stats)
This method provides compilation metrics, either using provided statistics or generating fallback defaults based on the current cache state. It enables monitoring and debugging of compilation performance by returning standardized metrics regardless of whether full compilation was performed.

##### For Humans: What This Means (stats)
Stats() gives you insight into how well the compilation process worked, whether you ran full compilation or just want to check the current state. It's like getting a performance report on the container's preparation phase.

##### Parameters (stats)
- `?array $compilationStats`: Optional pre-computed statistics; if null, defaults are generated from cache state.

##### Returns (stats)
- `array`: Compilation statistics with keys for compiled_services, cache_size, compilation_time, and validation_errors.

##### Throws (stats)
- None.

##### When to Use It (stats)
- After compilation to review the results
- For monitoring container health and performance
- In debugging scenarios to understand compilation behavior

##### Common Mistakes (stats)
- Passing invalid statistics arrays (should match the expected structure)
- Assuming stats() performs compilation (it only reports on existing state)
- Not using the returned metrics for monitoring and alerting

### Method: resolveDefinitionClass(ServiceDefinition $definition): ?string

#### Technical Explanation (resolveDefinitionClass)
This private method determines the concrete class name to use for a service definition by examining the concrete binding and falling back to the abstract identifier. It performs reflection analysis to validate that the resolved class exists and is instantiable, returning null for invalid definitions.

##### For Humans: What This Means (resolveDefinitionClass)
This internal method figures out what actual class should be used when the container needs to create a service. It looks at the service definition and decides which class to instantiate, making sure that class actually exists and can be created.

##### Parameters (resolveDefinitionClass)
- `ServiceDefinition $definition`: The service definition containing abstract and concrete bindings.

##### Returns (resolveDefinitionClass)
- `?string`: The resolved class name if valid and instantiable, null otherwise.

##### Throws (resolveDefinitionClass)
- `\Throwable`: When reflection analysis fails (handled internally by returning null).

##### When to Use It (resolveDefinitionClass)
- This is an internal method used during compilation and validation processes.

##### Common Mistakes (resolveDefinitionClass)
- This method is private and should not be called directly from outside the class.

### Method: __construct(...)

#### Technical Explanation (__construct)
This method is part of the file’s public/protected behavior surface. It exists to make a specific step in the container’s workflow explicit and reusable.

##### For Humans: What This Means (__construct)
When you call this (or when the container calls it), you’re asking the system to do one focused thing without you having to manually wire the details.

##### Parameters (__construct)
- See the PHP signature in the source file for exact types and intent.

##### Returns (__construct)
- See the PHP signature and implementation for what comes back and why it matters.

##### Throws (__construct)
- Any thrown exceptions here are part of the “contract” you need to be ready for when integrating this method.

##### When to Use It (__construct)
- Use it when you want this unit of behavior, not when you want to re-implement the underlying steps.

##### Common Mistakes (__construct)
- Calling it in the wrong lifecycle moment (before the container is configured/booted).
- Treating it as a pure function when it may read or affect container state.
