# Policies and Guards

Policy checks decide whether a resolution request is allowed.

## Main Units

- `DependencyInjection/Capability/Policies/ContainerPolicy.php`
- `DependencyInjection/Capability/Policies/ResolutionPolicy.php`
- `DependencyInjection/Capability/Policies/StrictResolutionPolicy.php`
- `DependencyInjection/Capability/Policies/CompositeResolutionPolicy.php`
- `DependencyInjection/Capability/Policies/CheckResolutionPolicy.php`
- `DependencyInjection/Capability/Resolution/Pipeline/Steps/EnforcePolicyStep.php`

## Current Shape

The old `Guard/*` language is gone.

Policy is now an explicit capability slice. The pipeline consumes it through `EnforcePolicyStep`.

## Ownership Rule

- policy rules live in `DependencyInjection/Capability/Policies`
- enforcement during resolution lives in the resolution pipeline

That split keeps the policy decision reusable while keeping pipeline ownership explicit.
