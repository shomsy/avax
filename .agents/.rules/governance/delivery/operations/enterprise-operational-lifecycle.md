# Enterprise Operational Lifecycle (V3)

Version: 3.0.0
Status: Normative
Scope: `.agents/governance/delivery/operations/**`

This document defines the strict operational expectations for enterprise-grade projects under the Agent Harness V3 OS.

## 1. Operational Readiness & SLAs
Every service MUST define its Service Level Agreement (SLA) and Service Level Objectives (SLOs) in its documentation. Deployment to production requires passing an Operational Readiness Review (ORR).

## 2. Incident Management & Support Escalation
- **Triage**: Any system failure affecting SLAs triggers an Incident.
- **Support Escalation**: Incidents must escalate to on-call engineers via defined pager paths. Agents MUST NOT automatically resolve high-severity incidents without human oversight.
- **Hotfix Flow**: A hotfix bypasses the standard sprint cycle but MUST NOT bypass the Recursive Governance Review. Validation, security checks, and explicit evidence logging are mandatory.

## 3. Postmortem Lifecycle
Every `HIGH` or `BLOCKER` incident requires a structured postmortem logged in `.agents/management/evidence/security/` or `operations/`.
- Must contain: Root cause, timeline, impact, and actionable prevention tasks.
- No blame language.

## 4. Release Freeze & Rollout Lifecycle
- **Release Freeze**: No non-hotfix deployments are allowed during declared freeze windows.
- **Staged Rollout & Canary Lifecycle**: Enterprise projects MUST deploy via canary or staged rollouts. An agent MUST NOT promote a canary to 100% traffic without verifying error rate and latency metrics against the SLOs.
- **Production Verification**: Post-deployment, the Release Agent must run a live smoke test suite and record the output in the Release Evidence Pack.

## 5. Audit Readiness
The combination of the `.agents/management/evidence/` machine logs and the explicit human dashboard (`EVIDENCE/`) forms the continuous audit trail. Changes, reviews, test outputs, and escalation records are immutable.
