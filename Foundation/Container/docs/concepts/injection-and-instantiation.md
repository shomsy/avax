# Injection and Instantiation

Instantiation and injection are different responsibilities.

## Instantiation

Instantiation creates the object itself.

Main units:

- `Capabilities/Resolution/Engine/Instantiator.php`
- `Capabilities/Resolution/Engine/DependencyResolver.php`
- `Capabilities/Prototypes/*`

This phase decides constructor arguments and creates the instance.

## Injection

Injection wires dependencies after the object already exists.

Main units:

- `Capabilities/Injection/InjectDependencies.php`
- `Capabilities/Injection/Properties/PropertyInjector.php`
- `Capabilities/Injection/Methods/MethodInjector.php`
- `Capabilities/Injection/Parameters/ResolveMethodParameters.php`
- `Capabilities/Invocation/InvokeAction.php`
- `Capabilities/Invocation/CallableInvocation/InvocationExecutor.php`

This phase handles:

- injectable properties
- injectable methods
- method parameter lists for post-instantiation calls
- post-construct style callable execution

## Why The Split Exists

The split keeps ownership honest:

- constructor creation stays in resolution
- late wiring stays in injection/invocation

That makes failures easier to explain and keeps each unit narrow.
