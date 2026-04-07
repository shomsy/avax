# Injection and Instantiation

Instantiation and injection are different responsibilities.

## Instantiation

Instantiation creates the object itself.

Main units:

- `DependencyInjection/Capabilities/Resolution/Engine/Instantiator.php`
- `DependencyInjection/Capabilities/Resolution/Engine/DependencyResolver.php`
- `DependencyInjection/Capabilities/Prototypes/*`

This phase decides constructor arguments and creates the instance.

## Injection

Injection wires dependencies after the object already exists.

Main units:

- `DependencyInjection/Capabilities/Injection/InjectDependencies.php`
- `DependencyInjection/Capabilities/Injection/Properties/PropertyInjector.php`
- `DependencyInjection/Capabilities/Injection/Methods/MethodInjector.php`
- `DependencyInjection/Capabilities/Injection/Parameters/ResolveMethodParameters.php`
- `DependencyInjection/Capabilities/Invocation/InvokeAction.php`
- `DependencyInjection/Capabilities/Invocation/CallableInvocation/InvocationExecutor.php`

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
