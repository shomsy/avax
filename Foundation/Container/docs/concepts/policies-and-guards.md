# Policies and Guards

Policy checks decide whether a resolution request is allowed.

## Main Units

- `DependencyInjection/Capabilities/Policies/ContainerPolicy.php`
- `DependencyInjection/Capabilities/Policies/ResolutionPolicy.php`
- `DependencyInjection/Capabilities/Policies/StrictResolutionPolicy.php`
- `DependencyInjection/Capabilities/Policies/CompositeResolutionPolicy.php`
- `DependencyInjection/Capabilities/Policies/CheckResolutionPolicy.php`
- `DependencyInjection/Capabilities/Resolution/Pipeline/Steps/EnforcePolicyStep.php`

## Current Shape

The old `Guard/*` language is gone.

Policy is now an explicit capability slice. The pipeline consumes it through `EnforcePolicyStep`.

## Ownership Rule

- policy rules live in `DependencyInjection/Capabilities/Policies`
- enforcement during resolution lives in the resolution pipeline

That split keeps the policy decision reusable while keeping pipeline ownership explicit.
