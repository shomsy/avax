# PropertyInjector

## Quick Summary
- Resolves values for injectable properties (`PropertyPrototype`) using overrides first, then type-based container resolution.
- Preserves the `KernelContext` chain to support circular-dependency protection when using internal container context resolution.
- Throws when a required property can’t be resolved and null/default are not allowed.

### For Humans: What This Means
This is the part of injection that decides what value goes into each injectable property—and it fails loudly when a required property can’t be filled.

## Terminology
- **PropertyPrototype**: Reflection-derived description of a property that may need injection (name, type, default, nullability, required).
- **Overrides**: Explicit values keyed by property name.
- **ReflectionTypeAnalyzer**: Helper that decides whether a property type is resolvable.
- **ContainerInternalInterface**: Internal container API that supports context-aware resolution.
- **PropertyResolution**: Result object describing whether a property was resolved and what value should be injected.

### For Humans: What This Means
Prototype tells what the property is; overrides let you force a value; type analyzer says “can we resolve this type”; internal container can resolve with context; PropertyResolution is the yes/no answer plus the value.

## Think of It
Like a smart parts picker in a warehouse: for each slot on a machine, it checks if you supplied a specific part (override). If not, it looks up the right part by type (container). If it still can’t, it either leaves it empty (optional) or raises an error (required).

### For Humans: What This Means
It tries your explicit choice first, then falls back to “find by type,” and only crashes when the property is truly required.

## Story Example
A class has an injected property `LoggerInterface $logger`. There’s no override. The type analyzer says it’s resolvable, so the injector asks the container for `LoggerInterface` (preserving context). The property gets set. If the property is required and there’s no service registered, it throws a `ResolutionException`.

### For Humans: What This Means
When the container knows the type, injection “just works.” When it doesn’t and the property is required, you get a clear error.

## For Dummies
1. If there’s no container set, you can’t resolve anything: throw.
2. If overrides contain the property name, use that value.
3. If the property type is resolvable, try container resolution.
4. If property has a default, treat it as unresolved (so default stays).
5. If null is allowed, resolve to null.
6. If it’s required and still unresolved, throw.

### For Humans: What This Means
Overrides win. Then types. Then defaults/null. If it’s required and still missing: error.

## How It Works (Technical)
`resolve` enforces a container reference, checks overrides, uses `ReflectionTypeAnalyzer` to decide type resolvability, attempts container resolution (using `resolveContext` when internal API is available), and falls back to default/null/required rules. It returns a `PropertyResolution` for the caller to apply.

### For Humans: What This Means
It follows a predictable priority order and returns a structured “inject this / don’t inject” result.

## Architecture Role
Supports `InjectDependencies` by resolving property values consistently. Depends on container availability and type analysis. It’s designed to preserve the resolution context chain so injection doesn’t bypass kernel guard logic.

### For Humans: What This Means
It’s the helper that makes property injection safe and predictable.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(?ContainerInterface $container, ReflectionTypeAnalyzer $typeAnalyzer = new ReflectionTypeAnalyzer())

#### Technical Explanation
Stores the container reference and the type analyzer used for deciding whether a property type can be resolved.

##### For Humans: What This Means
It needs access to the container to fetch services, and a helper to decide which types are resolvable.

##### Parameters
- `?ContainerInterface $container`: Container used to resolve property types.
- `ReflectionTypeAnalyzer $typeAnalyzer`: Helper for type resolvability.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Usually constructed by the container.

##### Common Mistakes
Passing null container and forgetting to set it later.

### Method: setContainer(ContainerInterface $container): void

#### Technical Explanation
Sets or replaces the container reference.

##### For Humans: What This Means
It plugs in the container so property injection can work.

##### Parameters
- `ContainerInterface $container`: Container reference.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
When the injector was constructed without a container.

##### Common Mistakes
Calling `resolve` before setting container.

### Method: resolve(PropertyPrototype $property, array $overrides, KernelContext $context, string $ownerClass): PropertyResolution

#### Technical Explanation
Returns a `PropertyResolution` based on overrides, type resolution using the container (with context preservation), default/null rules, and requiredness. Throws when a required property can’t be resolved.

##### For Humans: What This Means
It decides what value (if any) should be injected into the property.

##### Parameters
- `PropertyPrototype $property`: Description of injectable property.
- `array $overrides`: Override values keyed by property name.
- `KernelContext $context`: Context used for context-aware resolution.
- `string $ownerClass`: Class name used for error messages.

##### Returns
- `PropertyResolution`: Resolved/unresolved outcome and value.

##### Throws
- `RuntimeException` if container reference is missing.
- `ResolutionException` when a required property can’t be resolved.

##### When to Use It
Called by `InjectDependencies` during property injection.

##### Common Mistakes
Expecting unresolved to mean "inject default"; unresolved typically means "leave as-is".

## Risks, Trade-offs & Recommended Practices
- **Risk: Hidden dependency graphs**. Property injection can obscure what a class needs; document injection points in prototypes.
- **Risk: Container coupling**. Injectors depend on container availability; ensure boot order sets the container references.
- **Practice: Prefer required only when needed**. Mark properties required only when the class truly cannot operate without them.

### For Humans: What This Means
Property injection is convenient but can hide what you depend on; keep it disciplined and make sure the container is properly set.

## Related Files & Folders
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Uses this injector.
- `docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md`: Return type.
- `docs_md/Features/Think/Model/PropertyPrototype.md`: Prototype describing injectable properties.

### For Humans: What This Means
Read InjectDependencies to see orchestration, PropertyResolution to understand outcomes, and PropertyPrototype to see what data drives decisions.
