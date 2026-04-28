# Secrets And Key Management

Version: 1.0.0
Status: Normative

Use this policy for repositories, runtimes, CI/CD systems, and operational
surfaces that handle credentials or cryptographic material.

## Secret Rule

Secrets are never ordinary configuration.
They require ownership, scope, rotation, revocation, and evidence.

## Baseline

- no secrets in source control, fixtures, snapshots, screenshots, or generated
  evidence
- no copying secrets into code comments, docs, tickets, or review notes
- secret names, scopes, and owners should be inventoried
- runtime secret delivery should happen through bound configuration or a managed
  secret system
- the repository should know which secrets are build-time only, deploy-time
  only, or runtime-only

## Scope And Least Privilege

- secrets must be scoped to the smallest honest blast radius
- separate environments must not silently share the same high-impact secret by
  convenience
- human, service, pipeline, and third-party credentials must be distinguishable
- read, write, sign, and admin capabilities should not share one token unless
  the platform forces it
- credentials for dev tools must not inherit production privilege without an
  explicit exception

## Rotation And Revocation

- every high-impact secret must have a rotation path
- emergency revocation must be defined for exposed or suspected-compromised
  credentials
- long-lived credentials require stronger justification than short-lived or
  federated identity
- if rotation breaks the system, the secret posture is too fragile
- release readiness should include secret validity for the touched surface

## Key Management

- encryption, signing, and token keys need explicit lifecycle policy
- key generation, storage, activation, deactivation, and destruction should be
  intentional
- do not mix encryption keys, signing keys, and application secrets casually
- cryptographic algorithm choices must be current and reviewable
- key identifiers and rotation evidence should be available without exposing raw
  key material

## CI/CD And Local Development

- pipeline secrets must not be over-shared across unrelated jobs or branches
- local `.env` style files are allowed only as repository-local convenience,
  never as committed truth
- secret scanning should run on source and pipeline input where practical
- secret access by CI runners should be short-lived and environment-scoped
- generated config files containing secret material must be ephemeral or tightly
  controlled

## Logging And Evidence Rules

- secret values, raw tokens, private keys, and sensitive credentials must be
  redacted from logs, traces, errors, and evidence artifacts
- evidence should show that secret posture was checked without reproducing the
  secret itself
- incident timelines should capture which secret class was affected and what
  revocation action was taken
