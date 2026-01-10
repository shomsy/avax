# ResolverInterface

## Quick Summary
- Contract focused on service retrieval and callable invocation.
- Separates “getting services” from registration and compilation.
- Exists so code can depend on minimal resolution behavior without needing a full container.

### For Humans: What This Means (Summary)
It’s the “I can fetch services and call things” interface.

## Terminology (MANDATORY, EXPANSIVE)- **Resolver**: Component that can provide services by ID.
- **Prototype resolution**: Resolving from an analyzed service blueprint.
- **Call invocation**: Running callables with autowired parameters.

### For Humans: What This Means
It’s the contract for looking things up and running callables using DI.

## Think of It
Like a vending machine: you ask for an ID and it gives you the item; it can also run a small “service” for you (call).

### For Humans: What This Means (Think)
It’s the minimal runtime capability.

## Story Example
A library only needs to `get()` services and `call()` handlers. It depends on `ResolverInterface`, not the full container, so it stays decoupled.

### For Humans: What This Means (Story)
Depending on a smaller interface keeps your code simpler and more portable.

## For Dummies
- Use `has` to check.
- Use `get` to retrieve.
- Use `call` to invoke.

### For Humans: What This Means (Dummies)
This is the minimal runtime API.

## How It Works (Technical)
Implementations follow PSR semantics and may delegate to internal engines, prototypes, and invocation executors.

### For Humans: What This Means (How)
It’s an interface; actual behavior depends on the container implementation.

## Architecture Role
A contract for resolution-only dependencies.

### For Humans: What This Means (Role)
It helps keep dependencies minimal.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: has(string $id): bool

#### Technical Explanation (has)
Checks if the ID can be resolved.

##### For Humans: What This Means (has)
Ask “do you know this ID?”

##### Parameters (has)
- `string $id`

##### Returns (has)
- `bool`

##### Throws (has)
- None.

##### When to Use It (has)
Conditional resolution.

##### Common Mistakes (has)
Assuming `has` guarantees `get` won’t fail.

### Method: get(string $id): mixed

#### Technical Explanation (get)
Resolves and returns the service.

##### For Humans: What This Means (get)
Give me the service.

##### Parameters (get)
- `string $id`

##### Returns (get)
- `mixed`

##### Throws (get)
- `ContainerExceptionInterface`

##### When to Use It (get)
Normal resolution.

##### Common Mistakes (get)
Passing non-service literal IDs.

### Method: resolve(ServicePrototype $prototype): mixed

#### Technical Explanation (resolve)
Resolves a pre-analyzed prototype into an instance.

##### For Humans: What This Means (resolve)
Build from a blueprint.

##### Parameters (resolve)
- `ServicePrototype $prototype`

##### Returns (resolve)
- `mixed`

##### Throws (resolve)
- `ContainerExceptionInterface`

##### When to Use It (resolve)
Optimization paths.

##### Common Mistakes (resolve)
Using stale prototypes.

### Method: call(callable|string $callable, array $parameters = []): mixed

#### Technical Explanation (call)
Invokes a callable with DI.

##### For Humans: What This Means (call)
Run a function/method with autowired args.

##### Parameters (call)
- `callable|string $callable`
- `array $parameters`

##### Returns (call)
- `mixed`

##### Throws (call)
- `ContainerExceptionInterface`

##### When to Use It (call)
Handlers and hooks.

##### Common Mistakes (call)
Not providing overrides for scalars.

## Risks, Trade-offs & Recommended Practices
- **Trade-off: Minimal API**. You can’t register new services through this interface.

### For Humans: What This Means (Risks)
It’s intentionally limited.

## Related Files & Folders
- `docs_md/Features/Core/Contracts/ContainerInterface.md`: Full runtime API.
- `docs_md/Features/Actions/Resolve/Engine.md`: Underlying resolution behavior.

### For Humans: What This Means (Related)
Resolver is a smaller view of the container.
