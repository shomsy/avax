# Policies and Guards

The container has one policy file:

- `DependencyInjection/Dependencies/Resolution/ResolutionPolicy.php`

## What It Owns

`ResolutionPolicy` decides whether a requested id is allowed to flow into autowiring.

Today the main switch is `strict`:

- relaxed mode: unknown aliases may continue deeper into resolution
- strict mode: only real classes and interfaces are allowed through policy

## Where It Is Enforced

Policy is enforced in:

- `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`

There is no separate policy pipeline, guard folder, or kernel stage map in the shipped architecture.
