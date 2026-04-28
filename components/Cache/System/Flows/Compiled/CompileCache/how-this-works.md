---
title: CompileCache-flow
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# CompileCache Flow

Compiles and writes a single compiled artifact to the compiled cache directory.

## What This Owns

- CompileCache - main flow entry
- BuildCompiledCacheArtifact - builds artifact metadata
- WriteCompiledCacheArtifact - writes PHP file atomically

## Triggers

- `$compiledCache->compile($name, $builder, $sources)` call
- CLI: `php avax cache:compile`

## Main Flow

```mermaid
sequenceDiagram
    participant Client
    participant CompileCache
    participant Builder
    participant AtomicWriter
    participant Manifest

    Client->>CompileCache: compile(name, builder, sources)
    CompileCache->>Builder: call builder
    Builder-->>CompileCache: payload
    CompileCache->>AtomicWriter: write(name, payload)
    AtomicWriter->>Manifest: update after success
    CompileCache-->>Client: artifact metadata
```

## Failure Modes

- builder returns invalid payload
- directory not writable
- atomic rename fails
- manifest write fails