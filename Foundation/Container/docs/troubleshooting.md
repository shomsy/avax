# Troubleshooting

## Service Not Found

Check these in order:

1. Is the service explicitly bound in `RegisterBindings`?
2. Is the class instantiable?
3. Is strict mode blocking fallback autowiring?
4. Is there a contextual binding changing the expected concrete?

## Scope Surprises

If a value appears reused unexpectedly:

1. Check the lifetime on the definition
2. Check whether a scope is already open
3. Check whether `instance()` or singleton registration stored a global object

## Property Injection Missing

Check:

1. the property prototype is marked injectable
2. the property type can be resolved
3. the property is not readonly
4. the container instance was wired into the injection capability

## Callable Invocation Fails

Check:

1. whether the target is a valid PHP callable or `Class@method`
2. whether the target class itself can be resolved
3. whether parameter types are available in the container

## Provider Lifecycle Issues

Provider boot order is deterministic:

1. all providers `register()`
2. then all providers `boot()`

If boot fails, verify the dependency was registered during the register phase rather than lazily during boot.
