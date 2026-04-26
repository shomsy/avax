---
title: ClearCompiledCache-flow
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# ClearCompiledCache Flow

Clears compiled artifacts from the compiled cache directory.

## What This Owns

- ClearCompiledCache - main flow entry
- ClearCompiledCacheArtifact - deletes single artifact
- ClearAllCompiledCacheArtifacts - deletes all artifacts

## Triggers

- `$compiledCache->clear($name)` call
- `$compiledCache->clearAll()` call
- CLI: `php avax cache:clear`

## Main Flow

```mermaid
sequenceDiagram
    participant Client
    participant ClearCompiledCache
    participant Directory

    Client->>ClearCompiledCache: clear(name)
    ClearCompiledCache->>Directory: delete artifact file
    ClearCompiledCache->>Directory: remove manifest entry
    ClearCompiledCache-->>Client: void

    Client->>ClearCompiledCache: clearAll()
    ClearCompiledCache->>Directory: delete all artifact files
    ClearCompiledCache->>Directory: clear manifest
    ClearCompiledCache-->>Client: void
```

## Failure Modes

- file permission denied
- manifest write fails