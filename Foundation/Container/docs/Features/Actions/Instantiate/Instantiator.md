# Instantiator

## Quick Summary

- This file serves as the "Assembly Robot" that physically creates raw PHP objects using Reflection.
- It exists to decouple the "Logic of What to build" (Engine) from the "Act of Building" (Instantiator).
- It removes the complexity of manual `new Class()` calls by using analyzed metadata to fill constructors automatically.

### For Humans: What This Means (Summary)

This is the **Machine** that actually builds the objects. While other parts of the container deal with rules and finding parts, the Instantiator is the one that actually puts the "Key in the Ignition" and creates the object so you can start using it.

## Terminology (MANDATORY, EXPANSIVE)

- **Instantiable**: Whether a class *can* be created (e.g., NOT an interface, NOT an abstract class, and NOT a class with a private constructor).
  - In this file: Checked via `$reflection->isInstantiable()`.
  - Why it matters: Prevents the container from trying to build things that PHP says are "Unbuildable".
- **Reflection**: PHP's internal ability to "look at itself" and see the details of a class.
  - In this file: Used via `ReflectionClass`.
  - Why it matters: This is how the container knows what the constructor looks like without you having to tell it.
- **Analyzed Metadata (Prototype)**: A pre-checked blueprint of the class's constructor.
  - In this file: Fetched from `$context?->getMeta()` or the `$prototypes` factory.
  - Why it matters: It’s MUCH faster than using Reflection every single time you want to build the same class.
- **newInstanceArgs**: The specific PHP command that creates an object using an array of arguments.
  - In this file: Used at the end of the `build()` method.
  - Why it matters: This is the "Spark" that actually creates the object.

### For Humans: What This Means (Terminology)

The Instantiator checks if a class is **Instantiable** (Can it be built?), uses **Reflection** (Its eyes) to see the details, uses a **Prototype** (Blueprint) to save time, and finally uses **newInstanceArgs** to build it.

## Think of It

Think of a **3D Printer**:

1. **Engine**: The person who sends the file to the printer.
2. **Analyzed Metadata**: The 3D model file (the instructions).
3. **Dependency Resolver**: The plastic filament (the material).
4. **Instantiator**: The printer nozzle itself that physically lays down the material to create the final object.

### For Humans: What This Means (Analogy)

The nozzle (Instantiator) doesn't know *what* it is printing or *why*; it just follows the instructions it was given to produce the physical result.

## Story Example

You want to build a `ReportGenerator`. The container has already figured out that the generator needs a `DatabaseConnection` and a `PdfWriter`. The **Instantiator** is handed the `ReportGenerator` class name and the two objects it needs. It performs one last check: "Is `ReportGenerator` an abstract class? No. Is its constructor public? Yes." Once it’s satisfied, it "Plugs in" the connection and the writer, creates the generator, and hands it back.

### For Humans: What This Means (Story)

It’s the final step in the process. It ensures that everything the class needs is ready, and then it does the "Heavy Lifting" of creating the object in memory.

## For Dummies

Imagine you're putting together a Lego set.

1. **Check the Box**: "Is this a real set I can build?" (`isInstantiable`).
2. **Check the Instructions**: "What pieces go where?" (`Prototype`).
3. **Get the Pieces**: "Give me the bricks." (Arguments from the `Resolver`).
4. **Click them together**: "Build it!" (`newInstanceArgs`).

### For Humans: What This Means (Walkthrough)

It takes a list of "Rules" and a list of "Ingredients" and produces a "Finished Toy".

## How It Works (Technical)

The `Instantiator` follows a strict execution path:

1. **Metadata Sourcing**: It first tries to find a pre-computed "Prototype" in the `KernelContext`. If not found, it asks the `ServicePrototypeFactory` to create one (which involves Reflection).
2. **Safety Check**: It uses Reflection to ensure the class is not abstract, its constructor is accessible, and the class actually exists.
3. **Argument Resolution**: If the constructor has parameters, it delegates to the `DependencyResolver` to get the actual values (triggering recursive resolutions if needed).
4. **Construction**: It calls `$reflection->newInstanceArgs()` (if there are arguments) or `$reflection->newInstance()` (if the constructor is empty).
5. **Error Handling**: Any failure in this process is wrapped in a `ContainerException` with details about which class failed to build and why.

### For Humans: What This Means (Technical)

It is a "Safety First" builder. It checks every possible way the object creation could fail before it actually tries to create it.

## Architecture Role

- **Lives in**: `Features/Actions/Instantiate`
- **Role**: Physical Object Factory.
- **Collaborators**: `ServicePrototypeFactory`, `DependencyResolver`.

### For Humans: What This Means (Architecture)

It is the "Final Execution" point of the building process.

## Methods

### Method: setContainer(ContainerInterface $container)

#### Technical Explanation: setContainer

Wires the container facade into the instantiator. This is required because resolving constructor arguments often requires looking back into the container.

#### For Humans: What This Means

"Gives the robot a telephone so it can call the warehouse for parts."

### Method: build(string $class, array $overrides, KernelContext $context)

#### Technical Explanation: build

The primary method. It coordinates metadata retrieval, argument resolution, and physical object creation.

#### For Humans: What This Means

"Build this specific object now."

## Risks & Trade-offs

- **Reflection Cost**: Using Reflection is slower than hard-coded `new Class()` calls. The container mitigates this by using **Prototypes** (Metadata) that can be cached.
- **Abstract Classes**: The Instantiator will fail if you try to build an Interface or Abstract class directly. You MUST provide an implementation (concrete) in the container definitions if you want to use interfaces.

### For Humans: What This Means (Risks)

It’s slightly slower than building things by hand, but it’s much smarter. Just remember that you can't build "Ideas" (Interfaces)—you have to build "Things" (Classes).

## Related Files & Folders

- `ServicePrototype.php`: The data description of the constructor.
- `DependencyResolver.php`: The person who finds the pieces for the constructor.
- `Engine.php`: The boss who tells the Instantiator what to build.

### For Humans: What This Means (Relationships)

The **Engine** decides to build, the **Resolver** finds the parts, and the **Instantiator** clicks them together.
