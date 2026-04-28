---
title: ReadCompiledCache-flow
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# ReadCompiledCache Flow

Reads a compiled artifact, rebuilding if missing or stale.

## What This Owns

- ReadCompiledCache - main flow entry
- ReadCompiledCacheArtifact - requires and returns PHP payload
- RebuildCompiledCacheWhenMissing - rebuilds on missing
- RebuildCompiledCacheWhenStale - rebuilds on stale

## Triggers

- `$compiledCache->read($name, $builder, $sources)` call

## Main Flow

```mermaid
sequenceDiagram
    participant Client
    participant ReadCompiledCache
    participant PathResolver
    participant Manifest
    participant Builder
    participant PhpFile

    Client->>ReadCompiledCache: read(name, builder, sources)
    ReadCompiledCache->>PathResolver: resolve path
    PathResolver-->>ReadCompiledCache: path
    ReadCompiledCache->>Manifest: check freshness
    alt is fresh
        ReadCompiledCache->>PhpFile: require file
        PhpFile-->>ReadCompiledCache: payload
    else is stale or missing
        ReadCompiledCache->>Builder: rebuild
        Builder-->>ReadCompiledCache: payload
    end
    ReadCompiledCache-->>Client: payload
```

## Failure Modes

- compiled file returns invalid payload
- manifest corrupted