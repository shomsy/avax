# Advanced Deployment Policy (V3)

Version: 3.0.0
Status: Normative

## 1. Release Approval Lifecycle
Releases to production require explicit approval from the assigned Release Agent or human Release Manager. The approval requires a `FULL_GREEN` recursive review state.

## 2. Canary and Staged Rollout
- Phase 1: 5% traffic. Wait 10 minutes. Check SLOs.
- Phase 2: 25% traffic. Wait 15 minutes. Check SLOs.
- Phase 3: 100% traffic.
Any anomaly detected automatically triggers a rollback.

## 3. Rollback Management
Rollbacks MUST be automated. When a rollback occurs:
1. The previous stable state is restored.
2. A `rollback-evidence-pack.json` is generated.
3. The original deployment is marked as FAILED in `STATUS.md`.
