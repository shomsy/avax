# InvokeAction

## Quick Summary
- Provides a high-level API to invoke callables with automatic dependency resolution.
- Lazily wires an `InvocationExecutor` once a `ContainerInternalInterface` is available.
- Protects callers from using invocation before wiring by failing fast with a clear runtime error.

### For Humans: What This Means
This is the “smart call” button you press. It makes sure the invocation engine is wired to the container, then runs your callable with autowired arguments.

## Terminology
- **Invoke action**: A reusable action object that performs an operation (invoking callables).
- **InvocationExecutor**: The underlying engine that reflects, resolves args, and invokes.
- **ContainerInternalInterface**: Internal container API used for context-aware resolutions.
- **Wiring**: Initializing the executor with the container and resolver.

### For Humans: What This Means
InvokeAction is the facade; executor does the hard work; internal container supports advanced resolution; wiring means “hook it all together.”

## Think of It
Like a power tool with a safety switch: you can’t use it until it’s plugged in. Once plugged in (wired), it drills (invokes) reliably.

### For Humans: What This Means
It prevents you from invoking before the tool is ready.

## Story Example
The kernel wants to call a post-construct hook. It calls `InvokeAction->invoke([$object, 'init'])` and passes the current `KernelContext`. InvokeAction delegates to the executor, which resolves arguments and calls the method.

### For Humans: What This Means
The kernel can safely call methods with DI without manually building argument lists.

## For Dummies
1. Ensure the container is set (either via constructor or `setContainer`).
2. Call `invoke($target, $parameters, $context)`.
3. The action resolves arguments and runs the callable.

Common misconceptions:
- “It’s always ready.” It needs container wiring first.

### For Humans: What This Means
If you see “executor not initialized,” it means you forgot to wire the container.

## How It Works (Technical)
The constructor optionally wires the executor when a container is provided. `setContainer` wires it later. `invoke` creates an `InvocationContext` and asks `InvocationExecutor::execute` to normalize the target, reflect it, resolve args, and invoke.

### For Humans: What This Means
It just ensures the executor exists and then delegates.

## Architecture Role
This is the stable API that other features and kernel steps should depend on instead of instantiating executors directly. It depends on the resolver subsystem and the internal container interface.

### For Humans: What This Means
Use this action as the standard way to invoke callables with DI.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(?ContainerInternalInterface $container, DependencyResolverInterface $resolver)

#### Technical Explanation
Stores the container and resolver and wires an `InvocationExecutor` when the container is available.

##### For Humans: What This Means
It plugs the invoker into the container if it can.

##### Parameters
- `?ContainerInternalInterface $container`: Container for dependency resolution.
- `DependencyResolverInterface $resolver`: Parameter resolver.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Constructed during container boot.

##### Common Mistakes
Passing null container and forgetting to call `setContainer` later.

### Method: setContainer(ContainerInternalInterface $container): void

#### Technical Explanation
Stores the container and wires a new executor.

##### For Humans: What This Means
This is how you "plug it in" after construction.

##### Parameters
- `ContainerInternalInterface $container`

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
When boot order means the action is created before the container exists.

##### Common Mistakes
Calling `invoke` before calling `setContainer`.

### Method: invoke(callable|string $target, array $parameters = [], ?KernelContext $context = null): mixed

#### Technical Explanation
Ensures the executor exists, creates an `InvocationContext`, and delegates to the executor to resolve args and invoke the target.

##### For Humans: What This Means
Calls the thing you asked to call, with arguments autowired.

##### Parameters
- `callable|string $target`: Callable or string target (`Class@method`, `Class::method`, function name).
- `array $parameters`: Overrides keyed by parameter name.
- `?KernelContext $context`: Optional kernel context.

##### Returns
- `mixed`: Callable return value.

##### Throws
- `RuntimeException` when the executor is not wired.
- `ReflectionException` when reflection fails.

##### When to Use It
When you need DI-aware callable invocation.

##### Common Mistakes
Using it to resolve scalars without overrides.

## Risks, Trade-offs & Recommended Practices
- **Risk: Wiring order**. If you invoke too early, you get runtime errors; wire container first.
- **Practice: Pass context**. When invoking inside resolution, pass the current `KernelContext` so depth/trace are preserved.

### For Humans: What This Means
Plug it in before use, and pass the context when you’re inside the container pipeline.

## Related Files & Folders
- `docs_md/Features/Actions/Invoke/index.md`: Invoke subsystem overview.
- `docs_md/Features/Actions/Invoke/InvocationExecutor.md`: Underlying executor.
- `docs_md/Features/Actions/Invoke/Context/InvocationContext.md`: Invocation state.

### For Humans: What This Means
If you want to understand what `invoke()` really does, follow the executor and context docs.
