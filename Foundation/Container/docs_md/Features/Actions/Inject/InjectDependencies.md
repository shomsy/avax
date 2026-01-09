# InjectDependencies

## Quick Summary
- Injects dependencies into an existing object instance using reflection prototypes.
- Performs two phases: property injection and method injection.
- Uses overrides first, then type-based resolution via the container, and records failures through exceptions.

### For Humans: What This Means
This is the tool you use when you already have an object and you want the container to “finish wiring it up” by filling injectable properties and calling injectable methods.

## Terminology
- **Target**: The object instance you’re injecting into.
- **Prototype**: A reflection-derived blueprint (`ServicePrototype`) listing injectable properties and methods.
- **Overrides**: Per-property/per-parameter values you explicitly supply to override container resolution.
- **Property injection**: Setting a property value directly on the target.
- **Method injection**: Calling a method with dependencies as arguments.
- **DependencyResolverInterface**: Resolves method parameters using the container and overrides.
- **ContainerInterface**: Container reference required for method injection parameter resolution.

### For Humans: What This Means
Target is the thing you’re wiring; prototype tells where to inject; overrides let you force specific values; property injection fills fields; method injection calls setup methods; resolver and container figure out values.

## Think of It
Like plugging a pre-built device into a docking station that connects power, network, and peripherals automatically. The device exists, but it’s not “fully operational” until you connect everything.

### For Humans: What This Means
You already have the object. This action connects the missing dependencies for you.

## Story Example
You create an object manually (maybe from a factory) but it still has `#[Inject]` properties. You call `InjectDependencies->execute($object)` and the container fills those properties and runs injection methods using type hints.

### For Humans: What This Means
Even if you didn’t create the object through `Container::get()`, you can still get container-style injection.

## For Dummies
1. Provide the target object.
2. Optionally provide a prototype (otherwise it’s built from reflection).
3. Optionally provide overrides for specific properties/parameters.
4. Ensure a container reference is available (needed for method injection).
5. Call `execute()`.

Common misconceptions:
- “It registers the object in the container.” It doesn’t; it only injects.
- “It can inject methods without a container.” It can’t; method parameter resolution needs the container.

### For Humans: What This Means
This doesn’t make your object a singleton or store it. It only wires dependencies, and method injection needs access to the container.

## How It Works (Technical)
`execute` builds or accepts a `ServicePrototype`, then calls `injectProperties` and `injectMethods`. Properties are resolved through `PropertyInjectorInterface`, which handles overrides, type resolution, defaults/nullability, and requiredness. Methods are injected by resolving parameters via `DependencyResolverInterface` using the container reference, then invoking the reflected method.

### For Humans: What This Means
It finds what needs injecting, gets the right values (from overrides or container), sets properties, and calls methods.

## Architecture Role
Lives in `Features/Actions/Inject` because it is a reusable action invoked by kernel steps (e.g., `InjectDependenciesStep`) and potentially by application code for manual wiring. It depends on prototype analysis and resolution subsystems.

### For Humans: What This Means
It’s the injection engine that the kernel calls when it’s time to wire an object.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ServicePrototypeFactoryInterface $servicePrototypeFactory, PropertyInjectorInterface $propertyInjector, DependencyResolverInterface $resolver, ?ContainerInterface $container = null)

#### Technical Explanation
Stores dependencies required to build prototypes, resolve property values, resolve method parameters, and optionally access the container.

##### For Humans: What This Means
It receives the tools it needs: prototype builder, property injector, parameter resolver, and optionally the container.

##### Parameters
- `ServicePrototypeFactoryInterface $servicePrototypeFactory`: Builds prototypes from reflection.
- `PropertyInjectorInterface $propertyInjector`: Resolves property values.
- `DependencyResolverInterface $resolver`: Resolves method parameters.
- `?ContainerInterface $container`: Container used for method injection.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Created during container boot; you usually don’t instantiate it manually.

##### Common Mistakes
Constructing without a container and then attempting method injection.

### Method: setContainer(ContainerInterface $container): void

#### Technical Explanation
Sets the container reference used during method injection.

##### For Humans: What This Means
It plugs in the container so method injection can resolve arguments.

##### Parameters
- `ContainerInterface $container`: Container used for parameter resolution.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
If the action is constructed without a container, call this before injecting methods.

##### Common Mistakes
Forgetting to set the container and getting a runtime exception during method injection.

### Method: execute(object $target, ?ServicePrototype $prototype = null, ?array $overrides = null, ?KernelContext $context = null): object

#### Technical Explanation
Orchestrates injection into the target: ensures a prototype, builds reflection, injects properties, injects methods, and returns the (mutated) target object.

##### For Humans: What This Means
This is the main entry point: you give it an object, and it wires it.

##### Parameters
- `object $target`: The object to inject into.
- `?ServicePrototype $prototype`: Optional prototype to avoid rebuilding.
- `?array $overrides`: Optional overrides for property/method parameters.
- `?KernelContext $context`: Optional context to preserve resolution chain and depth.

##### Returns
- `object`: The same target object, now injected.

##### Throws
- `ResolutionException` when required properties cannot be resolved.
- `ContainerException` when method injection is needed but container is unavailable.
- `ReflectionException` when reflection fails.

##### When to Use It
When you need to inject dependencies into an already-constructed instance.

##### Common Mistakes
Passing overrides with wrong keys (must match property names/parameter names expected by resolver).

## Risks, Trade-offs & Recommended Practices
- **Risk: Hidden dependencies**. Property/method injection can hide required dependencies; prefer constructor injection for critical requirements.
- **Risk: Container missing for method injection**. Ensure container is set when prototypes include injected methods.
- **Trade-off: Convenience vs explicitness**. Injection is flexible but less explicit than constructor wiring.
- **Practice: Use overrides intentionally**. Overrides are powerful; treat them like configuration, not random patching.

### For Humans: What This Means
Injection makes your life easier, but it can make dependencies harder to see. Use it when it’s worth it, and make sure the container is available.

## Related Files & Folders
- `docs_md/Features/Actions/Inject/index.md`: Folder overview.
- `docs_md/Features/Actions/Inject/PropertyInjector.md`: Property injection resolver.
- `docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md`: Result object for property resolution.
- `docs_md/Core/Kernel/Steps/InjectDependenciesStep.md`: Kernel step that invokes this action.

### For Humans: What This Means
Start with the folder overview, then see how properties are resolved and how the kernel uses this action.
