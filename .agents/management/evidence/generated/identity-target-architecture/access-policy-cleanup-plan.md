# Access Policy Cleanup Plan

Date: 2026-05-21
Branch: architecture/identity-target-architecture

## Scope

In scope:

- Ensure `EndpointPostureEngine.php` contains only `EndpointPostureEngine`.
- Ensure `PolicyRule.php` contains only `PolicyRule`.
- Ensure `AttributeCondition.php` is already its own file and does not use `date('H')`.
- Remove duplicate `EndpointPostureSignalData` definition from `EndpointPostureSignal.php`.
- Document `AccessPolicy` ownership between the legacy contract and the canonical policy value object.
- Add focused characterization coverage for endpoint posture one-class-per-file ownership.
- Run available static validation.

Out of scope:

- Full Access runtime delegation redesign.
- AuthBuilder changes.
- Removing public legacy `AccessPolicy` interface without a compatibility decision.
- Full PHPUnit GREEN claim while Docker socket blocks PHP execution.

## Expected Status

PARTIAL_WITH_ENVIRONMENT_YELLOW unless PHP execution becomes available.
