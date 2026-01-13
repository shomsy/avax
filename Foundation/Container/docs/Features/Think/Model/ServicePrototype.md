# ServicePrototype

## Quick Summary

- This file defines the final "Data Model" (Blueprint) of a single PHP class inside the container.
- It exists to act as a "Unified Specification"—it combines everything the container knows about a class (Constructor,
  Properties, Methods) into a single, immutable object.
- It removes the need for the container to hold onto "Reflection" objects, which are memory-heavy and slow.

### For Humans: What This Means (Summary)

This is the **Master Instruction Manual** for a service. When the container wants to build an object, it doesn't look at
your code; it looks at this manual. The manual says exactly what parts to buy (Dependencies), where to put them (
Injection Points), and if the construction is even possible.

## Terminology (MANDATORY, EXPANSIVE)

- **Immutable DTO (Data Transfer Object)**: An object that, once created, can never be changed.
    - In this file: The `readonly` class definition.
    - Why it matters: This makes the blueprint safe to share across your entire application. You can be 100% sure that
      if you read the blueprint twice, it will say the same thing both times.
- **Instantiability Flag**: A simple Yes/No marker telling the container if it can use the `new` keyword on this class.
    - In this file: The `$isInstantiable` property.
    - Why it matters: Prevents crashing when a user accidentally asks the container to build an Interface.
- **Serialization**: The process of "Saving" an object to a string or array so it can be stored in a file.
    - In this file: The `toArray()` and `fromArray()` methods.
    - Why it matters: This is what allows for **High-Performance Caching**. The container can save 1,000 blueprints to a
      file and load them all in a single millisecond.
- **__set_state**: A special PHP method used when code is generated via `var_export()`.
    - In this file: Used for AOT (Ahead-of-Time) compilation.
    - Why it matters: It’s the secret behind why compiled containers are so fast—they load pre-built objects directly
      into memory.

### For Humans: What This Means (Terminology)

The Service Prototype is an **Immutable** (Unchangeable) blueprint that tracks **Instantiability** (Can we build it?)
and supports **Serialization** (Saving it to disk) via **__set_state** (Direct loading).

## Think of It

Think of a **Set of Architectural Blueprints**:

1. **Page 1 (Constructor)**: The structural supports and foundation.
2. **Page 2 (Properties)**: The electrical outlets and plumbing ports.
3. **Page 3 (Methods)**: The appliances that need to be plugged in after moving in.
4. **Cover Page (isInstantiable)**: A checkbox saying "This is a real building plan" (vs. a theoretical design).

### For Humans: What This Means (Analogy)

The blueprint doesn't contain the physical materials (the real objects); it just contains the "Dimensions" and "
Requirements" so the construction crew knows what to do.

## Story Example

You are building a `ReportGenerator`. It needs a `Database` in the constructor and a `Logger` in a property. The
container analyzes your class once and produces a **ServicePrototype**. This prototype is saved to your production
server's disk. When a user requests a report, the container doesn't "Think" or "Reflect". It just reads the
`ServicePrototype`, sees "Needs Database in constructor" and "Needs Logger in property", fetches those two items, and
hands you the finished `ReportGenerator`.

### For Humans: What This Means (Story)

It turns "Complex Analysis" into "Simple Reading". It bridges the gap between your dynamic code and a high-speed
production engine.

## For Dummies

Imagine you're ordering a custom car.

1. **Specifications**: You want a 4-door, red car with a sunroof. (`ServicePrototype`)
2. **Chassis**: It needs an engine. (`Constructor`)
3. **Interiors**: It needs leather seats. (`Properties`)
4. **Testing**: Can this car actually be built? (`isInstantiable`)
5. **Final Order**: You hand the spec sheet to the factory. (`The Blueprint`)

### For Humans: What This Means (Walkthrough)

It's the "Full Description" of a service. Everything the container needs to know is right here in this one object.

## How It Works (Technical)

The `ServicePrototype` is the terminal model in the analysis pipeline:

1. **Data Structure**: It is a composite object. It contains one `MethodPrototype` for the constructor, a list of
   `PropertyPrototype` objects, and a list of `MethodPrototype` objects for setters.
2. **State Reconstruction**: Through `fromArray()` and `__set_state()`, it can be rebuilt from static data without
   re-running any reflection. This makes it the backbone of the container's AOT (Ahead-of-Time) compilation feature.
3. **Validation (Implicit)**: By holding this object, the container implicitly knows that the target class was found and
   inspected. If `$isInstantiable` is false, it knows to throw an exception if the user tries to `make()` it.
4. **Immutability**: Because it is `readonly`, it is completely thread-safe.

### For Humans: What This Means (Technical)

It is a "Stateless Data Record". It exists to hold information as efficiently as possible while being easy for other
classes (like the `Instantiator`) to read.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Master Blueprint DTO.
- **Collaborator**: Produced by `PrototypeAnalyzer`, used by `Instantiator` and `InjectDependencies`.

### For Humans: What This Means (Architecture)

It is the "Information Hub" that connects the Planning phase to the Action phase.

## Methods

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Static factory that hydrates a new prototype from a raw array. Recursively hydrates nested method and property
prototypes.

#### For Humans: What This Means

"Rebuild the blueprint from a saved file."

### Method: toArray()

#### Technical Explanation: toArray

Converts the prototype tree into a multi-dimensional associative array.

#### For Humans: What This Means

"Prepare the blueprint to be saved in a file."

## Risks & Trade-offs

- **Structure Drift**: If you change the code of `ServicePrototype` (e.g., add a new property), existing cached versions
  on disk might become incompatible. You must clear your cache whenever you update the container's core code.
- **Size**: For very complex classes with dozens of properties and methods, the prototype object can become large.
  However, it is still much smaller than a living `ReflectionClass`.

### For Humans: What This Means (Risks)

"It's a Screenshot". Like a photo, it reflects what the class looked like at the moment it was analyzed. If you change
the code, you need to take a "New Photo" (Re-analyze and refresh the cache).

## Related Files & Folders

- `MethodPrototype.php`: The blueprint for one specific function/method.
- `PropertyPrototype.php`: The blueprint for one specific property.
- `PrototypeAnalyzer.php`: The "Agent" who creates this object.

### For Humans: What This Means (Relationships)

The **Analyzer** creates this **Master Blueprint**, which is made up of smaller **Method** and **Property** blueprints.
