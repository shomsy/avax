# Features/Think

## What This Folder Represents

This folder contains the "Intelligence Layer" of the container—the specialized logic that analyzes your code structure
to understand how to build and wire it.

Technically, `Features/Think` is responsible for Static and Dynamic Analysis of PHP classes. It uses Reflection to peak
inside your code, find your constructors, properties, and methods, and then converts that raw information into
high-performance "Prototypes" (Blueprints). This allows the container to do the "Hard Thinking" only once, caching the
results so that repeated resolutions are extremely fast.

### For Humans: What This Means (Summary)

This is the **Architect's Office**. This is where the container looks at your source code and draws up the blueprints.
Once the blueprints are finished, the container knows exactly how to build your classes without having to read the code
again.

## Terminology (MANDATORY, EXPANSIVE)

- **Prototype Building**: The process of transforming a raw PHP class into a structured data model (a Prototype).
    - In this folder: Mapped across `Analyze/`, `Prototype/`, and `Model/`.
    - Why it matters: Turns "Code" into "Data" that the container can easily understand and manipulate.
- **Reflection Analysis**: Using PHP's built-in tools to "Look inside" a class.
    - In this folder: Handled by `Analyze/PrototypeAnalyzer`.
    - Why it matters: This is the source of truth for all autowiring.
- **Verification**: Checking a blueprint for errors (like circular references or unresolvable types) *before* we try to
  use it.
    - In this folder: Handled by `Verify/`.
    - Why it matters: Allows the container to catch configuration errors early, giving you clearer error messages.
- **Prototype Cache**: Storing the finished blueprints in memory or on disk.
    - In this folder: Handled by `Cache/`.
    - Why it matters: Ensures the container stays fast even in massive applications with thousands of classes.

### For Humans: What This Means (Terminology)

**Prototype Building** is "Blueprint Creation". **Reflection Analysis** is "Studying the Code". **Verification** is "
Double-Checking the Plan", and **Caching** is "Saving the Results".

## Think of It

Think of a **Custom Furniture Factory**:

1. **Analyze**: A specialist looks at a wooden chair and writes down exactly how many screws, legs, and boards it needs.
2. **Prototype**: They draw a 1-page summary of those needs (The Blueprint).
3. **Cache**: They put that summary in a filing cabinet.
4. **Actions**: The next time someone wants that chair, the workers just grab the summary from the cabinet and start
   building.

### For Humans: What This Means (Analogy)

Instead of guessing how to build a chair every time, the factory "Thinks" once, writes it down, and then just "Acts"
from then on.

## Story Example

You create a new `UserService` class. The first time you ask the container for it, the **Think** system wakes up. It
reads your constructor, see it needs an `Emailer`, and sees you have a `#[Inject]` property for a `Logger`. It creates a
`ServicePrototype` containing all this info, verifies it’s correct, and stores it in the cache. The next 10,000 times
you ask for a `UserService`, the container doesn't look at your code at all—it just reads the 1-page "Think" summary and
builds it instantly.

### For Humans: What This Means (Story)

It’s what makes the container "Smart". It doesn't just guess; it learns your code structure once and then uses that
knowledge to be incredibly efficient.

## For Dummies

If you're wondering "How does the container know what's in my constructor?", this is where the magic happens.

1. **Peep**: Look at the class code using Reflection.
2. **Note**: Write down all the parameters, types, and attributes.
3. **Check**: Make sure the plan actually makes sense.
4. **Save**: Keep the note so we don't have to peep again.

### For Humans: What This Means (Walkthrough)

It's a "Read Once, Use Many" system for your code's architecture.

## How It Works (Technical)

The "Think" process follows a linear pipeline:

1. **Analysis**: The `PrototypeAnalyzer` uses `ReflectionClass` to walk through constructors, properties, and methods.
   It uses a `ReflectionTypeAnalyzer` to extract and normalize type information.
2. **Modeling**: The results are stored in immutable DTOs found in `Model/` (like `ServicePrototype`).
3. **Verification**: The prototype is passed through various verifiers to ensure it meets the container's rules (e.g. no
   private constructor autowiring).
4. **Caching**: The final verified prototype is stored in the `PrototypeCache` for future requests.

### For Humans: What This Means (Technical)

It converts "Implicit" code rules into "Explicit" data objects. It bridges the gap between the PHP language and the
Container's resolution engine.

## Architecture Role

- **Lives in**: `Features/Think`
- **Role**: Intelligence, Metadata, and Blueprinting.
- **Consumer**: Used by the `ContainerKernel` and `Actions` to understand what they are building.

### For Humans: What This Means (Architecture)

It is the "Brain" and "Memory" of the container.

## What Belongs Here

- Any class that uses Reflection to analyze code.
- Data models that represent class architecture (Prototypes).
- Verification logic for class structures.

### For Humans: What This Means (Belongs)

If it helps the container "Understand" code, it lives here.

## What Does NOT Belong Here

- **Actually building objects**: (lives in `Actions/Instantiate`).
- **Executing functions**: (lives in `Actions/Invoke`).
- **Defining manual rules/bindings**: (lives in `Features/Define`).

### For Humans: What This Means (Not Belongs)

This folder only **Analyzes**. It doesn't act, and it doesn't store user-defined rules.

## How Files Collaboration

The `Analyze` folder creates the models in the `Model` folder. The `Verify` folder checks those models, and the `Cache`
folder stores them. The `Prototype` folder acts as the central coordinator for this entire flow.

### For Humans: What This Means (Collaboration)

The "Researcher" (Analyze) writes the "Report" (Model), the "Editor" (Verify) checks the report, and the "Librarian" (
Cache) saves it.
