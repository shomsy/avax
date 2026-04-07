# Injection and Instantiation

Instantiation and injection are separate on purpose.

## Instantiation

Instantiation creates the object itself.

Main files:

- `Resolution/BuildService.php`
- `Resolution/CreateServiceBlueprint.php`
- `Resolution/ResolveDependencies.php`
- `Resolution/ServiceBlueprint.php`

Constructor arguments are resolved first. If the object cannot be created, the resolver fails before any late wiring starts.

## Injection

Injection happens after the object exists.

Main files:

- `Injection/InjectProperties.php`
- `Injection/InjectMethods.php`
- `Injection/InjectionReport.php`
- `Calls/ResolveCallArguments.php`
- `Calls/FunctionCaller.php`

Current rules:

- injectable properties and methods are marked with `#[Inject]`
- property injection and method injection stay separate
- callable argument resolution stays in `Calls/`, not in the facade

## Why The Split Matters

- constructor failure is different from late wiring failure
- reflection discovery stays reusable
- callable invocation and object injection do not collapse into one generic helper
