# Injection and Instantiation

Instantiation and injection are different responsibilities.

## Instantiation

Instantiation creates the object itself.

Main units:

- `DependencyInjection/Capability/Resolution/Engine/Instantiator.php`
- `DependencyInjection/Capability/Resolution/Engine/DependencyResolver.php`
- `DependencyInjection/Capability/Prototypes/*`

This phase decides constructor arguments and creates the instance.

## Injection

Injection wires dependencies after the object already exists.

Main units:

- `DependencyInjection/Capability/Injection/InjectDependencies.php`
- `DependencyInjection/Capability/Injection/Properties/PropertyInjector.php`
- `DependencyInjection/Capability/Injection/Methods/MethodInjector.php`
- `DependencyInjection/Capability/Injection/Parameters/ResolveMethodParameters.php`
- `DependencyInjection/Capability/Invocation/InvokeAction.php`
- `DependencyInjection/Capability/Invocation/CallableInvocation/InvocationExecutor.php`

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
