# ServicePrototypeBuilder

## Quick Summary

- This file serves as the "Constructor" for class blueprints.
- It exists to provide a clean, readable way (a DSL) to manifest a `ServicePrototype` without needing to use the "Automatic" reflection analyzer.
- It removes the complexity of manually creating deep object trees for prototypes by using a fluent interface.

### For Humans: What This Means (Summary)

This is the **Manual Architect**. While the `PrototypeAnalyzer` works like a 3D scanner that automatically scans your code, the `ServicePrototypeBuilder` is like a **Drawing Board**. You use it when you want to manually draw a plan for a class, which is super useful for tests or when you want to override the automatic rules.

## Terminology (MANDATORY, EXPANSIVE)

- **Fluent Interface**: A style of coding where methods return `$this`, allowing you to "Chain" them together like a sentence.
  - In this file: The entire class uses this style.
  - Why it matters: It makes the code much easier to read and "Feels" like you're writing a configuration.
- **Service Prototype**: The final, immutable "Instruction Sheet" produced by the builder.
  - In this file: The result of the `build()` method.
  - Why it matters: This is the actual "Data Model" the container uses to know what to build.
- **Instantiable State**: A flag that tells the container "Yes, you can build this" or "No, this requires a special factory".
  - In this file: Set via `setInstantiable()`.
  - Why it matters: Prevents the container from trying to build abstract classes or interfaces unless a custom builder is provided.

### For Humans: What This Means (Terminology)

The Builder uses a **Fluent** (Chained) interface to create a **Service Prototype** (Blueprint) while specifying the **Instantiable State** (Can it be built?).

## Think of It

Think of a **Custom Pizza Order Menu**:

1. **Selection**: You choose the "Dough type" (`for`).
2. **Base**: You choose the "Main sauce" (`withConstructor`).
3. **Toppings**: You add "Veggies" (`addProperty`) and "Meats" (`addMethod`).
4. **Order**: You hit "Place Order" (`build`), and the kitchen gets a single paper slip with your custom instructions.

### For Humans: What This Means (Analogy)

The builder is the "Order Form". You fill it out step-by-step, and when you're done, you get a single, clear "Order Slip" (Prototype) for the kitchen (The Container).

## Story Example

You are writing a unit test for your container. You want to test if the container correctly injects a `Logger` into a property, but you don't want to create a physical PHP file with a `#[Inject]` attribute just for one test. Instead, you use the **ServicePrototypeBuilder**. In 5 lines of code, you build a "Fake" prototype that says "Class A needs Logger B in property C". You hand this to the container, and it works perfectly without ever needing to touch a real file or use reflection.

### For Humans: What This Means (Story)

It gives you "Dynamic Control". It’s the tool you use when you want to be the "Boss" and tell the container exactly how a class should behave, ignoring whatever is written in the source code.

## For Dummies

Imagine you're building a Lego set.

1. **Identify**: Start the instructions for a "Red Car". (`for`)
2. **Structure**: Say that it needs a "Chassis block" first. (`withConstructor`)
3. **Details**: Add that it needs "Blue doors" and "Yellow wheels". (`addProperty`)
4. **Finalize**: Print the instruction manual. (`build`)

### For Humans: What This Means (Walkthrough)

It's a "Step-by-Step" blueprint creator. You start with the name and finish with a completed manual.

## How It Works (Technical)

The `ServicePrototypeBuilder` is a mutable state-container that produces an immutable DTO:

1. **State Management**: It stores class names, constructor prototypes, property lists, and method lists in internal private variables.
2. **Fluent Methods**: Methods like `for()`, `addProperty()`, and `addMethod()` modify this internal state and return `$this`. This allows for a declarative syntax.
3. **Variadic Inputs**: Methods like `addProperty()` accept variadic arguments (`...$prototypes`), allowing you to add multiple injection points in a single call.
4. **Immutability**: When `build()` is called, it takes all the internal "Gathered" state and passes it into the constructor of the `ServicePrototype` class. Once that object is created, it can never be changed.

### For Humans: What This Means (Technical)

It is a "Temporary Workspace". You can change things, add things, or remove things until you're ready. Once you hit "Build", the workspace is locked, and you get a permanent, finished product.

## Architecture Role

- **Lives in**: `Features/Think/Prototype`
- **Role**: Programmatic Prototype Creation DSL.
- **Result**: `ServicePrototype`.

### For Humans: What This Means (Architecture)

It is the "Manual Interface" for the Intelligence Layer.

## Methods

### Method: for(string $class)

#### Technical Explanation: for

Sets the target class for the blueprint. Required before building.

#### For Humans: What This Means

"Tell the builder which class we are drawing a plan for."

### Method: addProperty(PropertyPrototype ...$prototypes)

#### Technical Explanation: addProperty

Adds one or more property injection specifications to the internal list.

#### For Humans: What This Means

"Add a variable that needs data injected into it."

### Method: build()

#### Technical Explanation: build

The finalization method. Validates the state and returns the immutable `ServicePrototype`.

#### For Humans: What This Means

"Lock the plan and give me the final blueprint."

## Risks & Trade-offs

- **Manual Error**: Because you are bypassing automatic analysis, you can easily create an "Impossible Blueprint" (e.g. telling the container to inject a string into a variable that requires an object). The builder does not run a full validation of your PHP code.
- **Maintenance**: If your class changes (e.g. you rename a private property), a manual builder might break while an automatic analyzer would have just "Seen" the change. Use the builder sparingly!

### For Humans: What This Means (Risks)

"With great power comes great responsibility." You are telling the container what to do, so you have to be extra careful that your manual plan matches reality. If you use this, you become the "Quality Control" person.

## Related Files & Folders

- `ServicePrototype.php`: The final product created by this builder.
- `PrototypeAnalyzer.php`: The "Automatic" alternative to this manual builder.
- `PropertyPrototype.php`: One of the "Parts" used by this builder.

### For Humans: What This Means (Relationships)

The **Builder** is the manual way, and the **Analyzer** is the automatic way, but they both create the same **Prototype**.
