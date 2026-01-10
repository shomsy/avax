# Features/Define/Store

## What This Folder Represents
This folder is the container’s internal registry for “what services mean”.

Technically, `Features/Define/Store` holds the data structures that represent service definitions and the indices that make them queryable (by id, by tag, by consumer context, etc.). Builders write into this folder’s models; runtime resolution reads from them. The store is the authoritative source of truth for service metadata.

### For Humans: What This Means (Represent)
This is the container’s notebook. Builders write down facts here, and the runtime reads those facts later when it needs to build objects.

## What Belongs Here
- Definition models (`ServiceDefinition`) and registries (`DefinitionStore`).
- Indexing helpers (tags, contextual maps, extender lists).
- Optional compilation contracts that can process the store.

### For Humans: What This Means (Belongs)
If it’s about “storing the truth” of your registrations, it lives here.

## What Does NOT Belong Here
- Fluent DSLs (those live in `Features/Define/Bind`).
- Object creation logic (those live in `Features/Actions` and `Core/Kernel`).
- Reflection analysis (those live in `Features/Think`).

### For Humans: What This Means (Not Belongs)
This folder is storage, not a factory.

## How Files Collaborate
`ServiceDefinition` is the per-service blueprint. `DefinitionStore` stores many definitions and keeps extra lookup tables (tags, contextual overrides, extenders). Compiler passes can be applied to the store to pre-process definitions (validation, normalization, optimization) before runtime resolution consumes them.

### For Humans: What This Means (Collaboration)
One blueprint describes one service, and the store keeps the whole library of blueprints.

