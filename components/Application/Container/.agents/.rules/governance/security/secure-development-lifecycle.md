# Secure Development Lifecycle

Version: 1.0.0
Status: Normative

Use this contract for repositories that build, deploy, or operate software with
security impact.

## SDLC Rule

Security is mandatory in every phase:

1. requirements
2. design
3. implementation
4. verification
5. release
6. operations
7. retirement

If any phase skips security ownership entirely, the SDLC is incomplete.

## 1. Requirements Phase

The adopting repository should define:

- protected assets and sensitive data classes
- user roles, trust boundaries, and abuse-sensitive flows
- compliance or contractual obligations when they exist
- security invariants that must never silently downgrade
- release-blocking security expectations

Mandatory outputs:

- security requirements are explicit
- trust assumptions are written down
- protected surfaces are named before implementation starts

## 2. Design Phase

Before major implementation:

- map trust boundaries
- map entrypoints and external dependencies
- identify abuse cases and high-impact misuse paths
- decide fail-closed behavior and acceptable degraded behavior
- decide logging, alerting, and forensic evidence posture

For serious systems, design should include:

- threat model or abuse-case review
- authorization boundary review
- data-flow review for secrets and sensitive data
- dependency and integration risk review

## 3. Implementation Phase

Implementation must:

- follow the applicable stack security profiles and policies
- validate input before side effects
- enforce authorization at the owned resource boundary
- keep secrets and privileged data out of source and logs
- preserve least privilege across code, runtime, and pipeline access

Security debt created during implementation must be tracked explicitly.

## 4. Verification Phase

Verification should include the minimum relevant combination of:

- code review with security scope
- unit and integration tests for trust boundaries
- SAST, SCA, IaC, container, and dependency scanning as applicable
- DAST or runtime probing for externally exposed surfaces when justified
- auth, authorization, session, and failure-path tests

For promoted software, evidence should show:

- what was checked
- which tools or commands ran
- what the findings were
- what remains accepted as residual risk

## 5. Release Phase

Release is NO-GO unless:

- required security gates are green
- secrets and environment posture are valid
- rollback and containment paths exist
- protected-route or protected-surface smoke checks are complete
- security-sensitive findings are either resolved or explicitly accepted

## 6. Operations Phase

Operating posture must include:

- monitoring for auth, access, and anomalous behavior
- vulnerability intake and remediation ownership
- secret rotation and emergency revocation paths
- patch and dependency update posture
- incident escalation and evidence capture expectations

## 7. Retirement Phase

When decommissioning a system, service, or feature:

- revoke or rotate remaining secrets and credentials
- disable exposed endpoints and integrations
- archive required evidence and logs by policy
- remove or quarantine stale artifacts and pipeline permissions
- verify that retired software cannot silently return via stale automation

## Mandatory Gates

Every serious repository should be able to defend:

- what must be protected
- who can do what
- how failure is contained
- how evidence is captured
- how vulnerabilities are fixed
- how compromised or retired assets are revoked
