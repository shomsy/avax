# Context

## What This Folder Represents
Immutable value objects that represent the state of a single invocation (target normalization, reflection, resolved arguments, and result). It exists because invocation becomes a pipeline, and pipelines benefit from explicit state objects.

### For Humans: What This Means (Represent)
This folder holds the “invocation notebook” that tracks what you intended to call, what it got normalized into, what arguments were resolved, and what result came back.

## What Belongs Here
- `InvocationContext`: State for one invocation.

### For Humans: What This Means (Belongs)
If it represents state for a callable invocation, it belongs here.

## What Does NOT Belong Here
Executors, caching, and heavy logic.

### For Humans: What This Means (Not Belongs)
This is just state, not the engine.

## How Files Collaborate
`InvokeAction` creates an `InvocationContext`, `InvocationExecutor` populates it with normalized targets/reflection/arguments, then invokes.

### For Humans: What This Means (Collaboration)
The executor fills in the notebook as it goes.
