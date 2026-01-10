# InjectDependencies

## Quick Summary

- This file serves as the "Hydrator" which fills an object with dependencies *after* the object has been created.
- It exists to handle "Setter Injection" and "Property Injection" for cases where Constructor Injection is not used or not sufficient.
- It removes the complexity of manually calling `setX()` methods or setting protected properties by automating the discovery and injection process.

### For Humans: What This Means (Summary)

This is the **Decorator** of the container. If the `Instantiator` builds the "Shell" of your object, `InjectDependencies` is the one that goes back in and installs the "Furniture" (properties) and "Utilities" (setters) so the object is truly ready to use.

## Terminology (MANDATORY, EXPANSIVE)

- **Hydration**: The process of taking a "dry" object (it exists but has empty slots) and "filling" it with the data and dependencies it needs.
  - In this file: The `execute()` method.
  - Why it matters: Vital for classes that don't use their constructor for everything, like some framework controllers or DTOs.
- **Property Injection**: Directly setting a class variable (even a private one) without using a method.
  - In this file: Handled by `injectProperties()`.
  - Why it matters: Allows for "Magic" injection where you just add an Attribute/Annotation to a property and it gets filled automatically.
- **Setter/Method Injection**: Calling a specific method (e.g., `setLogger()`) and passing it a dependency from the container.
  - In this file: Handled by `injectMethods()`.
  - Why it matters: A common pattern for optional dependencies where the constructor should stay clean.
- **Injection Points**: Locations within a class (properties or methods) that have been marked as "Needing a dependency".
  - In this file: Discovered via the `ServicePrototype`.
  - Why it matters: Tells the hydrator exactly where it is "allowed" to touch the object.

### For Humans: What This Means (Terminology)

The hydrator performs **Hydration** by finding **Injection Points** and filling them via **Property Injection** (Directly in the variable) or **Setter Injection** (Via a method call).

## Think of It

Think of a **House Construction**:

1. **Instantiator**: The crew that builds the walls, roof, and floors. The house now "exists" but you can't live in it yet.
2. **InjectDependencies**: The electricians, plumbers, and interior designers. They come in *after* the walls are up to install the wires, pipes, and furniture into the specific spots (Injection Points) where they are supposed to go.

### For Humans: What This Means (Analogy)

It’s not enough to have a house; you need it to be "connected" to the utilities. This class provides those connections.

## Story Example

You have a `Dashboard` class. It has a `#[Inject]` attribute on a private `$user` property. When the container builds the Dashboard, it first uses the Instantiator to create the object. Then, it hands the object to **InjectDependencies**. The hydrator sees the `user` property is marked for injection, finds the current User object in the container, and "Injects" it directly into that private property. Finally, it looks for a `setTheme()` method, sees it also needs a dependency, and calls it. Only then is the Dashboard handed back to your application.

### For Humans: What This Means (Story)

It makes your classes very flexible. You can have properties that "Just Work" without having to pass every single thing through the constructor, which can sometimes get too crowded.

## For Dummies

Imagine you're getting a phone.

1. **Instantiator**: You get the phone hardware.
2. **Hydration**: You sign into your account, and suddenly all your apps, contacts, and wallpapers appear.
3. **Result**: You have a phone that is actually *useful* to you personally.

### For Humans: What This Means (Walkthrough)

It’s the "Setup Phase" that happens automatically behind the scenes.

## How It Works (Technical)

The `InjectDependencies` class operates in two distinct phases:

1. **Property Phase**: It uses the `PropertyInjector` to resolve values for any properties marked in the `ServicePrototype`. It uses Reflection to bypass visibility (private/protected) and set the value directly. It specifically checks for `readonly` properties and throws an error if injection is attempted on them (as PHP doesn't allow changing readonly properties after construction).
2. **Method Phase**: It iterates through any methods marked for injection. It uses the `DependencyResolver` to resolve the parameters for these methods, then uses Reflection to `invoke()` the method with those arguments.
This entire process is recursive if the dependencies being injected haven't been built yet—the container will pause hydration, build the dependency, and then resume.

### For Humans: What This Means (Technical)

It first fills the "Boxes" (Properties) and then runs the "Setup Tasks" (Methods). It handles all the difficult PHP Reflection work so you don't have to.

## Architecture Role

- **Lives in**: `Features/Actions/Inject`
- **Role**: Post-Construction Hydration Orchestrator.
- **Primary Collaborators**: `PropertyInjector`, `DependencyResolver`.

### For Humans: What This Means (Architecture)

It is the "Final Polish" layer of the object lifecycle.

## Methods

### Method: setContainer(ContainerInterface $container)

#### Technical Explanation: setContainer

Wires the container facade into the hydrator. Because method parameters can be any service, the hydrator needs a way to ask the container for those services.

#### For Humans: What This Means

"Gives the Decorator the key to the warehouse so they can fetch the furniture."

### Method: execute(object $target, ServicePrototype $prototype)

#### Technical Explanation: execute

The main entry point. It takes a living object and its "Instruction set" (Prototype) and performs the hydration.

#### For Humans: What This Means

"Finish setting up this object."

## Risks & Trade-offs

- **Visibility Bypass**: It can set private properties. While powerful, this can lead to "Magic" behaviors that are hard to debug if you don't know the container is doing it.
- **Performance**: Every property or method injection requires a Reflection call and a potential Container lookup. In high-performance loops, prefer constructor injection which is handled in one go.
- **Readonly Properties**: As mentioned, you CANNOT use property injection on `readonly` properties. These MUST be filled in the constructor.

### For Humans: What This Means (Risks)

It’s very powerful but can feel like "Magic". Use it for secondary things (like Loggers or Configurations) rather than for your main data, and remember that `readonly` variables are off-limits for this tool.

## Related Files & Folders

- `PropertyInjector.php`: The specialist who handles the actual property-setting logic.
- `DependencyResolver.php`: The helper who finds the objects needed for setter methods.
- `Instantiator.php`: The "Builder" who runs right before this class.

### For Humans: What This Means (Relationships)

The **Instantiator** builds the shell, and then this class (the **Hydrator**) fills it with life.
