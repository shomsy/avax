# Identity Reference Package Evidence

Status: YELLOW_REFERENCE_IMPLEMENTATION

## What this package proves

- The target Identity architecture can be expressed as explicit runtime graphs.
- PublicSurface can stay thin and delegate-only.
- Runtime state can be reset through one reset flow.
- Password authentication, permission authorization, session creation, token issuing, token verification, external identity resolution and tenant context flows can remain separate and testable.

## What this package does not prove

- Compatibility with the existing AvaX container API.
- Compatibility with every current Identity public API.
- PHPStan correctness inside the real AvaX repository.
- Full security coverage for production password/token/session policy.

## Required real-repo validation

- AvaX governance checks.
- AvaX PHPStan profile.
- AvaX PHPUnit full suite.
- Identity migration map.
- Security review.
- Runtime worker reset proof.
