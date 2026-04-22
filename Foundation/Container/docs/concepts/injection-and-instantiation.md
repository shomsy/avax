# Injection and Instantiation

Instantiation and injection are separate on purpose.

## Instantiation

Instantiation creates the object itself.

Main files:

- `src/Capabilities/Execution/BuildService.php`
- `src/Capabilities/Declaration/Blueprints/CreateServiceBlueprint.php`
- `src/Capabilities/Resolution/ResolveDependencies.php`
- `src/Capabilities/Declaration/Blueprints/ServiceBlueprint.php`

Constructor arguments are resolved first. If the object cannot be created, the resolver fails before any late wiring
starts.

Scalar arguments can also come from a `forContext()` view. The same context fallback works in both the dynamic resolver
and the compiled runtime path.

## Injection

Injection happens after the object exists.

Main files:

- `src/Capabilities/Execution/Injection/Properties/InjectProperties.php`
- `src/Capabilities/Execution/Injection/Methods/InjectMethods.php`
- `src/Capabilities/Execution/Injection/Reports/InjectionReport.php`
- `src/Capabilities/Execution/Injection/Invocation/ResolveCallArguments.php`
- `src/Capabilities/Execution/Injection/Invocation/FunctionCaller.php`

Current rules:

- injectable properties and methods are marked with `#[Inject]`
- property injection and method injection stay separate
- callable argument resolution stays inside the invocation area, not in the facade
- context values are named fallbacks for scalar arguments, not a second binding system

## Why The Split Matters

- constructor failure is different from late wiring failure
- reflection discovery stays reusable
- callable invocation and object injection do not collapse into one generic helper
