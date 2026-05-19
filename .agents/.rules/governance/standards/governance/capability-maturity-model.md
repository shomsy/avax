# Capability Maturity Model (CMM)

Version: 1.0.0
Status: Normative
Scope: `.agents/governance/**`

This document defines the maturity levels for governance capabilities within the Agent Harness OS. Every normative document should declare its maturity level.

## 1. Maturity Levels

| Level | Name | Description | Stability | Evidence Expectation |
| :--- | :--- | :--- | :--- | :--- |
| **L1** | **Experimental** | New concept, unproven in production. | Volatile | Minimal / Exploration only |
| **L2** | **Draft** | Documented but not fully implemented/validated. | Unstable | Proposal / Plan |
| **L3** | **Stable** | Proven in standard projects. | Solid | Validation logs |
| **L4** | **Hardened** | Battle-tested in multiple repo types. | Immutable | Review logs + quality metrics |
| **L5** | **Enterprise-Ready** | Full compliance, audit-ready, LOUD security. | Standard | Full Evidence Packs |

## 2. Capability Classification

| Capability | Current Maturity | Target (V3) |
| :--- | :--- | :--- |
| Core Bootstrap | L4 | L5 |
| Profile Resolution | L3 | L4 |
| Recursive Review | L3 | L5 |
| Evidence Model | L3 | L5 |
| Management Model | L4 | L5 |
| Security Model | L2 | L4 |
| Orchestration Model | L2 | L3 |
| Installer / Productization | L3 | L4 |
| Entropy Control | L1 | L2 |

## 3. Maturity Criteria

### L5 Enterprise-Ready Requirements:
- Machine-verifiable schema for all outputs.
- Explicit BLOCKER/HIGH/MEDIUM/LOW taxonomy.
- Traceability to human dashboard.
- Automated validation coverage > 90%.
- Zero known "LOUD" security gaps.
