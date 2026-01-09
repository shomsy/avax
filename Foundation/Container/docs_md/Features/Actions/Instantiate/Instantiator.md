# Instantiator

## Quick Summary
- Creates new object instances from class names.
- Uses constructor prototypes (when available) to resolve constructor arguments through the container.
- Converts low-level reflection errors into `ContainerException` with clear intent.

### For Humans: What This Means
This is the object factory: you give it a class name and it builds the object, filling constructor dependencies using the container.

## Terminology
- **Autowiring**: Automatically resolving constructor parameters by type hints.
- **Prototype**: Reflection-based metadata describing the constructor and its parameters.
- **Overrides**: Explicit argument values you provide to replace container resolution.
- **DependencyResolverInterface**: Resolves parameter lists into concrete argument values.
- **KernelContext**: Optional context that can carry a precomputed prototype and preserves trace/depth.

### For Humans: What This Means
Autowiring means “figure out constructor args for me.” Prototype is the blueprint. Overrides are your manual inputs. Resolver picks values. Context carries extra info and avoids repeated work.

## Think of It
Like a build station on a factory line: it reads the blueprint (prototype), gathers parts (resolved parameters), and assembles the device (new instance).

### For Humans: What This Means
It’s the place where “a class name” becomes “a real object.”

## Story Example
A service is autowired by class name. `AnalyzePrototypeStep` already computed the constructor prototype and stored it in context. `Instantiator->build()` reads that prototype from context, resolves parameters via the container, and instantiates the class via reflection. If the class is missing, it throws a clear `ContainerException`.

### For Humans: What This Means
It’s faster when prototypes are already analyzed, and it fails with helpful messages when it can’t build.

## For Dummies
1. Check the class exists.
2. Use the prototype from context if available; otherwise generate one.
3. Ensure the class is instantiable.
4. If there’s a constructor, resolve its parameters (using overrides first).
5. Instantiate the object with the resolved arguments.
6. Wrap unexpected failures in a container exception.

Common misconceptions:
- “It also injects properties.” It doesn’t; it only handles construction.
- “Overrides are optional.” They are optional, but must match expected parameter keys.

### For Humans: What This Means
This tool builds the object; injection happens later. Overrides are there when you need to take control.

## How It Works (Technical)
`build()` validates class existence and instantiability, obtains a prototype (from context metadata or via `ServicePrototypeFactoryInterface`), resolves constructor parameters via `DependencyResolverInterface` using the container reference, and instantiates the class via `ReflectionClass::newInstanceArgs`. Any non-container exceptions are wrapped into `ContainerException` with the original exception attached.

### For Humans: What This Means
It checks it can build, computes what arguments are needed, asks the container for them, and calls `new` through reflection.

## Architecture Role
This action is used by the resolution engine and kernel steps when a service must be constructed. It depends on prototype analysis and parameter resolution and requires a container reference for autowiring.

### For Humans: What This Means
It’s the constructor builder the engine relies on when it needs a fresh instance.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ServicePrototypeFactoryInterface $prototypes, DependencyResolverInterface $resolver, ?ContainerInterface $container = null)

#### Technical Explanation
Stores the prototype factory and parameter resolver needed for instantiation, plus an optional container reference used during parameter resolution.

##### For Humans: What This Means
It keeps the blueprint reader, the argument picker, and optionally the container.

##### Parameters
- `ServicePrototypeFactoryInterface $prototypes`: Prototype factory.
- `DependencyResolverInterface $resolver`: Parameter resolver.
- `?ContainerInterface $container`: Container for autowiring.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Constructed by the container.

##### Common Mistakes
Constructing without a container and then trying to autowire.

### Method: setContainer(ContainerInterface $container): void

#### Technical Explanation
Sets the container reference used for resolving constructor parameters.

##### For Humans: What This Means
It plugs in the container so constructor arguments can be resolved.

##### Parameters
- `ContainerInterface $container`: Container reference.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
When `Instantiator` is created before the container is available.

##### Common Mistakes
Forgetting to call this and seeing “Container not available for dependency resolution.”

### Method: build(string $class, array $overrides = [], ?KernelContext $context = null): object

#### Technical Explanation
Builds a new instance of the given class. Uses a constructor prototype to determine needed parameters and resolves them using overrides and the container. Wraps unexpected errors in `ContainerException`.

##### For Humans: What This Means
This is the main method: give it a class name and it returns a new object.

##### Parameters
- `string $class`: Class name to instantiate.
- `array $overrides`: Manual argument overrides.
- `?KernelContext $context`: Optional context (may carry prototype and tracing info).

##### Returns
- `object`: New instance.

##### Throws
- `ContainerException`: When class is missing, not instantiable, container missing, or instantiation fails.

##### When to Use It
Used by the engine during service creation.

##### Common Mistakes
Passing a non-class string; expecting it to work without a container when constructor parameters exist.

## Risks, Trade-offs & Recommended Practices
- **Risk: Reflection cost**. Reflection has overhead; prefer using prototypes already computed in context.
- **Risk: Hard failures on missing classes**. This is correct behavior; ensure your definitions are accurate.
- **Practice: Keep constructors clear**. Too many constructor parameters make resolution slow and error-prone.

### For Humans: What This Means
It works best when prototypes are cached and constructors are reasonable. Fix missing classes in definitions rather than trying to "work around" errors.

## Related Files & Folders
- `docs_md/Features/Actions/Instantiate/index.md`: Folder overview.
- `docs_md/Core/Kernel/Steps/AnalyzePrototypeStep.md`: Often produces prototypes used here.
- `docs_md/Features/Actions/Resolve/DependencyResolver.md`: Parameter resolver behavior.

### For Humans: What This Means
Read the analysis step to see how prototypes are made, and the dependency resolver to see how arguments are chosen.
