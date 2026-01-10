# Steps

## What This Folder Represents
Contains the concrete kernel pipeline steps that inspect, build, validate, and cache service definitions during resolution. Each step takes the shared `KernelContext`, enforces a specific policy (prototype analysis, guard checks, lifecycle storage, etc.), and passes the augmented context to the next step.

### For Humans: What This Means (Represent)
This is the assembly line of work the container does when resolving a service—each step checks or modifies the shared context before handing it along.

## What Belongs Here
Classes implementing `KernelStep` that perform actions such as prototype analysis, guard enforcement, dependency injection, instance resolution, lifecycle storage, and telemetry reporting.

### For Humans: What This Means (Belongs)
Every piece of logic that runs as part of the container pipeline belongs here; if it touches the context and runs in the kernel, it lives in this folder.

## What Does NOT Belong Here
Helper utilities, contracts, configuration, or non-pipeline behavior such as CLI tools or providers.

### For Humans: What This Means (Not Belongs)
Don’t mix unrelated helpers here—only actual pipeline steps stay in this folder.

## How Files Collaborate
Steps are invoked sequentially by the resolution pipeline builder. Early steps enrich metadata (prototype analysis, diagnostics), mid steps validate and inject dependencies, later steps resolve/retrieve instances, and final steps store lifecycle state.

### For Humans: What This Means (Collaboration)
Think of it as a conveyor belt where each worker does one job, then passes the context to the next worker.
