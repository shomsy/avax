# CI/CD And Supply-Chain Security

Version: 1.0.0
Status: Normative

Use this policy for build pipelines, release automation, dependency posture,
artifact publication, container delivery, and infrastructure-as-code flows.

## Pipeline Trust Rule

CI/CD is part of the attack surface.
Pipeline compromise is product compromise.

## Baseline

- source control protections, review posture, and release permissions must be
  explicit
- pipeline identities must use least privilege
- build and release jobs should be reproducible, auditable, and environment-
  scoped
- mutable manual steps should be minimized and clearly owned
- untrusted pull-request code must not automatically inherit production-grade
  secrets or deploy permissions

## Secure Pipeline Controls

At minimum, serious pipelines should consider:

- secret leakage scanning
- lint and policy checks
- SAST
- SCA
- IaC scanning
- container or base-image scanning where applicable
- DAST or integration probing for exposed surfaces where justified
- artifact integrity and provenance checks

The exact toolchain may vary.
The control classes must still be defendable.

## Dependency And Component Rules

- direct and transitive dependencies need ownership and update posture
- vulnerable and outdated components are release-significant findings, not
  background noise
- lockfiles and dependency manifests are part of the security contract
- dependency sources should be trusted, pinned, and minimally permissive
- emergency patch or quarantine posture must exist for exploited dependencies

## SBOM And Inventory Rules

- serious systems should be able to produce a machine-readable software bill of
  materials
- CycloneDX or another widely-supported SBOM format is preferred when supply-
  chain transparency matters
- SBOM data should cover first-party, third-party, and containerized components
  where practical
- dependency inventory without remediation ownership is incomplete
- release evidence should indicate how dependency risk was checked

## Artifact And Provenance Rules

- published artifacts should have named ownership and integrity posture
- do not allow unsigned, untracked, or manually replaced artifacts to become
  canonical silently
- build outputs should be traceable to source revision and pipeline execution
- promotion between environments should preserve provenance instead of rebuilding
  from unknown local state
- cache poisoning and dependency confusion risks must be considered for package
  and build systems

## Runner And Environment Rules

- CI runners should be ephemeral or aggressively cleaned between jobs where the
  platform allows it
- self-hosted runners require stronger hardening and network policy
- environment variables and mounted credentials should be scoped per job and
  per environment
- deploy jobs should not run in the same trust context as arbitrary test jobs
  without a deliberate control boundary
- production deploy capability should require controlled approvals or policy
  gates appropriate to risk

## Release And Deployment Rules

- release automation should enforce the same security gates every time
- deployment should verify artifact identity before promotion
- infrastructure changes should be reviewed and scanned like application code
- rollback paths must preserve security posture, not merely availability
- post-deploy smoke should include protected-surface and policy checks

## Recommended OWASP Anchors

This lane maps strongly to:

- OWASP Top 10: 2025 for supply-chain, integrity, and deployment-related risk
  framing
- OWASP DevSecOps Guideline
- OWASP Dependency-Check, Dependency-Track, CycloneDX, or equivalent classes of
  supply-chain control
