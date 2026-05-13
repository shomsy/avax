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
        D10["V5.7-02 Contracts & Foundation — GREEN"]
        D11["V5.7-03 Fluent Event DSL — GREEN"]
        D12["V5.7-04 emit() Surface — GREEN"]
        D13["V5.7-05 ListensTo Attribute — GREEN"]
        D14["V5.7-06 Compiled Registry — GREEN"]
        D15["V5.7-07 Dispatch Runtime — GREEN"]
        D16["V5.7-08 PSR-14 Adapter — GREEN"]
        D17["V5.7-09 Real Dogfooding — GREEN"]
        D18["V5.7-10 CQRS Projection — GREEN"]
        D19["V5.7-11 Event-History — GREEN_REF"]
        D20["V5.7-12 Tooling Gates — GREEN"]
        D21["V5.7-13 Final Audit — GREEN"]
        D22["V5.7 COMPLETE — GREEN"]
        D23["V5.8 Design Lock — GREEN"]
    end

    subgraph Ready["Ready Next"]
        R0["None — V5.7 complete"]
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

All stages through V5.7 are COMPLETE. V5.7 is FULL GREEN (14 stages, 10 event gates PASS, 8069 tests GREEN, PHPStan 0 errors).
V5.8-V6.9 are PLANNED/LOCKED.
