# Features/Operate/Shutdown

## What This Folder Represents
This folder contains shutdown-time operations for the container.

Technically, `Features/Operate/Shutdown` is where you place actions that are executed when the container is being terminated or when an application scope ends permanently. These actions typically coordinate cleanup (closing scopes, releasing references, flushing resources) without embedding shutdown logic into unrelated runtime components.

### For Humans: What This Means (Represent)
It’s the “turn everything off safely” part of the container—like shutting down a factory and making sure machines stop in the right order.

## What Belongs Here
- Small shutdown actions (often invokable classes) that cleanly terminate scopes or resources.

### For Humans: What This Means (Belongs)
If it runs at the end of the container’s life, it belongs here.

## What Does NOT Belong Here
- Scope lifecycle operations themselves (those belong in `Features/Operate/Scope`).
- Runtime resolution and injection (those belong elsewhere).

### For Humans: What This Means (Not Belongs)
Shutdown is about cleanup, not doing new work.

## How Files Collaborate
Shutdown actions typically depend on scope infrastructure (like `ScopeManager`) and invoke termination methods. Higher-level boot/runtime flows decide *when* to run shutdown actions; this folder provides *what* to run.

### For Humans: What This Means (Collaboration)
Some other part decides “we’re done”, and this folder contains the cleanup steps to execute.

