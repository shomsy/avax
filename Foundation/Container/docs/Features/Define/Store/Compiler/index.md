# Features/Define/Store/Compiler

## What This Folder Represents
This folder defines how you can process definitions before runtime.

Technically, this is a contract layer for “compiler passes”: objects that take a `DefinitionStore` and transform it or validate it. This lets you run an explicit pre-processing phase where you can normalize definitions, apply conventions, or reject invalid registrations before the container starts resolving services.

### For Humans: What This Means (Represent)
It’s like proofreading and rewriting your recipe book before you start cooking, so you don’t discover mistakes at dinner time.

## What Belongs Here
- Interfaces/abstractions for compiler passes.
- Simple passes that transform a `DefinitionStore` (if present in the project).

### For Humans: What This Means (Belongs)
Anything that “rewrites the rules” before runtime belongs here.

## What Does NOT Belong Here
- Runtime resolution steps or pipeline logic.
- Bootstrapping code that registers services.

### For Humans: What This Means (Not Belongs)
This is for preparation, not execution.

## How Files Collaborate
The runtime (or a bootstrapper) can run a series of compiler passes. Each pass receives the `DefinitionStore`, potentially changes it, and then hands it to the next pass. The output is a cleaned-up, ready-to-use set of definitions.

### For Humans: What This Means (Collaboration)
You can stack “rule editors” in a sequence, and each one makes the store a bit better.

