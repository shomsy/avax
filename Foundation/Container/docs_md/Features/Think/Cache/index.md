# Features/Think/Cache

## What This Folder Represents
This folder caches the results of expensive prototype analysis.

Technically, `Features/Think/Cache` defines the caching contract (`PrototypeCache`) and concrete implementations (like `FilePrototypeCache`) that persist `ServicePrototype` instances across runs. The purpose is simple: reflection analysis is expensive, so you cache prototypes and reuse them in production.

### For Humans: What This Means
Instead of re-reading a long manual every time you build an object, you write down the key notes once and reuse them.

## What Belongs Here
- The `PrototypeCache` interface.
- Cache implementations (file-based, memory-based, etc.).
- Integration layers that choose/configure cache backends.

### For Humans: What This Means
If it stores prototype “blueprints” so you don’t re-analyze, it belongs here.

## What Does NOT Belong Here
- Prototype analysis logic itself (that’s `Think/Analyze`).
- Runtime resolution steps (that’s `Features/Actions` / `Core/Kernel`).

### For Humans: What This Means
Cache is storage, not thinking or doing.

## How Files Collaborate
Analyzers produce `ServicePrototype` instances. Verifiers validate them. Cache implementations store them and later retrieve them for runtime usage. Integrations help select a cache backend from configuration.

### For Humans: What This Means
Build blueprint → validate blueprint → store blueprint → reuse blueprint.

