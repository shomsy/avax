# Features/Think/Prototype

## What This Folder Represents

This folder contains the "Manufacturing Plant" for service blueprints—the orchestration layer that manages how class
information is created, stored, and compiled.

Technically, `Features/Think/Prototype` is the management layer for `ServicePrototype` metadata. While the `Analyze`
folder "looks" at code, this folder "manages" the results of that looking. It includes the factories that coordinate
between analysis and caching, the builders that allow for manual blueprint creation, and the dumpers that allow for "
Freezing" those blueprints into static files. It is the bridge between the "Reflection" world and the "Production"
world.

### For Humans: What This Means (Represent)

This is the **Librarian's Desk**. If the `Analyze` folder is the set of eyes reading the books, this folder is where the
Librarian sits to decide which books to read, which notes to keep, and how to organize the shelves so others can find
the info instantly.

## Terminology (MANDATORY, EXPANSIVE)

- **Prototype Orchestration**: The high-level logic that decides whether to "Think" (Analyze code) or "Remember" (Read
  from Cache).
    - In this folder: Handled by `ServicePrototypeFactory`.
    - Why it matters: This is what keeps the container from being slow.
- **Blueprint Lifecycle**: The stages a blueprint goes through: Creation -> Verification -> Caching -> Execution.
    - In this folder: Managed by the various builders and factories.
    - Why it matters: Ensures that every piece of data the container uses is fresh and valid.
- **Ahead-of-Time (AOT) Compilation**: Converting dynamic rules into static code *before* the application runs.
    - In this folder: Handled by `CompiledPrototypeDumper`.
    - Why it matters: It is the "Ultimate Weapon" for production performance.
- **Fluent Construction**: Building complex blueprints using a "Readable" set of methods.
    - In this folder: Handled by `ServicePrototypeBuilder`.
    - Why it matters: Makes it easy for developers to write custom container rules.

### For Humans: What This Means (Terminology)

**Orchestration** is "The Manager's Plan". **Lifecycle** is "Living and Aging". **AOT Compilation** is "Pre-Printing",
and **Fluent Construction** is "Easy Ordering".

## Think of It

Think of a **Centralized Print Shop**:

1. **Request**: Someone wants a specialized business card.
2. **Factory (Manager)**: Checks if the design is already saved in the digital database.
3. **Builder (Designer)**: If they want a custom design from scratch, the designer uses a drawing board to create it.
4. **Dumper (Printing Press)**: Once the design is finished, the press prints 1,000 copies so they never have to design
   it again.

### For Humans: What This Means (Analogy)

This folder manages the "Process" of turning an idea (Your Code) into a physical, useful product (The Blueprint).

## Story Example

You are building a high-traffic e-commerce site. You use the `ServicePrototypeFactory` to make sure your
`CheckoutService` is analyzed and cached during development. But for production, you want more. You use the
`CompiledPrototypeDumper` to export all your blueprints into a single PHP file. Now, when a customer hits "Buy", the
container doesn't "Think", it doesn't "Analyze", and it doesn't even "Check the Cache"—it just loads the one file and
executes. You just turned a complex "Living" system into a "Precision Machine".

### For Humans: What This Means (Story)

It enables you to move from "Flexible Development" to "Rigid, High-Power Production" with almost zero effort.

## For Dummies

If you're wondering "How does all this reflection stuff not slow down my site?", this folder is the reason.

1. **Manage**: Use a Factory to make sure we only reflect a class once.
2. **Flexible**: Use a Builder if the automatic reflection can't figure something out.
3. **Fast**: Use a Dumper to save everything into a simple file for big production servers.

### For Humans: What This Means (Walkthrough)

It's the "Control Panel" for the container's intelligence.

## How It Works (Technical)

The "Prototype" folder acts as an orchestration layer:

1. The `ServicePrototypeFactory` provides a "Cache-First" lookup service.
2. The `ServicePrototypeBuilder` provides a "Programmatic" alternative to `PrototypeAnalyzer`.
3. The `CompiledPrototypeDumper` provides a "Persistence" path to static PHP files.
   This folder uses the `Models` created by the `Analyze` folder but focuses on the "Delivery" and "Storage" of those
   models.

### For Humans: What This Means (Technical)

It is the "Dispatcher". It doesn't do the "Thinking" itself, but it knows which "Thinker" to call and where to save
their notes.

## Architecture Role

- **Lives in**: `Features/Think/Prototype`
- **Role**: Blueprint Management and Serialization.
- **Goal**: Reliable, high-speed access to class metadata.

### For Humans: What This Means (Architecture)

It is the "Operations Manager" of the Intelligence Layer.

## What Belongs Here

- Classes that coordinate the creation of prototypes.
- Classes that serialize or "Dump" prototypes for storage.
- Builders for manual prototype creation.

### For Humans: What This Means (Belongs)

Anything that manages "Blueprints" lives here.

## What Does NOT Belong Here

- **Actually analyzing the PHP code**: (lives in `Think/Analyze`).
- **Actually storing the data in RAM/Disk**: (lives in `Think/Cache`).
- **Verifying the blueprints**: (lives in `Think/Verify`).

### For Humans: What This Means (Not Belongs)

This folder only **Manages the Process**. It doesn't do the "Research" or "Storage".

## How Files Collaboration

The `ServicePrototypeFactory` is the main gate. It uses the `Analyzer` to get info and the `Cache` to remember it.
Meanwhile, the `Dumper` can take everything in the `DefinitionStore` and turn it into a physical file for production.

### For Humans: What This Means

The **Gatekeeper** (Factory) uses the **Researcher** (Analyzer) and the **Librarian** (Cache) to get you what you need.
