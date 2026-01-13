# Features/Actions/Resolve

## What This Folder Represents

This folder contains the "Resolution Engine"— the core industrial machinery that transforms a service request (like an
interface name) into a living object or value.

Technically, `Features/Actions/Resolve` handles the heavy lifting of the dependency injection lifecycle. It doesn't
just "find" services; it orchestrates their construction, handles contextual overrides (exceptions to the rules), and
manages autowiring. It is the bridge between a static "Definition" and a runtime "Instance".

### For Humans: What This Means (Summary)

This is the **Fulfillment Center**. When you ask the container for a service, this folder is where the staff checks the
order, finds the right blueprint, builds the product, and makes sure it's delivered to you exactly how you requested it.

## Terminology (MANDATORY, EXPANSIVE)

- **Resolution Engine**: The main coordinator that follows rules to create objects.
    - In this folder: The `Engine` class.
    - Why it matters: It ensures that objects are built consistently every time.
- **Dependency Resolution**: The process of finding and building the "hidden" objects a class needs in its constructor.
    - In this folder: The `DependencyResolver` class.
    - Why it matters: This is what makes "Autowiring" possible.
- **Contextual Match**: Finding a specific rule that only applies to the current situation.
    - In this folder: Logic inside the `Engine`.
    - Why it matters: It allows you to say "Give everyone a Logger, but give the Database class a *special* Logger."
- **Autowiring**: Building a class automatically just by looking at the types in its constructor.
    - In this folder: Enabled by the `Engine` using the `Instantiator`.
    - Why it matters: It saves you from having to register every single class in your application manually.

### For Humans: What This Means (Terminology)

The **Resolution Engine** is the "Coordinator". **Dependency Resolution** is "Finding the missing pieces". **Contextual
Matching** is "Handling Special Requests", and **Autowiring** is "Smart Building".

## Think of It

Think of a **Custom PC Assembly Shop**:

1. **Engine**: The lead technician who reads the customer's order.
2. **Dependency Resolver**: The person who goes to the back warehouse to find the specific CPU, RAM, and GPU that the
   motherboard needs.
3. **Definition Store**: The catalog of parts and special deals.

### For Humans: What This Means (Analogy)

You don't just "get a PC". You order a specific model, and the shop (this folder) coordinates getting all the parts and
putting them together for you correctly.

## Story Example

Imagine your app needs a `UserExporter`. You ask the container for it. The **Engine** checks the definitions. It sees
that `UserExporter` needs `CsvFormatter`. The **Dependency Resolver** kicks in, looks at the `CsvFormatter` constructor,
builds it, and hands it back to the Engine. The Engine then builds the `UserExporter` with that formatter and gives it
to your app. All you did was ask for one class—this folder handled the entire assembly line.

### For Humans: What This Means (Story)

It turns "One Simple Request" into many "Coordinated Actions" so you don't have to build complex objects yourself.

## For Dummies

If you're wondering "How does the container actually *know* what to build?", this folder is the answer.

1. **Check for Special Rules**: "Is there a specific instruction for this request?"
2. **Find the Blueprint**: "What class or function should I use?"
3. **Find the Dependencies**: "What other objects does THIS object need?"
4. **Build It**: "Assemble everything and return the final result."

### For Humans: What This Means (Walkthrough)

It’s a 4-step process: Rules -> Blueprint -> Parts -> Finished Product.

## How It Works (Technical)

The resolution flow is recursive and FSM-guarded. The `Engine` receives a `KernelContext`, builds a
`ResolutionStageHandlerMap`, and walks states under `ResolutionPipelineController` control: Contextual → Definition →
Autowire to find a candidate; Evaluate to normalize closures/objects/class strings; Instantiate to build class strings
with the `Instantiator`. Each stage records trace entries. `DependencyResolver` drives constructor parameter resolution,
spawning child contexts and repeating the same ordered stages to build a full "Resolution Tree".

### For Humans: What This Means (Technical)

It's like a recursive game of "Ask the container" but with a fixed checklist: check special rules, normal rules,
autowire, evaluate what you found, then build it—writing down every step for debugging.

## Architecture Role

- **Lives in**: `Features/Actions/Resolve`
- **Role**: Fulfillment and Execution.
- **Consumer**: Used by the `ContainerKernel` to satisfy `get()` and `make()` requests.

### For Humans: What This Means (Architecture)

It is the "Brain" of the resolution process.

## What Belongs Here

- The main resolution `Engine`.
- Helpers for resolving constructor and method parameters (`DependencyResolver`).
- Contracts related to resolution results.

### For Humans: What This Means (Belongs)

Any code that helps "Decide what to build" or "Find the parts" lives here.

## What Does NOT Belong Here

- **Caching**: Storing the finished objects (lives in `Operate/Scope`).
- **Actually constructing the raw PHP object**: (lives in `Actions/Instantiate`).
- **Injecting after the object is built**: (lives in `Actions/Inject`).

### For Humans: What This Means (Not Belongs)

This folder is the **Decision Maker** and **Coordinator**, not the warehouse (Cache) or the assembly robot (
Instantiator).

## How Files Collaboration

The `Engine` is the leader. It uses the `DependencyResolver` to handle parameter lists. The `DependencyResolver` in turn
talks back to the `Container` to resolve each parameter, creating a feedback loop that continues until the entire object
graph is built.

### For Humans: What This Means (Collaboration)

The files work together like a project manager (Engine) and a sourcing agent (Resolver).
