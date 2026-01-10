# Features/Think/Model

## What This Folder Represents

This folder contains the "Memory Cells" of the container—the immutable data structures that store everything the container has learned about your application's structure.

Technically, `Features/Think/Model` is the DTO (Data Transfer Object) layer for the container's intelligence. It contains a hierarchy of objects: `ServicePrototype` (The whole class), `MethodPrototype` (One function), `PropertyPrototype` (One variable), and `ParameterPrototype` (One argument). These models are designed to be high-performance, serializable, and thread-safe. They are the "Static Language" used by the container to communicate requirements between the Analysis phase and the Execution phase.

### For Humans: What This Means (Represent)
This is the **Filing Cabinet**. If all the other folders are people (The Analyst, The Builder, The Librarian), this folder is the set of **Official Forms** they all fill out and read. Every form has a specific set of boxes to check, ensuring that everyone is using the same language and the same facts.

## Terminology (MANDATORY, EXPANSIVE)

- **Immutable Serialization**: The ability for an object to be turned into a string and back without ever changing its internal meaning.
  - In this folder: Every model supports `toArray()` and `fromArray()`.
  - Why it matters: This is the foundation of the container's speed.
- **Blueprint Hierarchy**: The nested relationship between a class, its methods, and its parameters.
  - In this folder: `ServicePrototype` -> `MethodPrototype` -> `ParameterPrototype`.
  - Why it matters: It allows the container to handle complex classes with many different ways of receiving data.
- **LRU Registry**: A smart memory storage for these models.
  - In this folder: Handled by `PrototypeRegistry`.
  - Why it matters: It keeps the "Hot" (frequently used) models in RAM for instant speed.
- **Heuristic Auditing**: Using these models to calculate if a class is "Too Complex".
  - In this folder: Handled by `PrototypeReport`.
  - Why it matters: It gives the developer feedback on their code quality.

### For Humans: What This Means (Terminology)

**Serialization** is "Freezing and Thawing". **Hierarchy** is "The Big Plan made of Tiny Plans". **LRU Registry** is "The Active Pile", and **Auditing** is "The Health Check".

## Think of It

Think of a **Set of Blueprints for a Modular Space Station**:

1. **ServicePrototype**: The master design for a whole "Module" (e.g. The Lab).
2. **MethodPrototype**: The design for a "Docking Port" or a "Power Interface".
3. **PropertyPrototype**: The design for an "Observation Window" or a "Sensor Array".
4. **ParameterPrototype**: The specific "Bolt" or "Wire" needed for a connection.

### For Humans: What This Means (Analogy)

One model represents the "Whole Room", while the others represent the "Doors", "Windows", and "Bolts" that make the room work.

## Story Example

You are building an app with a `UserRegistration` class. You add an `EmailService` to the constructor.

1. The `Analyze` folder "Scans" your class.
2. It fills out a `ServicePrototype` (The Module).
3. Inside that, it fills out a `MethodPrototype` for the constructor (The Docking Port).
4. Inside THAT, it fills out a `ParameterPrototype` for the `$email` variable (The Bolt).
Now, this whole "File" is saved in the `Model` folder's structures. The next time you need to build a `UserRegistration`, the container just pulls this file and follows the instructions.

### For Humans: What This Means (Story)

It makes the container's knowledge "Durable". Once it learns something, it has a precise, structured way to remember it forever.

## For Dummies

If you're wondering "What exactly is saved in the cache?", it's these classes.

1. **Service**: The name of the class and its instantiability.
2. **Method**: The name of the function and its parameters.
3. **Property**: The name of the variable and its type.
4. **Parameter**: The name, type, and default value.

### For Humans: What This Means (Walkthrough)

It's the "Schema" of the container's knowledge.

## How It Works (Technical)

The "Model" folder provides the data contracts for the rest of the system:

1. **Statelessness**: These objects contain data, but no logic for *performing* actions. They are "Passive".
2. **AOT Compatibility**: They use `__set_state` to support extremely fast loading from PHP files.
3. **Validation**: By enforcing regular constructors (instead of just arrays), these models ensure that the data within them is always valid (e.g. types are strings, booleans are booleans).

### For Humans: What This Means (Technical)

It is the "Standardized Data Format" of the container. It’s like a barcode—every part of the container knows how to read it.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Data Definition and Knowledge Storage.
- **Goal**: To provide an immutable, serializable representation of code.

### For Humans: What This Means (Architecture)

It is the "Information Base" of the Intelligence Layer.

## What Belongs Here

- Plain Old PHP Objects (POPOs) that represent code structure.
- Logic for converting these objects to and from arrays.
- Registries and reports specifically for these objects.

### For Humans: What This Means (Belongs)
Anything that is "A piece of information about a class" lives here.

## What Does NOT Belong Here

- **Actually analyzing the code**: (lives in `Think/Analyze`).
- **Actually building the objects**: (lives in `Features/Actions`).
- **Managing the cache files**: (lives in `Think/Cache`).

### For Humans: What This Means (Not Belongs)
This folder is the **Knowledge** itself, not the **Teacher** (Analyze) or the **Library** (Cache).

## How Files Collaboration

The `ServicePrototype` is the "Parent". It contains lists of `MethodPrototype` and `PropertyPrototype`. Each `MethodPrototype` then contains a list of `ParameterPrototype`. Together, they form a "Tree" that describes your entire class in perfect detail.

### For Humans: What This Means

The **Master Blueprint** (Service) is made of **Task Blueprints** (Method), which are made of **Item Blueprints** (Parameter).
