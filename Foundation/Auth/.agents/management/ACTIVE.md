# ACTIVE Board

Visual live board for currently active work across `TODO.md` and `BUGS.md`.

## Board

```mermaid
flowchart LR
    subgraph Ready["Ready"]
        R1["AUTH-006 | Implementation of missing tests | 2026-04-06 16:30 CET"]
        R2["AUTH-007 | Docs Audit | 2026-04-06 16:30 CET"]
        R3["AUTH-008 | Examples Audit | 2026-04-06 16:30 CET"]
    end

    subgraph InProgress["In Progress"]
        P0["No active items"]
    end

    subgraph Blocked["Blocked"]
        B0["No blocked items"]
    end

    subgraph Verify["Verify / Review"]
        V1["AUTH-019 | OAuth/API auth subsystem v1 | updated 2026-04-12 15:36 CEST"]
    end

    Ready --> InProgress
    InProgress --> Verify
    InProgress --> Blocked
    Blocked --> InProgress
```
