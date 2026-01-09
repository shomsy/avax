# ResolverInterface

## Quick Summary
- Contract focused on service retrieval and callable invocation.
- Separates “getting services” from registration and compilation.
- Exists so code can depend on minimal resolution behavior without needing a full container.

### For Humans: What This Means
It’s the “I can fetch services and call things” interface.

## Terminology
- **Resolver**: Component that can provide services by ID.
- **Prototype resolution**: Resolving from an analyzed service blueprint.
- **Call invocation**: Running callables with autowired parameters.

### For Humans: What This Means
It’s the contract for looking things up and running callables using DI.

## Think of It
Like a vending machine: you ask for an ID and it gives you the item; it can also run a small “service” for you (call).

### For Humans: What This Means
It’s the minimal runtime capability.

## Story Example
A library only needs to `get()` services and `call()` handlers. It depends on `ResolverInterface`, not the full container, so it stays decoupled.

### For Humans: What This Means
Depending on a smaller interface keeps your code simpler and more portable.

## For Dummies
- Use `has` to check.
- Use `get` to retrieve.
- Use `call` to invoke.

### For Humans: What This Means
This is the minimal runtime API.

## How It Works (Technical)
Implementations follow PSR semantics and may delegate to internal engines, prototypes, and invocation executors.

### For Humans: What This Means
It’s an interface; actual behavior depends on the container implementation.

## Architecture Role
A contract for resolution-only dependencies.

### For Humans: What This Means
It helps keep dependencies minimal.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: has(string $id): bool

#### Technical Explanation
Checks if the ID can be resolved.

##### For Humans: What This Means
Ask “do you know this ID?”

##### Parameters
- `string $id`

##### Returns
- `bool`

##### Throws
- None.

##### When to Use It
Conditional resolution.

##### Common Mistakes
Assuming `has` guarantees `get` won’t fail.

### Method: get(string $id): mixed

#### Technical Explanation
Resolves and returns the service.

##### For Humans: What This Means
Give me the service.

##### Parameters
- `string $id`

##### Returns
- `mixed`

##### Throws
- `ContainerExceptionInterface`

##### When to Use It
Normal resolution.

##### Common Mistakes
Passing non-service literal IDs.

### Method: resolve(ServicePrototype $prototype): mixed

#### Technical Explanation
Resolves a pre-analyzed prototype into an instance.

##### For Humans: What This Means
Build from a blueprint.

##### Parameters
- `ServicePrototype $prototype`

##### Returns
- `mixed`

##### Throws
- `ContainerExceptionInterface`

##### When to Use It
Optimization paths.

##### Common Mistakes
Using stale prototypes.

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation
Invokes a callable with DI.

##### For Humans: What This Means
Run a function/method with autowired args.

##### Parameters
- `callable|string $callable`
- `array $parameters`

##### Returns
- `mixed`

##### Throws
- `ContainerExceptionInterface`

##### When to Use It
Handlers and hooks.

##### Common Mistakes
Not providing overrides for scalars.

## Risks, Trade-offs & Recommended Practices
- **Trade-off: Minimal API**. You can’t register new services through this interface.

### For Humans: What This Means
It’s intentionally limited.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/ContainerInterface.md`: Full runtime API.
- `docs_md/Features/Actions/Resolve/Engine.md`: Underlying resolution behavior.

### For Humans: What This Means
Resolver is a smaller view of the container.
