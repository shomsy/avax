# Features/Think/Analyze

## What This Folder Represents
This folder is where raw PHP reflection turns into structured knowledge.

Technically, `Features/Think/Analyze` contains the reflection-heavy analyzers that inspect a class (constructor, parameters, properties, attributes) and convert that into prototype models. These analyzers are intentionally CPU-heavy but *stateless*: you run them when building prototypes, not on every resolution.

### For Humans: What This Means
This is the “scan the blueprint” stage. The container reads your class like a mechanic reading an engine manual before touching the engine.

## What Belongs Here
- Reflection/type analyzers.
- Logic that detects injection points and computes prototypes.

### For Humans: What This Means
If a class’s job is to *look at other classes* and describe them, it lives here.

## What Does NOT Belong Here
- Actual object creation (instantiate/inject/invoke).
- Caching implementations (those live in `Features/Think/Cache`).

### For Humans: What This Means
This folder thinks. It doesn’t build.

## How Files Collaborate
`ReflectionTypeAnalyzer` provides low-level reflection helpers and consistent error handling. `PrototypeAnalyzer` uses that to build higher-level `ServicePrototype` models from the reflected class. Other parts of the container can then verify and cache those models.

### For Humans: What This Means
One class provides the “microscope”, the other writes the “lab report”.

