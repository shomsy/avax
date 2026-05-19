# Adoption Model — Rules Engine & Precedence

This document defines how the Agent Harness is adopted into project repositories and how the rules engine handles the "two sources of truth" problem.

## 1. The Rules Engine (`.agents/.rules/`)

When `install-os.sh` is run, it copies the canonical governance from the harness into the target repository's `.agents/.rules/` directory.

- **`.agents/.rules/`**: This is a **read-only, frozen copy** of the harness version used at install/upgrade time. It contains the universal "laws" of the OS.
- **`.agents/governance/`**: This directory in the target project contains the **local specialization**.
- **Root `AGENTS.md`**: The primary entry point and conflict resolver.

### Precedence Diagram

```mermaid
graph TD
    Root["Root AGENTS.md (Local Choice)"] --> P1[".agents/AGENTS.md (Local Strategy)"]
    P1 --> P2[".agents/governance/** (Local Overrides)"]
    P2 --> P3[".agents/.rules/AGENTS.md (Harness Baseline)"]
    P3 --> P4[".agents/.rules/governance/** (Harness Baseline)"]

    subgraph "Local Project Space"
    Root
    P1
    P2
    end

    subgraph "Immutable OS Space"
    P3
    P4
    end
```

## 2. Resolving "Two Sources of Truth"

To prevent ambiguity:

1. **Explicit Precedence**: The root `AGENTS.md` MUST define the `Order Of Precedence`. If a rule exists in both `.agents/governance/` and `.agents/.rules/governance/`, the one higher in the precedence list wins.
2. **Frozen Base**: The `.agents/.rules/` directory should NEVER be modified by agents or humans in the child project. It is managed exclusively via `./install-os.sh --upgrade`.
3. **Local Shadowing**: If you need to change a global rule for a specific project, do not edit `.rules/`. Instead, create a file in `.agents/governance/` and place it higher in the precedence list.

## 3. Adoption Flow

```mermaid
sequenceDiagram
    participant H as Agent Harness
    participant P as Project Repo
    participant A as Agent

    H->>P: run install-os.sh
    Note over P: Creates .agents/.rules/ (Immutable)
    Note over P: Creates .agents/ (Local)
    Note over P: Creates AGENTS.md (Precedence)
    
    A->>P: Read Root AGENTS.md
    P-->>A: Order of Precedence
    A->>P: Load Governance
    Note right of A: Strategy: Local wins over OS Rules
```

## 4. Why `.rules`?

Without `.rules`, a project only has its local copies. If the local copies drift or are poorly edited, the "identity" of the Agent OS is lost. The `.rules` folder acts as the **"Constitutional Baseline"** that can be verified via `./install-os.sh --validate`.
