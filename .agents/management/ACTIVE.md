# ACTIVE Board

Visual live board for currently active work across `TODO.md` and `BUGS.md`.

## Rules

- mirror only active items from `TODO.md` and `BUGS.md`
- every card must reference the canonical queue item `id`
- update this board in the same change where queue status changes
- closed items leave this board and move to evidence, not to a fake done column
- card labels should stay short and include the latest meaningful timestamp

Recommended card format:

- `TODO-001 | short label | updated 2026-03-27 21:14 CET`
- `BUG-003 | short label | updated 2026-03-27 21:18 CET`

## Board

```mermaid
flowchart LR
    subgraph Done["Completed"]
        D0["V1 Kernel — PROVEN"]
        D1["V2 Platform — GREEN"]
        D2["V3 SystemDesign — GREEN"]
        D3["V4 Runtime — GREEN"]
        D4["V5 Convergence — GREEN"]
        D5["V5.5 Benchmarks — GREEN"]
        D6["V5.6 FailureBoundary — FULL GREEN"]
        D7["Current Plan Lock — FULL GREEN"]
        D8["V5.7 Design Lock — GREEN"]
        D9["V5.7-01 Owner Convergence — GREEN"]
    end

    subgraph Ready["Ready Next"]
        R0["V5.7-02 Contracts & Foundation — READY_NEXT"]
    end

    subgraph InProgress["In Progress"]
        P0["None"]
    end

    subgraph Planned["Locked Roadmap"]
        PL0["V5.8-V6.9 — PLANNED/LOCKED"]
    end

    Done --> Ready
    Ready --> InProgress
    InProgress --> Planned
```

All stages through V5.6 are COMPLETE. Current Plan Lock is FULL GREEN.
V5.7 Design Lock is GREEN. V5.7 Implementation is READY_NEXT but NOT_STARTED. V5.8-V6.9 are PLANNED/LOCKED.
