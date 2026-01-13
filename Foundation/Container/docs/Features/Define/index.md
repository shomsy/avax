# Features/Define

## What This Folder Represents

This folder is where you *teach the container new facts*.

Technically, `Features/Define` is the “definition layer” of the Container component: it contains the builders and stores
that let you describe what a service *is*, how it should be created, and what extra rules should apply (lifetime, tags,
contextual overrides, etc.). Nothing here is about *executing* resolution; it’s about recording intent in a structured
way so other parts of the container can later act on it.

### For Humans: What This Means (Represent)

Think of this folder like a recipe book you write for your kitchen robot. You’re not cooking here—you’re writing down
recipes so the robot can cook later, consistently, without guessing.

## What Belongs Here

- Fluent registration/builders that help you “declare” bindings (readable, developer-facing APIs).
- Storage objects that hold service metadata (“blueprints”) and lookup indices (tags, contextual rules).
- Compilation contracts that can transform/validate definitions before runtime.

### For Humans: What This Means (Belongs)

If a class helps you *describe* how services should behave, it lives here.

## What Does NOT Belong Here

- Runtime resolution logic (that belongs in `Core/Kernel` and `Features/Actions`).
- Reflection analysis models (that belongs in `Features/Think`).
- Observability/diagnostics instrumentation (that belongs in `Observe`).

### For Humans: What This Means (Not Belongs)

If the code *builds objects*, it’s not “Define”. If it *describes objects*, it is.

## How Files Collaborate

`Bind/` provides fluent builders (DSLs) that are pleasant to use during bootstrapping. Those builders write into
`Store/`, which is the authoritative registry of definitions. Compiler passes (in `Store/Compiler`) can process and
normalize that registry before it’s used by the runtime engine.

### For Humans: What This Means (Collaboration)

You talk to the friendly “builder” APIs, and they quietly update the container’s internal notebook. Later, the runtime
reads that notebook to do real work.

