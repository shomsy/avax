# Features/Operate

## What This Folder Represents

This folder is about running the container inside a real application lifecycle.

Technically, `Features/Operate` contains the “application-facing” orchestration pieces: bootstrapping, configuration wiring, scope lifecycle management, and shutdown actions. It’s where the container becomes operational, not just definable.

### For Humans: What This Means (Summary)

This is where the container goes from “a set of rules” to “a system that actually runs during your app’s start and finish”.

## What Belongs Here

- Boot-time orchestration and application lifecycle classes.
- Configuration objects and profiles that tune container behavior.
- Scope lifecycle management and shutdown/cleanup actions.

### For Humans: What This Means (Belongs)

If the code is about “starting, running, and stopping” the container, it belongs here.

## What Does NOT Belong Here

- Definition registration models (that’s `Features/Define`).
- Reflection analysis (that’s `Features/Think`).
- Runtime injection steps (that’s `Features/Actions` / `Core/Kernel`).

### For Humans: What This Means (Not Belongs)

Operate is the stage manager, not the script writer or the actors.

## How Files Collaborate

Boot components construct and configure the container for a specific environment. Scope components manage per-request/per-job boundaries for scoped lifetimes. Shutdown components clean up at the end of the lifecycle so state doesn’t leak.

### For Humans: What This Means (Collaboration)

First you set it up, then you run it, then you clean up—like any good lifecycle.
