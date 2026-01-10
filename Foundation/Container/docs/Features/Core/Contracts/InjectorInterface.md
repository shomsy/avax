# InjectorInterface

## Quick Summary
- Contract for injecting dependencies into existing objects.
- Provides three key capabilities: `injectInto`, `canInject`, and `getInjectionInfo`.
- Exists to separate injection concerns from resolution and registration.

### For Humans: What This Means (Summary)
It’s the interface for “wire this object up after it already exists.”

## Terminology (MANDATORY, EXPANSIVE)- **Injection**: Populating properties and invoking methods with dependencies.
- **Injection points**: Locations marked for injection (attributes, conventions).
- **Dry-run**: Checking injection viability without mutating the object.

### For Humans: What This Means
It defines how the container can connect dependencies to an object.

## Think of It
Like a technician who can connect cables to a device, check if the cables fit, and report what ports exist.

### For Humans: What This Means (Think)
It wires objects, checks wiring feasibility, and reports wiring details.

## Story Example
A legacy object is created outside the container. The app calls `injectInto($object)` to fill `#[Inject]` properties. Before doing it, the app calls `canInject($object)` to confirm the container can resolve everything.

### For Humans: What This Means (Story)
You can retrofit DI into objects you didn’t build through the container.

## For Dummies
- Use `injectInto()` to do the injection.
- Use `canInject()` to check first.
- Use `getInjectionInfo()` to debug injection points.

### For Humans: What This Means (Dummies)
It’s injection + preflight + introspection.

## How It Works (Technical)
Implementations typically scan targets via reflection, build prototypes, resolve dependencies, and apply injection safely.

### For Humans: What This Means (How)
It looks at the object, figures out what it needs, finds those things, and sets them.

## Architecture Role
Decouples injection behavior so different injection strategies can be swapped.

### For Humans: What This Means (Role)
It keeps injection modular.

## Methods 

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: injectInto(object $target): object

#### Technical Explanation (injectInto)
Performs dependency injection into an existing instance.

##### For Humans: What This Means (injectInto)
Wire the object.

##### Parameters (injectInto)
- `object $target`

##### Returns (injectInto)
- `object`

##### Throws (injectInto)
- `ContainerExceptionInterface` when injection fails.

##### When to Use It (injectInto)
When you have an object but want container wiring.

##### Common Mistakes (injectInto)
Expecting it to create the object.

### Method: canInject(object $target): bool

#### Technical Explanation (canInject)
Checks whether injection is possible.

##### For Humans: What This Means (canInject)
Ask “will injection work?”

##### Parameters (canInject)
- `object $target`

##### Returns (canInject)
- `bool`

##### Throws (canInject)
- None.

##### When to Use It (canInject)
Before injection.

##### Common Mistakes (canInject)
Treating false as “bug”; sometimes it’s correct (no injection points).

### Method: getInjectionInfo(object $target): array

#### Technical Explanation (getInjectionInfo)
Returns structured info about injection points.

##### For Humans: What This Means (getInjectionInfo)
Get a report of injection ports.

##### Parameters (getInjectionInfo)
- `object $target`

##### Returns (getInjectionInfo)
- `array`

##### Throws (getInjectionInfo)
- None (implementation dependent).

##### When to Use It (getInjectionInfo)
Diagnostics and tools.

##### Common Mistakes (getInjectionInfo)
Expecting a stable schema across implementations.

## Risks, Trade-offs & Recommended Practices
- **Risk: Hidden dependencies**. Injection makes dependencies less obvious; document injection points.
- **Practice: Prefer constructor injection for core requirements**.

### For Humans: What This Means (Risks)
Injection is powerful, but constructors stay clearer.

## Related Files & Folders
- `docs_md/Features/Actions/Inject/InjectDependencies.md`: Concrete injection action.
- `docs_md/Features/Core/Attribute/Inject.md`: Marker for injection points.

### For Humans: What This Means (Related)
Injector implementations usually rely on InjectDependencies and the Inject attribute.

### Method: setConfig(...)

#### Technical Explanation (setConfig)
This method lets an injector receive configuration that shapes how it performs injection work.

##### For Humans: What This Means (setConfig)
You use this when you want the injector to “know” some options up front, so you don’t have to pass the same knobs on every call.

##### Parameters (setConfig)
- The configuration value(s) the injector needs to make consistent decisions.

##### Returns (setConfig)
- The method’s return value tells you whether configuration is accepted and what chaining/usage style the API supports.

##### Throws (setConfig)
- Any configuration errors you see here usually mean you passed invalid options or tried to configure at the wrong time.

##### When to Use It (setConfig)
- When you initialize or bootstrap the injector.
- When you swap injector behavior based on environment or profile.

##### Common Mistakes (setConfig)
- Configuring after injection already started (inconsistent behavior).
- Passing runtime state instead of stable configuration.
