# Policies and Guards

Policy checks decide whether a resolution request is allowed.

## Main Units

- `Capabilities/Policies/ContainerPolicy.php`
- `Capabilities/Policies/ResolutionPolicy.php`
- `Capabilities/Policies/StrictResolutionPolicy.php`
- `Capabilities/Policies/CompositeResolutionPolicy.php`
- `Capabilities/Policies/CheckResolutionPolicy.php`
- `Capabilities/Resolution/Pipeline/Steps/EnforcePolicyStep.php`

## Current Shape

The old `Guard/*` language is gone.

Policy is now an explicit capability slice. The pipeline consumes it through `EnforcePolicyStep`.

## Ownership Rule

- policy rules live in `Capabilities/Policies`
- enforcement during resolution lives in the resolution pipeline

That split keeps the policy decision reusable while keeping pipeline ownership explicit.
