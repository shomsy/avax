# PropertyPrototype

## Quick Summary

- This file defines the "Plan for a Variable"—it describes how a single class property should be filled by the container.
- It exists to decouple the container from the raw source code, providing a static description of the property's injection requirements.
- It removes the need for checking attributes and types via Reflection at runtime.

### For Humans: What This Means (Summary)

This is the **Valve Specification**. If your class is a machine, a `PropertyPrototype` describes a single valve that needs to be connected to a pipe. The specification says which pipe to connect (Type), what the valve is named (Name), and if it’s okay for the pipe to be empty (Nullability).

## Terminology (MANDATORY, EXPANSIVE)

- **Field Injection**: Also known as Property Injection. The process of setting a class variable directly after the object is built.
  - In this file: The primary purpose of this model.
  - Why it matters: It’s a clean way to handle optional dependencies that don't need to be in the constructor.
- **Normalized Type**: The class name or ID required by the property, translated into a standard string.
  - In this file: the `$type` property.
  - Why it matters: Ensures the container doesn't get confused by different ways of writing class names in the code.
- **Required Injection**: A flag that tells the container: "If you can't find a dependency for this property, you MUST throw an error."
  - In this file: The `$required` property.
  - Why it matters: It prevents "Silent Failures" where your app starts up with empty variables and crashes much later.

### For Humans: What This Means (Terminology)

The Property Prototype enables **Propety Injection** (Variable filling) by tracking the **Normalized Type** (The requirement) and enforcing **Required Injection** (The importance).

## Think of It

Think of a **Plug in a Data Center**:

1. **Label**: "Fiber Channel Port A" (Name).
2. **Cable Needed**: "Standard SFP+ 10Gb" (Type).
3. **Criticality**: "Mission Critical" (Required).

### For Humans: What This Means (Analogy)

The `PropertyPrototype` is the label on the rack. It tells the technician (The PropertyInjector) exactly what cable to plug in and how important it is.

## Story Example

You have a `NewsletterService`. You don't want to clutter the constructor, so you add `#[Inject] public Logger $logger;`. The container analyzes the class and creates a **PropertyPrototype**. It notes that the property name is `logger` and it needs a `Logger` service. When the `NewsletterService` is instantiated, the `PropertyInjector` looks at the prototype, finds the `Logger` service, and sets the property value immediately. Because of the prototype, the injector never had to "Look" at your `#[Inject]` attribute—it already knew what to do.

### For Humans: What This Means (Story)

It makes your code cleaner. You can use property injection freely because this class ensures the performance is lightning-fast by pre-calculating the rules.

## For Dummies

Imagine you're building a kitchen.

1. **The Slot**: "Dishwasher space". (`$name`)
2. **The Connection**: "220V Power + Water". (`$type`)
3. **The Rule**: "Must have a dishwasher before the kitchen is finished". (`$required`)

### For Humans: What This Means (Walkthrough)

It's a "Reserved Slot" description. It identifies the slot and the rules for filling it.

## How It Works (Technical)

The `PropertyPrototype` is a specialized DTO:

1. **Structure**: It holds 6 facts about a property. These are extracted from `ReflectionProperty` during the `Think` phase.
2. **Hydration**: It is used exclusively by the `PropertyInjector`. The injector iterates through an array of these prototypes and performs a `ReflectionProperty->setValue()` call for each one.
3. **Readonly Check**: By convention, `PropertyPrototype` is only created for properties that are NOT `readonly`. This check happens in the `PrototypeAnalyzer`.
4. **Immutability**: Being a `readonly` class, it is safe to cache and share.

### For Humans: What This Means (Technical)

It is a "Metadata DTO". Its only job is to transport information from the Analyzer to the Injector as efficiently as possible.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Property-level metadata model.
- **Collaborator**: Contained by `ServicePrototype`, used by `PropertyInjector`.

### For Humans: What This Means (Architecture)

It is part of the "Static Model" layer. It’s one of the three core building blocks of a class blueprint (Constructor, Properties, Methods).

## Methods

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Hydrates the property metadata from a flat array. Vital for AOT compilation support.

#### For Humans: What This Means

"Restore the property rules from a saved file."

### Method: toArray()

#### Technical Explanation: toArray

Converts the rules into a simple array for storage.

#### For Humans: What This Means

"Save the property rules for later."

## Risks & Trade-offs

- **Encapsulation**: Property injection often targets `private` or `protected` variables. While it is powerful, it means the container is reaching deep into your objects.
- **Circular Dependencies**: Property injection is often used to break circular dependencies, but if not managed carefully, it can lead to objects that are "Partially Built" for a split second.

### For Humans: What This Means (Risks)

"Watch out for deep cuts". The container is filling your variables directly. If you have special logic in your `setLogger()` method, property injection MIGHT bypass it if you use a direct property instead of a method.

## Related Files & Folders

- `PropertyInjector.php`: The "Worker" who actually fills the properties.
- `ServicePrototype.php`: The larger manual that contains this property plan.
- `Inject.php`: The attribute that triggers the creation of this prototype.

### For Humans: What This Means (Relationships)

The **Attribute** triggers the **Plan** (this class), which is executed by the **Injector**.
