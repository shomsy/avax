---
title: WarmCompiledCache-flow
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# WarmCompiledCache Flow

Warms multiple compiled artifacts at application startup.

## What This Owns

- WarmCompiledCache - main flow entry
- WarmCompiledCacheArtifacts - compiles multiple artifacts

## Triggers

- Application warmup
- CLI: `php avax cache:warm`

## Main Flow

```mermaid
sequenceDiagram
    participant App
    participant WarmCompiledCache
    participant CompiledArtifacts[]

    App->>WarmCompiledCache: warm(definitions)
    loop for each definition
        WarmCompiledCache->>CompiledArtifacts: compile artifact
    end
    WarmCompiledCache-->>App: results
```

## Failure Modes

- one artifact build fails
- partial failure reporting required