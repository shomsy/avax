# InvocationExecutor

## Quick Summary
- Reflects a callable target and resolves its parameters using the container.
- Supports multiple callable forms: closures, functions, `Class::method`, `[object, method]`, invokable objects, and `Class@method` shorthand.
- Preserves `KernelContext` chains so nested resolutions can detect circular dependencies.

### For Humans: What This Means
It’s the “call it for me” engine: it figures out what arguments a callable needs, pulls them from the container (or your overrides), then executes the callable.

## Terminology
- **Callable**: Anything PHP can call: closures, functions, methods, invokable objects.
- **Reflection**: PHP introspection used to read parameters/types.
- **InvocationContext**: Immutable object that carries target, reflection, arguments, and result through invocation.
- **ParameterPrototype**: Simple model describing a parameter (name, type, default, nullability).
- **ReflectionCache**: Local cache mapping targets to reflection objects to avoid repeated reflection.
- **Class@method**: String shorthand meaning “resolve class from container, then call method.”

### For Humans: What This Means
Reflection is how it reads what the callable needs. InvocationContext is the “invocation record.” Prototypes are simplified parameter descriptions. Cache makes it faster. `Class@method` is a shortcut for container-resolved method calls.

## Think of It
Like a stage manager: you hand them a script (callable). They read which actors are needed (parameters), fetch them from backstage (container), then start the scene (invoke).

### For Humans: What This Means
You don’t hand-wire the cast; the system does it.

## Story Example
You want to invoke `App\Handler\UserHandler@handle(Request $request, LoggerInterface $logger)`. The executor sees `Class@method`, resolves the handler instance from the container, reflects `handle()`, resolves `Request` and `LoggerInterface`, and invokes the method with those arguments.

### For Humans: What This Means
You can call a handler by name and the container supplies the dependencies automatically.

## For Dummies
1. Normalize the target (especially if it’s `Class@method`).
2. Reflect the callable to get parameters.
3. Build parameter prototypes.
4. Resolve argument values using overrides first and then container resolution.
5. Invoke the callable.

Common misconceptions:
- “It only supports closures.” It supports many callable formats.
- “It ignores context.” It creates a child `KernelContext` to preserve depth/trace.

### For Humans: What This Means
It’s a general-purpose invoker that still plays nicely with kernel context and guards.

## How It Works (Technical)
`execute()` normalizes targets, fetches reflection (cached), builds a `KernelContext` for the invocation, resolves parameters via `DependencyResolverInterface`, stores resolved arguments in `InvocationContext`, and calls `invoke()`. The reflection cache key is derived from the callable form, and type resolution chooses the first non-built-in type in union types.

### For Humans: What This Means
It standardizes all callable types into one flow: normalize, inspect, resolve args, invoke.

## Architecture Role
This is an action-level utility used by `InvokeAction` and potentially other features needing callables invoked with DI. It depends on the container and the dependency resolver, but stays independent from kernel pipeline steps.

### For Humans: What This Means
It’s a reusable tool for “smart calling” that the rest of the container can rely on.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: execute(InvocationContext $context, array $parameters = [], ?KernelContext $parentContext = null): mixed

#### Technical Explanation
Normalizes the target, resolves reflection, builds parameter prototypes, resolves arguments with the resolver, stores resolved args in the context, and invokes the target.

##### For Humans: What This Means
This is the main entry point: give it a target and optional overrides, and it calls it.

##### Parameters
- `InvocationContext $context`: Invocation state including the target.
- `array $parameters`: Override values keyed by parameter name.
- `?KernelContext $parentContext`: Optional parent context for trace/depth preservation.

##### Returns
- `mixed`: Whatever the callable returns.

##### Throws
- `ReflectionException` when reflection cannot be created.
- Container exceptions when `Class@method` normalization needs container resolution.

##### When to Use It
Used by `InvokeAction` whenever a callable must be invoked with DI.

##### Common Mistakes
Providing overrides with wrong keys; they must match parameter names.

### Method: normalizeTarget(InvocationContext $context, ?KernelContext $parentContext): InvocationContext

#### Technical Explanation
Transforms `Class@method` string targets into `[instance, method]` by resolving the class instance from the container.

##### For Humans: What This Means
It turns shortcut strings into real callable arrays.

##### Parameters
- `InvocationContext $context`: Current invocation state.
- `?KernelContext $parentContext`: Parent context (currently only carried through).

##### Returns
- `InvocationContext`: New context with normalized target.

##### Throws
- `InvalidArgumentException` for malformed `Class@method` strings.

##### When to Use It
Internal helper before reflection.

