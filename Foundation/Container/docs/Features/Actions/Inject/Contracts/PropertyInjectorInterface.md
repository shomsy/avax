# PropertyInjectorInterface

## Quick Summary
- Defines the contract for resolving a single injectable property into a `PropertyResolution`.
- Keeps `InjectDependencies` independent from a specific property injection implementation.
- Requires callers to provide context, overrides, and owner class for correct behavior and error messaging.

### For Humans: What This Means (Summary)
It’s the “rules of the game” for property injection: given a property and the current context, return either a value to inject or a signal to leave it alone.

## Terminology (MANDATORY, EXPANSIVE)- **Contract**: Interface defining behavior without implementation.
- **PropertyPrototype**: Data describing a property injection point.
- **KernelContext**: Context preserving resolution chain (important for guarding and diagnostics).
- **Overrides**: Explicit values you want to force.
- **Owner class**: Class name used for meaningful error messages.
- **PropertyResolution**: Result object that says whether injection should happen and what value to use.

### For Humans: What This Means
You hand the injector a description of the property plus context and overrides, and it returns a structured answer: “inject this value” or “don’t inject.”

## Think of It
Like a “parts request form”: you submit the part details (property prototype) plus constraints (overrides) and the system returns either the part to install or a note saying “no part available.”

### For Humans: What This Means (Think)
It standardizes how you ask for an injectable property value.

## Story Example
You replace the default `PropertyInjector` with a custom one that supports configuration-based injection. Because it implements this interface, `InjectDependencies` can use it without code changes.

### For Humans: What This Means (Story)
You can swap injection behavior without rewriting the kernel.

## For Dummies
- Implement this interface in a class.
- Return `PropertyResolution::resolved($value)` when you can inject.
- Return `PropertyResolution::unresolved()` when you can’t or shouldn’t.
- Throw a resolution exception when the property is required and cannot be filled.

### For Humans: What This Means (Dummies)
This interface makes “property value picking” consistent across implementations.

## How It Works (Technical)
Declares `resolve(PropertyPrototype $property, array $overrides, KernelContext $context, string $ownerClass): PropertyResolution`.

### For Humans: What This Means (How)
There’s just one required method: “resolve this property.”

## Architecture Role
A decoupling seam between orchestration (`InjectDependencies`) and the concrete implementation (`PropertyInjector`). Helps testing, customization, and alternative injection strategies.

### For Humans: What This Means (Role)
It’s the switch point that lets you replace how property injection works.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: resolve(PropertyPrototype $property, array $overrides, KernelContext $context, string $ownerClass): PropertyResolution

#### Technical Explanation (resolve)
Resolves a single injectable property to a `PropertyResolution` based on overrides, container capabilities, type rules, defaults, and requiredness.

##### For Humans: What This Means (resolve)
Given a property, decide whether to inject and what value to use.

##### Parameters (resolve)
- `PropertyPrototype $property`: What property needs injection.
- `array $overrides`: Explicit values keyed by property name.
- `KernelContext $context`: Resolution context for guarding and nested resolution.
- `string $ownerClass`: Class name for error reporting.

##### Returns (resolve)
- `PropertyResolution`: Result describing resolved/unresolved and value.

##### Throws (resolve)
- Implementation-specific exceptions when resolution is required but impossible.

##### When to Use It (resolve)
Called during injection for each injectable property.

##### Common Mistakes (resolve)
Returning resolved for defaults without intending to override the class’s default value.

## Risks, Trade-offs & Recommended Practices
- **Risk: Inconsistent semantics**. Different implementations must keep "resolved/unresolved" meaning consistent.
- **Practice: Preserve context**. Use `KernelContext` when resolving nested dependencies.

### For Humans: What This Means (Risks)
Keep behavior predictable and don’t lose the context chain when resolving nested dependencies.

## Related Files & Folders
- `docs_md/Features/Actions/Inject/Contracts/index.md`: Contracts overview.
- `docs_md/Features/Actions/Inject/PropertyInjector.md`: Default implementation.
- `docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md`: Return type.

### For Humans: What This Means (Related)
See the default implementation and the resolution result object to understand how callers interpret outcomes.
