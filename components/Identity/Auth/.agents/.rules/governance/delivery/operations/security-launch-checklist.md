# Security Launch Checklist

Version: 1.0.0
Status: Normative

Use this checklist before a security-sensitive launch, exposure increase, or
production-ready claim.

## Trust And Session Strategy

The adopting repository must define:

- how identity is presented
- whether browsers attach credentials automatically
- whether CSRF protection is required
- whether origin or referer checks are required
- what revocation or expiry model exists

If session cookies or browser-attached credentials exist, CSRF posture must be
explicit and testable.

## Launch Checklist

- production environment guards pass
- security middleware or equivalent baseline is active
- protected routes or commands enforce auth and authorization
- least-privilege defaults are preserved
- trust boundaries do not silently downgrade in production
- migration or schema posture is current
- CI and required security gates are green
- staging or release smoke for protected surfaces is complete

## Secrets Checklist

- no secrets in source control
- runtime secrets are injected through bound configuration
- secret rotation process is documented and testable
- emergency revocation or invalidation path is defined
- secrets do not leak into logs, reports, or evidence artifacts
