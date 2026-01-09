# DependencyResolver

## Quick Summary
- Resolves constructor/method parameter values using overrides, type-based container resolution, defaults, and nullability.
- Preserves `KernelContext` chains when available to detect circular dependencies and to resolve in child contexts.
- Throws clear `ResolutionException`/`ServiceNotFoundException` when required parameters can’t be resolved.

### For Humans: What This Means
This is the piece that fills the argument list for constructors and methods: it checks your overrides first, then asks the container for services by type, and only falls back to defaults/null when allowed.

## Terminology
- **Parameter prototype**: An object describing a parameter (name, type, default, required, allowsNull).
- **Overrides**: Values you supply keyed by parameter name.
- **Context-aware resolution**: Resolving dependencies using `KernelContext::child()` so the container can track depth and loops.
- **Circular dependency guard**: Detecting that the same type is being resolved again in the current chain.

### For Humans: What This Means
Parameter prototypes are the blueprint; overrides are your manual inputs; context-aware resolution keeps the stack; circular guard prevents loops.

## Think of It
Like a shopping list solver: if you already packed an item (override), use it. Otherwise, get it from the store (container). If it’s optional and missing, use the default or skip.

### For Humans: What This Means
Overrides win; container fills the rest; defaults/nulls apply only when allowed.

## Story Example
A constructor needs `LoggerInterface $logger` and `string $channel = 'app'`. Overrides provide `channel`. Resolver resolves `logger` from container, uses override for `channel`, and returns the argument array.

### For Humans: What This Means
You can control some arguments manually while still autowiring the rest.

## For Dummies
1. For each parameter:
   - If overrides contain its name, use that.
   - Else if it has a resolvable type, ask the container.
   - Else if it has a default, use the default.
   - Else if it allows null, use null.
   - Else if required, throw.
2. Return the resolved argument list.

Common misconceptions:
- “It resolves scalars from container.” It doesn’t; scalars need overrides/defaults.

### For Humans: What This Means
For strings/ints, you must provide overrides or defaults; the container typically resolves only class-like types.

## How It Works (Technical)
`resolveParameters` iterates parameters and calls `resolveParameter`. `resolveParameter` checks overrides, then type resolvability (class/interface/enum), then resolves via `ContainerInternalInterface::resolveContext` with a child context when possible, otherwise `ContainerInterface::get`. It throws for required unresolved parameters.

### For Humans: What This Means
It tries the safest and most explicit sources first, and uses the container only for resolvable types.

## Architecture Role
Shared helper used by instantiation and invocation subsystems. It’s the bridge between parameter prototypes and the container’s service graph.

### For Humans: What This Means
It’s the “argument builder” used in multiple places: constructors, method injection, invocation.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: resolveParameters(array $parameters, array $overrides, ContainerInterface $container, ?KernelContext $context): array

#### Technical Explanation
Resolves each parameter prototype into an argument value and returns an ordered argument array.

##### For Humans: What This Means
Builds the full argument list.

##### Parameters
- `array $parameters`: Parameter prototypes.
- `array $overrides`: Overrides keyed by parameter name.
- `ContainerInterface $container`: Container used to resolve types.
- `?KernelContext $context`: Context chain for nested resolution.

##### Returns
- `array`: Argument list in the same order as parameters.

##### Throws
- `ServiceNotFoundException`/`ResolutionException` when required parameters fail.

##### When to Use It
Used by `Instantiator`, `InvocationExecutor`, and method injection.

##### Common Mistakes
Providing overrides that don’t match parameter names.

## Risks, Trade-offs & Recommended Practices
- **Risk: Hidden parameter sources**. Overrides and defaults can hide where values come from; document your conventions.
- **Risk: Circular graphs**. Ensure context is passed so guard logic can detect loops.
- **Practice: Keep parameter names stable**. Overrides depend on names.

### For Humans: What This Means
Don’t make overrides depend on unstable naming, and always pass context when resolving inside the container.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/index.md`: Folder overview.
- `docs_md/Features/Actions/Instantiate/Instantiator.md`: Uses this to resolve constructor args.
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Uses this to resolve callable args.

### For Humans: What This Means
This resolver is reused across instantiation and invocation—read those to see where it fits.
