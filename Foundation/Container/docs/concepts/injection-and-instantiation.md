# Injection and Instantiation

Instantiation and injection are separate on purpose.

## Instantiation

Instantiation creates the object itself.

Main files:

- `DependencyInjection/Dependencies/Resolution/BuildService.php`
- `DependencyInjection/Dependencies/Blueprints/CreateServiceBlueprint.php`
- `DependencyInjection/Dependencies/Resolution/ResolveDependencies.php`
- `DependencyInjection/Dependencies/Blueprints/ServiceBlueprint.php`

Constructor arguments are resolved first. If the object cannot be created, the resolver fails before any late wiring starts.

## Injection

Injection happens after the object exists.

Main files:

- `DependencyInjection/Injection/Properties/InjectProperties.php`
- `DependencyInjection/Injection/Methods/InjectMethods.php`
- `DependencyInjection/Injection/Reports/InjectionReport.php`
- `DependencyInjection/Injection/Invocation/ResolveCallArguments.php`
- `DependencyInjection/Injection/Invocation/FunctionCaller.php`

Current rules:

- injectable properties and methods are marked with `#[Inject]`
- property injection and method injection stay separate
- callable argument resolution stays inside the invocation area, not in the facade

## Why The Split Matters

- constructor failure is different from late wiring failure
- reflection discovery stays reusable
- callable invocation and object injection do not collapse into one generic helper
