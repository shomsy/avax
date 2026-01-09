# Features/Think/Model

## What This Folder Represents
This folder holds the “prototype models” for dependency injection.

Technically, the classes here are immutable (or treated as immutable) data structures that represent *what the container learned* about a service: which constructor exists, which parameters are required, which properties/methods should be injected, and what defaults/constraints apply. They are designed to be serializable so they can be cached and reused.

### For Humans: What This Means
These are the container’s notes. Instead of re-reading your class every time, it keeps a structured summary.

## What Belongs Here
- Immutable prototype DTOs: `ServicePrototype`, `MethodPrototype`, `ParameterPrototype`, `PropertyPrototype`.
- Support structures around prototype storage/reporting (if present).

### For Humans: What This Means
If it’s a “blueprint object” that describes injection, it belongs here.

## What Does NOT Belong Here
- Reflection and analysis logic (that’s `Think/Analyze`).
- Injection execution (that’s `Features/Actions`).

### For Humans: What This Means
These classes are “data”, not “behavior”.

## How Files Collaborate
Analyzers create these objects. Verifiers validate them. Caches store them. Runtime resolution reads them to know what to inject and how.

### For Humans: What This Means
They’re like printed blueprints: you don’t redraw them while building—you just follow them.

