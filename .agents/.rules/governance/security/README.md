# Security Governance

Reusable security policy and secure SDLC contracts for web applications, APIs,
services, jobs, CI/CD systems, and deployable software products.

This folder defines the normative security baseline.
It is not a product-specific threat model and it is not an operator-only
runbook lane.

## What Lives Here

- `secure-development-lifecycle.md`: security duties from requirements to
  retirement
- `owasp-web-and-api-baseline.md`: reusable web, app, and API security
  baseline
- `authentication-and-session-security.md`: identity, session, token, and
  authorization law
- `secrets-and-key-management.md`: secret handling and cryptographic material
  policy
- `ci-cd-and-supply-chain-security.md`: pipeline, dependency, artifact, and
  provenance security
- `tool-and-plugin-capability-isolation.md`: least-privilege and isolation law
  for tools, plugins, MCP servers, and sandboxes
- `threat-modeling-and-abuse-case-policy.md`: design-time threat and abuse-case
  discipline
- `vulnerability-and-incident-response.md`: intake, triage, remediation, and
  response posture

## Standards Anchors

Use this folder with awareness of current security standards such as:

- OWASP Top 10: 2025 for current web application risk framing
- OWASP API Security Top 10: 2023 for API-specific exposure
- OWASP ASVS 5.0.0 as the verification-depth baseline for serious applications
- OWASP Proactive Controls for developer-facing control categories
- OWASP DevSecOps Guideline for secure pipeline posture
- OWASP CycloneDX and similar SBOM standards where software supply-chain
  transparency matters

## Boundary With Other Governance Domains

- `../operations/security-launch-checklist.md` is the operator launch gate
- this folder is the policy and SDLC baseline that exists before launch day
- `../profiles/**` may narrow security behavior for a specific stack
- child repos must bind concrete tools, commands, endpoints, and approval paths

## Rule

- if a repository ships software, exposes data, or depends on CI/CD, security
  is not optional
- fail-closed, least privilege, explicit trust boundaries, and auditable
  evidence are the default posture
- a launch checklist cannot compensate for a missing secure SDLC
