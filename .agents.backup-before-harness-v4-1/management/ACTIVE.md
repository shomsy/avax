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
        D24["V5.8-01 Foundation Enums — GREEN"]
        D25["V5.8-02 Registration Objects — GREEN"]
        D26["V5.8-03 Event Objects (19) — GREEN"]
        D27["V5.8-04 Compiled Registry — GREEN"]
        D28["V5.8-05 DSL Classes — GREEN"]
        D29["V5.8-06 Global Functions — GREEN"]
        D30["V5.8-07 Transaction Integration — GREEN"]
        D31["V5.8-08 Outbox Groundwork — GREEN"]
        D32["V5.8-09 Tests (67) — GREEN"]
        D33["V5.8 COMPLETE — GREEN"]
        D34["V5.8.5 Enterprise Cleanup — FULL GREEN"]
        D35["V5.8.6 Response Layer — FULL GREEN"]
    end

    subgraph Ready["Ready Next"]
        R0["V5.9 AuthBuilder split first slice"]
    end

    subgraph Blocked["Blocked"]
        B0["Boot DSL continuation | blocked by exact AuthBuilder blocker | updated 2026-05-16"]
    end

    subgraph Planned["Locked Roadmap"]
        PL0["V5.8-V6.9 — PLANNED/LOCKED"]
    end

    Done --> Ready
    Ready --> Blocked
    Blocked --> Planned
```

All stages through V5.8.x Repo-Wide Truth Reconciliation are COMPLETE historically. The V5.9 Boot DSL first slice
exists.
The 2026-05-16 Harness-Full governance baseline has been classified:

- Semantic PHPDoc legacy debt is YELLOW_WITH_RATCHET (9823 findings, touched/new scope blocking).
- How-to document structure is PASS.
- Large-unit gate has one exact real blocker: `AuthBuilder.php`.

V5.9 Boot DSL continuation is BLOCKED until `V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE` is executed or the AuthBuilder blocker
is formally reclassified with new evidence.
