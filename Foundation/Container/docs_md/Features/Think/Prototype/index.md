# Features/Think/Prototype

## What This Folder Represents
This folder turns analysis into reusable, production-friendly prototypes.

Technically, `Features/Think/Prototype` contains factories/builders and dumpers that orchestrate prototype creation, caching, and (optionally) compilation into deployable artifacts. It sits between “Analyze” (discover facts) and “Cache/Runtime” (reuse facts).

### For Humans: What This Means
This is where the container takes what it learned about your classes and turns it into something you can reuse easily—like printing the blueprint and filing it away.

## What Belongs Here
- Factories that create prototypes and consult caches.
- Builders that construct prototypes programmatically.
- Dumpers/compilers that export definitions/prototypes into PHP artifacts.
- Contracts that define factory behavior.

### For Humans: What This Means
If a class’s job is “make prototypes and manage their lifecycle”, it belongs here.

## What Does NOT Belong Here
- Raw reflection scanning logic (that’s `Think/Analyze`).
- Cache storage implementations (that’s `Think/Cache`).
- Runtime injection/instantiation steps (that’s `Features/Actions` / `Core/Kernel`).

### For Humans: What This Means
This is the orchestration layer, not the microscope and not the factory floor.

## How Files Collaborate
Factories ask caches for an existing `ServicePrototype`. If missing, they delegate to analyzers to create one, then store it back into the cache. Dumpers can export store/prototype data into precompiled PHP artifacts for fast production loading.

### For Humans: What This Means
It’s the “check if we already know this” logic, plus the “save it for later” logic.

