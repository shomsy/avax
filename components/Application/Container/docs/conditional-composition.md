# Conditional Composition

Conditional composition is explicit per registration.

## Supported Conditions

A `ServiceRegistration` can restrict itself by:

- `profiles([...])`
- `flags([...])`
- `tenants([...])`
- `regions([...])`
- `modes([...])`

These conditions are part of authored ownership metadata, not runtime guesswork.

## Active Composition Inputs

The runtime reads the current composition from `ContainerSettings`:

- `app_env`
- `composition.flags`
- `composition.tenant`
- `composition.region`
- `composition.mode`

The same values are reflected in diagnostics so operators can see why something is active or inactive.

## Explainability

Use:

- `describeService()['conditions']`
- `debugGraph()['conditions']`
- `validate()`

These surfaces explain:

- whether a registration is active
- which environment, flags, tenant, region, and mode are currently active
- the exact reasons a registration is inactive

Runtime failures for inactive registrations are fix-oriented and include the dependency path plus likely fixes.

## Override And Collision Rules

The registry now keeps an override history for rebound abstracts.

Diagnostics expose it through:

- `describeService()['overrides']`
- `debugGraph()['overrides']`

Validation rejects overlapping overrides when they silently change:

- owner slice
- category
- visibility

That keeps conditional overrides from silently violating ownership rules.

## Honest Boundary

This container does **not** silently pick between multiple overlapping implementations for the same abstract at runtime.

The supported model is:

- one authored binding per abstract at a time
- explicit composition conditions on that binding
- explicit override history and collision diagnostics when the binding is replaced

If a product needs multi-candidate selection under one abstract, that decision should live in authored composition, not
hidden runtime magic.
