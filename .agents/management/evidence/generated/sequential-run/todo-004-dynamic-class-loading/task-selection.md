# Task Selection

- TODO: TODO-004 — Dynamic class-loading boundaries
- Priority: P0 BLOCKER
- Source: TODO.md Iteration 1.4 / fix-this.md TODO-004
- Cluster: CLUSTER-006
- Scope: QueueWorker + FailureBoundary recovery/fallback paths (safe remediation batch)
- Reason for selection: Next P0 after Round 002 completion; Round 002 review complete
- Dependencies: Round 002 merged (all 4 branches integrated to main)
- Non-goal: Container class_exists+new sites tracked separately

Note: Per fix-this.md, the safe remediation batch is "Start with QueueWorker and FailureBoundary recovery/fallback paths; track container internals separately inside same cluster."
