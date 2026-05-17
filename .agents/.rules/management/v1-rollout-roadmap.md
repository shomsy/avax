# v1 Rollout Roadmap — Shipment Plan

Version: 1.0.0
Status: Normative
Scope: `.agents/management/roadmap/**`

This document defines the 3-week sequence for deploying the **Agent OS v1** specialized orchestration framework.

---

## 📅 Week 1: Foundation (Orchestration Core)

- [x] **Supervisor / Router**: Role definition and classification logic.
- [x] **Mapper / Researcher**: Role definition and exploration tools.
- [x] **Codex Executor**: Implementation guidelines and usage policy.
- [x] **Docs MCP**: Knowledge base for project-specific and external docs.
- [x] **Minimal Supervisor Flow**: 8-step canonical pipeline.

## 📅 Week 2: Signal & Standard (Briefing Layer)

- [x] **GitHub Read-only MCP**: Access to Issues, PRs, and Diffs.
- [x] **CI / Log Triager**: Role and tool access for build summaries.
- [x] **Artifact Contract**: Mandatory 9-field schema enforcement.
- [x] **Prompt Templates**: v1 templates for Mapping, Research, and Execute.

## 📅 Week 3: Governance & Hardening (Control Layer)

- [x] **Reviewer Agent**: Final diff audit and sanity check role.
- [x] **Trust Tiers (T0-T2)**: Permission enforcement and approval gates.
- [x] **Human Approval Gates**: Mandatory for all external write (T2).
- [x] **Success Metrics**: Tracking Codex Avoidance and Context Reduction.

---

## 🚀 Post-v1: Expansion (Optional)

1.  **Additional Agents**: Security, Performance, Ops.
2.  **Remote Orchestration**: Parallel execution via Blackbox/Cline pods.
3.  **Write-capable MCP Servers**: Gradual rollout with multi-layer approval.

## 📈 Success Criteria for v1 Rollout

1.  **Codex Avoidance > 30%** for non-implementation tasks.
2.  **Context Input Reduction > 80%** for final implementation.
3.  **Zero Unapproved Write Actions** to external systems.
