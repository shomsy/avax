# Agent Harness V4 Roadmap — Enterprise Hardening

**Status**: Draft
**Target**: V4.0.0
**Reference**: USER_REQUEST_2026-05-16

## Core Hardening Pillars

### 1. CI-Native Governance Execution
- **GitHub Actions / GitLab CI Parity**: Provide official `.github/workflows/verify-governance.yml` templates.
- **Local/CI Parity**: Ensure `install-os.sh --validate` is the single source of truth for both local hooks and CI gates.
- **Zero-Drift**: Detect when a repository's governance has drifted from its declared profiles.

### 2. Machine-Executable Recursive Review
- **Review Engines**: Move from procedural (docs) to deterministic (scripts).
- **Auto-Classification**: Automatically classify findings (Blocker/High/Medium) based on pre-defined severity rules.
- **Remediation Graph**: Provide clear machine-readable steps to fix a red status.

### 3. Governance Performance Profiling
- **Overhead Measurement**: Measure how much time agents spend parsing governance vs. executing code.
- **Scalability**: Benchmark against 10k, 50k, and 100k file repositories.
- **Monorepo Support**: Optimize validation latency for large-scale monorepos.

### 4. Real-World Adoption Proof
- **Polyglot Validation**: Prove architecture neutrality with 1 PHP, 1 Go, and 1 Node/TS reference implementation.
- **Neutrality Audit**: Ensure no language-specific leaks remain in core governance.

### 5. Governance Migration Engine
- **V1→V3 Automation**: Provide a scripted path to upgrade legacy harness versions.
- **Stale Profile Detection**: Identify and suggest removal of deprecated rules or profiles.

### 6. Agent Orchestration Runtime
- **Role Delegation**: Split tasks between Planner, Executor, Reviewer, and Truth agents.
- **Society of Mind**: Implement the orchestration pattern for complex, multi-agent workflows.

### 7. Governance Observability
- **Metrics**: Entropy score, Drift score, and Review Debt trends.
- **False-Green Detection**: Detect when status is claimed GREEN but evidence is missing or stale.

### 8. Sandboxed Execution Model
- **Mutation Isolation**: Strengthen boundaries for dangerous commands.
- **Trust Tiers**: Enforce different permissions based on the agent's role and the task's risk profile.

### 9. Formal Release Lanes
- **LTS Strategy**: Define Experimental, Beta, Stable, and Enterprise-LTS support windows.

### 10. AI Hallucination Containment
- **Evidence Reconciliation**: An engine that matches claims in `STATUS.md` against actual machine-readable evidence.
- **Proof-Required Gate**: Prevent `FULL_GREEN` status if machine-evidence is missing.

### 11. Self-Healing Governance
- **Auto-Reconciliation**: Suggest fixes for contradictory documents or dead profiles.
- **Stale Contract Detection**: Identify when a contract hasn't been updated despite significant repo changes.

### 12. Long-Term Entropy Defense
- **Simplification Pressure**: Automated pressure to reduce the number of rules and documents.
- **Archive Lifecycle**: Automatic moving of old evidence to archives.
