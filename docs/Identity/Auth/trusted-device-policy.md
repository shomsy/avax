# Trusted Device Policy

This package explicitly does **not** ship a remembered-device or trusted-device
feature at this time.

## Decision

Trusted-device behavior is intentionally rejected for the current package scope.

## Reason

- it weakens the guarantee that phishing-resistant and fresh-factor policies are
  enforced consistently
- it introduces a new revocation surface and device-token lifecycle that should
  not be half-owned
- it creates ambiguity in UX if the application cannot clearly explain why MFA
  is bypassed on one device but not another

## Product Rule

- documentation, examples, and policies must not imply that remembered-device
  bypass exists
- applications that want device trust must implement a separate, explicit
  device-binding subsystem with its own audit, revocation, and suspicious-device
  re-challenge model

## Revisit Trigger

Re-open this decision only when the package can own:

- device-token issuance
- device binding
- per-device revocation
- suspicious-device re-challenge
- device audit trail
