# ContainerInterface

## Quick Summary
- Defines the runtime-facing API of the container: resolve services, call callables, inject into objects, manage scopes, and register instances.
- Extends PSR-11 `ContainerInterface` for interoperability.
- Exists to separate “what the container does at runtime” from how it’s implemented.

### For Humans: What This Means (Summary)
This interface is the main “remote control” you use to interact with the container.

## Terminology (MANDATORY, EXPANSIVE)- **PSR-11**: Standard container interface (`get`, `has`).
- **Make**: Resolve/build a service with optional overrides.
- **Call**: Invoke a callable with autowired parameters.
- **InjectInto**: Populate an existing object with dependencies.
- **Scope**: Boundary for scoped services.
- **Instance registration**: Register an already-created object under a service ID.

### For Humans: What This Means
It defines the main actions you can do: get services, call functions, inject objects, manage scopes, and register instances.

## Think of It
Like a universal remote for your app’s wiring: it lets you request a component, call a handler, or connect cables to an object.

### For Humans: What This Means (Think)
It’s the API you reach for when you need the container to do something.

## Story Example
A controller needs a service with an override for a single parameter. It calls `$container->make(UserService::class, ['tenantId' => $tenantId])`. Later, it calls `$container->call([Handler::class, 'handle'])` to run a method with DI.

### For Humans: What This Means (Story)
You can resolve services and call handlers without manual argument lists.

## For Dummies
- Use `get()` for PSR-11 retrieval.
- Use `make()` when you need overrides.
- Use `call()` when you want to invoke something with DI.
- Use `injectInto()` to wire an object you already built.
- Use `beginScope()`/`endScope()` for scoped services.

### For Humans: What This Means (Dummies)
It’s a toolbox of the most common container operations.

## How It Works (Technical)
This is a contract only. Concrete implementations typically route `make` to resolution engines, route `call` to invocation executors, and route `injectInto` to injection actions while respecting kernel contexts and lifecycle policies.

### For Humans: What This Means (How)
The interface doesn’t do work; implementations do—this just defines the shape.

## Architecture Role
Central runtime contract used across the component and by external code. Higher layers should depend on this interface, not a concrete container class.

### For Humans: What This Means (Role)
Depending on an interface keeps your code flexible and testable.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: make(string $abstract, array $parameters = []): object

#### Technical Explanation (make)
Resolves a service by identifier with optional parameter overrides.

##### For Humans: What This Means (make)
Get a service, and optionally override a few inputs.

##### Parameters (make)
- `string $abstract`: Service ID.
- `array $parameters`: Overrides.

##### Returns (make)
- `object`

##### Throws (make)
- Container resolution exceptions when build fails.

##### When to Use It (make)
When you need overrides.

##### Common Mistakes (make)
Providing overrides that don’t match parameter names.

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation (call)
Invokes a callable with dependency resolution.

##### For Humans: What This Means (call)
Run a function/method and let the container fill in dependencies.

##### Parameters (call)
- `callable|string $callable`
- `array $parameters`

##### Returns (call)
- `mixed`

##### Throws (call)
- Invocation/reflection exceptions.

##### When to Use It (call)
Invoking handlers.

##### Common Mistakes (call)
Expecting scalars to be resolved without overrides.

### Method: injectInto(object $target): object

#### Technical Explanation (injectInto)
Injects dependencies into an existing instance.

##### For Humans: What This Means (injectInto)
Finish wiring an object you already have.

##### Parameters (injectInto)
- `object $target`

##### Returns (injectInto)
- `object`

##### Throws (injectInto)
- Injection exceptions.

##### When to Use It (injectInto)
Objects created outside the container.

##### Common Mistakes (injectInto)
Calling it when container isn’t fully wired.

### Method: canInject(object $target): bool

#### Technical Explanation (canInject)
Checks whether injection is possible without mutating.

##### For Humans: What This Means (canInject)
Ask “can the container wire this object?”

##### Parameters (canInject)
- `object $target`

##### Returns (canInject)
- `bool`

##### Throws (canInject)
- None.

##### When to Use It (canInject)
Pre-flight checks.

##### Common Mistakes (canInject)
Assuming true means no runtime errors; it’s a best-effort check.

### Method: beginScope(): void

#### Technical Explanation (beginScope)
Starts a new scope for scoped services.

##### For Humans: What This Means (beginScope)
Begin a “scoped lifetime window.”

##### Parameters (beginScope)
- None.

##### Returns (beginScope)
- `void`

##### Throws (beginScope)
- None.

##### When to Use It (beginScope)
At request/job start.

##### Common Mistakes (beginScope)
Forgetting to end scopes.

### Method: endScope(): void

#### Technical Explanation (endScope)
Ends current scope and disposes scoped services.

##### For Humans: What This Means (endScope)
Close the scope and clean up.

##### Parameters (endScope)
- None.

##### Returns (endScope)
- `void`

##### Throws (endScope)
- None.

##### When to Use It (endScope)
At request/job end.

##### Common Mistakes (endScope)
Ending scope too early.

### Method: instance(string $abstract, object $instance): void

#### Technical Explanation (instance)
Registers an existing object as a shared service.

##### For Humans: What This Means (instance)
Tell the container “use this object for that ID.”

##### Parameters (instance)
- `string $abstract`
- `object $instance`

##### Returns (instance)
- `void`

##### Throws (instance)
- None.

##### When to Use It (instance)
Bridging legacy objects.

##### Common Mistakes (instance)
Registering mutable per-request objects as global instances.

## Risks, Trade-offs & Recommended Practices
- **Risk: Overusing `call` and `injectInto`**. Can hide dependencies; prefer explicit wiring for critical code.
- **Practice: Use scopes correctly**. Scoped lifetimes only work when you manage scope boundaries.

### For Humans: What This Means (Risks)
Use powerful features carefully, and be disciplined about scope lifecycle.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/ContainerInternalInterface.md`: Internal extension.
- `docs_md/Features/Actions/Resolve/Engine.md`: Core resolution logic.
- `docs_md/Features/Actions/Invoke/Core/InvokeAction.md`: Invocation logic.
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Injection logic.

### For Humans: What This Means (Related)
This interface delegates into resolve/invoke/inject subsystems.
