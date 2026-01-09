# Core

## What This Folder Represents
High-level invoker actions that provide a stable API for the rest of the container to invoke callables with automatic dependency resolution. It exists to keep invocation usage consistent and to hide executor wiring details.

### For Humans: What This Means
This is the friendly front door to “smart call.” You use these classes instead of talking to the low-level executor directly.

## What Belongs Here
- `InvokeAction`: The main action used to invoke callables with DI.

### For Humans: What This Means
If it’s the API you call from other parts of the container to invoke callables, it belongs here.

## What Does NOT Belong Here
Reflection caches, context models, or low-level execution helpers.

### For Humans: What This Means
Core is the public entry point, not the machinery.

## How Files Collaborate
`InvokeAction` wires a `InvocationExecutor` once it has a container and delegates invocation to it.

### For Humans: What This Means
InvokeAction sets things up once and then calls the executor for you.
