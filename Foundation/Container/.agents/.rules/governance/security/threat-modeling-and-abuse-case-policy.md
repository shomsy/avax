# Threat Modeling And Abuse-Case Policy

Version: 1.0.0
Status: Normative

Use this policy for features, services, APIs, identity flows, payment-like
operations, admin surfaces, and other changes with non-trivial trust impact.

## Threat Modeling Rule

The earlier a trust defect is found, the cheaper it is to fix.
Security review starts in design, not only after code exists.

## Minimum Required Questions

Every serious feature or system should be able to answer:

1. what are we protecting
2. who are the actors
3. where does trust change
4. what can an attacker abuse
5. what is the blast radius if controls fail
6. how is abuse detected and contained

## Required Inputs

- feature purpose and user or operator outcome
- entrypoints and exposed surfaces
- trusted and untrusted data paths
- dependencies and external systems
- auth, authorization, and identity posture
- privileged actions and sensitive business flows

## Abuse-Case Baseline

At minimum, consider:

- privilege escalation
- tenant breakout
- object or property overreach
- replay and race abuse
- enumeration and brute-force posture
- webhook or callback forgery
- SSRF and outbound trust abuse
- data exfiltration and overexposure
- unsafe admin, support, or impersonation paths
- CI/CD or artifact tampering paths when the feature touches delivery

## Expected Outputs

Threat modeling should produce:

- trust boundary map
- named abuse cases
- highest-impact assumptions
- required controls
- required verification or monitoring
- explicit residual risks

## When It Is Mandatory

This policy is mandatory for:

- new externally exposed surfaces
- auth or authorization model changes
- new admin or privileged features
- new payment-like, workflow-gating, or data-export flows
- high-impact integrations and webhooks
- major architecture or CI/CD trust changes

## Rule

- do not hide high-risk assumptions in architecture prose
- if a feature has an obvious abuse path, the design must name it before launch
- if abuse detection is impossible, recovery and forensics will be weak too