##### Common Mistakes
Using `@` in targets unintentionally; only intended for `Class@method`.

### Method: getReflection(mixed $target): ReflectionFunctionAbstract

#### Technical Explanation
Fetches reflection from cache or builds it and stores it.

##### For Humans: What This Means
It avoids re-inspecting the same callable repeatedly.

##### Parameters
- `mixed $target`: Callable target.

##### Returns
- `ReflectionFunctionAbstract`: Reflection for the callable.

##### Throws
- `ReflectionException`

##### When to Use It
Internal.

##### Common Mistakes
Assuming closures cache across requests; cache is in-memory for this executor instance.

### Method: buildCacheKey(mixed $target): string

#### Technical Explanation
Builds a stable cache key for common callable types.

##### For Humans: What This Means
It creates a unique “name” to store reflection under.

##### Parameters
- `mixed $target`

##### Returns
- `string`

##### Throws
- None.

##### When to Use It
Internal.

##### Common Mistakes
Using non-deterministic identifiers for closures; it uses `spl_object_id`.

### Method: createReflection(mixed $target): ReflectionFunctionAbstract

#### Technical Explanation
Creates an appropriate reflection object depending on callable type.

##### For Humans: What This Means
It turns “callable shapes” into a reflection you can inspect.

##### Parameters
- `mixed $target`

##### Returns
- `ReflectionFunctionAbstract`

##### Throws
- `ReflectionException` or `InvalidArgumentException`.

##### When to Use It
Internal.

##### Common Mistakes
Passing unsupported callable types.

### Method: buildParameterPrototypes(array $parameters): array

#### Technical Explanation
Builds `ParameterPrototype` objects from reflection parameters, including type/name/default/variadic/nullability.

##### For Humans: What This Means
It converts raw reflection parameters into a simpler model the resolver understands.

##### Parameters
- `array $parameters`: Reflection parameters.

##### Returns
- `ParameterPrototype[]`

##### Throws
- None.

##### When to Use It
Internal.

##### Common Mistakes
Assuming built-in types will resolve via container; only class/interface/enum types become resolvable types.

### Method: resolveType(?ReflectionType $type): ?string

#### Technical Explanation
Returns the first non-built-in type name (supports union types) or null.

##### For Humans: What This Means
It picks the injectable type from a parameter’s type hint.

##### Parameters
- `?ReflectionType $type`

##### Returns
- `?string`

##### Throws
- None.

##### When to Use It
Internal.

##### Common Mistakes
Union types with only built-ins resolve to null.

### Method: invoke(InvocationContext $context): mixed

#### Technical Explanation
Invokes the reflected callable using resolved arguments.

##### For Humans: What This Means
It actually runs the callable.

##### Parameters
- `InvocationContext $context`

##### Returns
- `mixed`

##### Throws
- `ReflectionException`

##### When to Use It
Internal.

##### Common Mistakes
Attempting to invoke without reflection/args; it returns null in that case.

### Method: resolveInvocationObject(InvocationContext $context): mixed

#### Technical Explanation
Extracts the object instance used for method invocation when reflection is a method.

##### For Humans: What This Means
It figures out which object to call the method on.

##### Parameters
- `InvocationContext $context`

##### Returns
- `mixed`: Object instance or null.

##### Throws
- None.

##### When to Use It
Internal.

##### Common Mistakes
Assuming static method invocation provides an object; it returns null for class strings.

## Risks, Trade-offs & Recommended Practices
- **Risk: Reflection overhead**. Use caching and keep executor reused.
- **Risk: Hidden dependencies**. Autowiring callables can hide required args; document callable signatures.
- **Practice: Use overrides for non-container values**. Don’t try to resolve scalars from container.
- **Practice: Preserve context**. Pass parent context when invoking from within resolution.

### For Humans: What This Means
It’s powerful but can hide what’s being passed. Use overrides for things like IDs/strings, and keep context chains for safe resolution.

## Related Files & Folders
- `docs_md/Features/Actions/Invoke/index.md`: Folder overview.
- `docs_md/Features/Actions/Invoke/Core/InvokeAction.md`: High-level entry point.
- `docs_md/Features/Actions/Invoke/Context/InvocationContext.md`: Invocation state object.
- `docs_md/Features/Actions/Invoke/Cache/ReflectionCache.md`: Reflection cache.
- `docs_md/Features/Actions/Resolve/DependencyResolver.md`: Parameter resolver.

### For Humans: What This Means
Start at InvokeAction, then see how InvocationExecutor does reflection and uses DependencyResolver to supply arguments.
