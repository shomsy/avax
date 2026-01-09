# DependencyResolverInterface

## Quick Summary
- Defines the contract for resolving a list of parameter prototypes into an ordered argument array.
- Keeps instantiation, injection, and invocation decoupled from a specific resolver implementation.

### For Humans: What This Means
It’s the contract for “given these parameters, build me the argument list.”

## Terminology
- **Parameter list**: Prototypes describing what arguments are needed.
- **Overrides**: Caller-provided values keyed by parameter name.
- **Container**: Source for class/interface/enum dependencies.
- **KernelContext**: Optional chain info to preserve depth and circular guards.

### For Humans: What This Means
Parameters describe needs, overrides supply manual values, container supplies services, context keeps the resolution stack.

## Think of It
Like a recipe assistant: it reads the ingredient list (parameters), uses what you already have (overrides), and fetches the rest from the pantry (container).

### For Humans: What This Means
It builds the final ingredient list for cooking.

## Story Example
`InvocationExecutor` needs to call a callable. It asks the dependency resolver to turn parameter prototypes into actual argument values. Swapping resolver implementations changes resolution behavior without touching invocation code.

### For Humans: What This Means
Invocation stays the same even if your parameter resolution rules change.

## For Dummies
Implement `resolveParameters(...)` to return the argument list in the correct order.

### For Humans: What This Means
One method contract: resolve arguments.

## How It Works (Technical)
Declares `resolveParameters(array $parameters, array $overrides, ContainerInterface $container, ?KernelContext $context): array`.

### For Humans: What This Means
Given prototypes + overrides + container + optional context, return a list of values.

## Architecture Role
Used across instantiation, invocation, and method injection as the shared mechanism for building argument lists.

### For Humans: What This Means
It’s reused everywhere you need to fill parameters.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: resolveParameters(array $parameters, array $overrides, ContainerInterface $container, ?KernelContext $context): array

#### Technical Explanation
Resolves each parameter based on overrides, container resolution, defaults/nullability, and requiredness.

##### For Humans: What This Means
Builds the argument list.

##### Parameters
- `array $parameters`: Parameter prototypes.
- `array $overrides`: Override values.
- `ContainerInterface $container`: Container used to resolve dependencies.
- `?KernelContext $context`: Context for nested resolution.

##### Returns
- `array`: Ordered arguments.

##### Throws
- Implementation-specific resolution exceptions.

##### When to Use It
Called wherever argument lists are needed.

##### Common Mistakes
Returning associative arrays instead of ordered lists.

## Risks, Trade-offs & Recommended Practices
- **Practice: Preserve ordering**. Callers expect positional arrays.

### For Humans: What This Means
Order matters: arguments must match the callable signature.

## Related Files & Folders
- `docs_md/Features/Actions/Resolve/Contracts/index.md`: Contracts overview.
- `docs_md/Features/Actions/Resolve/DependencyResolver.md`: Default implementation.

### For Humans: What This Means
See the default resolver to understand the typical resolution rules.
