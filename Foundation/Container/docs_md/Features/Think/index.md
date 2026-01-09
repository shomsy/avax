# Features/Think

## What This Folder Represents
This folder is the container’s “thinking phase”: reflection analysis and prototype building.

Technically, `Features/Think` is responsible for converting raw PHP classes into structured “prototypes” that describe how to inject dependencies (constructor parameters, injected properties, injected methods). This keeps runtime resolution faster and more deterministic: instead of doing reflection on every resolution, you can analyze once, cache it, and then reuse the result.

### For Humans: What This Means
This is the part where the container studies your class ahead of time, so it doesn’t have to “think on the spot” later.

## What Belongs Here
- Reflection/type analyzers that inspect classes.
- Prototype models (`ServicePrototype`, `MethodPrototype`, `ParameterPrototype`, `PropertyPrototype`).
- Caches for analyzed prototypes.
- Verifiers that validate prototypes before they’re used or cached.

### For Humans: What This Means
If the container is reading your code structure (via reflection) and turning it into a plan, it belongs here.

## What Does NOT Belong Here
- Actual instantiation and injection execution (those are `Features/Actions`).
- Definition registration and binding DSLs (those are `Features/Define`).

### For Humans: What This Means
Think = “make a plan”. Actions = “do the work”.

## How Files Collaborate
Analyzers inspect a class and produce prototype models. The prototypes can be verified for correctness, then cached for reuse. Runtime resolution consumes these prototypes instead of repeatedly reflecting the same classes.

### For Humans: What This Means
First you build a blueprint, then you reuse the blueprint whenever you need to build the object.

