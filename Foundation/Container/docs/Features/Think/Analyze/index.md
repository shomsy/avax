# Features/Think/Analyze

## What This Folder Represents

This folder contains the "Vision System" of the container—the specialized logic that physically observes and translates
your PHP code markers into a structured plan.

Technically, `Features/Think/Analyze` is the implementation layer for PHP Reflection within the container. It contains
the classes that "read" your constructors, properties, and methods. Their responsibility is to bridge the gap between
the chaotic world of raw PHP source code and the highly structured world of the Container's internal Prototypes. They
handle the complexity of type-hints, attributes, and visibility rules so that the rest of the container can stay "Blind"
to the raw source code and focus on building objects.

### For Humans: What This Means (Represent)

This is the **Reading Room**. If the container was a chef, this folder is where the chef sits down to read your recipe
book (Your Code) and writes down a clean list of ingredients (The Prototype).

## Terminology (MANDATORY, EXPANSIVE)

- **Static Analysis Filter**: The process of ignoring things that don't matter to the container (like private helper
  methods) and focusing only on "Injection Points".
    - In this folder: Part of the logic in `PrototypeAnalyzer`.
    - Why it matters: Keeps the blueprints small and focused only on what needs to be wired.
- **Type Discovery**: Figuring out the "Class" of a variable even if it uses modern PHP syntax.
    - In this folder: Handled by `ReflectionTypeAnalyzer`.
    - Why it matters: This is what makes "Autowiring" possible.
- **Metadata Extraction**: Specifically looking for attributes like `#[Inject]` and converting their arguments into
  data.
    - In this folder: Handled by both Analyzers.
    - Why it matters: It’s the primary way for you to "Talk" to the container through your code.

### For Humans: What This Means (Terminology)

**Filtering** is "Ignoring the noise". **Discovery** is "Identifying the parts", and **Extraction** is "Reading the
stickers".

## Think of It

Think of a **Custom Car Shop**:

1. **ReflectionTypeAnalyzer**: The person who looks at a part and identifies its serial number, size, and type.
2. **PrototypeAnalyzer**: The person who looks at the whole car and makes a master list of all those parts.

### For Humans: What This Means (Analogy)

One person identifies the "Single Bricks" (Types), and the other person identifies how those bricks form "The
Building" (The Class).

## Story Example

You write a new class called `UserDashboard`. You ask the container to build it. The system looks in the `Analyze`
folder. The **ReflectionTypeAnalyzer** looks at the `UserDashboard` constructor and sees it needs an `AuthManager`. It
verifies the `AuthManager` is a real class. Then the **PrototypeAnalyzer** takes that info and writes down a blueprint:
`UserDashboard` -> `needs AuthManager`. This blueprint is then used by the rest of the container to actually build your
dashboard.

### For Humans: What This Means (Story)

It turns your "Ideas" into "Instructions". You don't have to explain your class to the container; it just "Looks" at it
and figures it out.

## For Dummies

If you're wondering "How does the container know what I typed in my editor?", this is how.

1. **Open the Class**: Use PHP Reflection to open the class file in memory.
2. **Read the Constructor**: See what's inside the brackets `(...)`.
3. **Read the Attributes**: See if there are any `#[Inject]` tags.
4. **Create a Map**: Write down a simple map of those findings.

### For Humans: What This Means (Walkthrough)

It's the "Reader" and "Translator" of your code.

## How It Works (Technical)

The analysis process is a collaboration between two specialists:

1. The `ReflectionTypeAnalyzer` is the low-level expert. It handles the "Gory Details" of PHP types (Union,
   Intersection, Nullable) and provides a fast, cached way to access `ReflectionClass` objects.
2. The `PrototypeAnalyzer` is the high-level expert. It uses the low-level expert to scan a class and build a complete
   `ServicePrototype` DTO.
   This folder is intentionally "Read-Only"—it never modifies your classes; it only observes them.

### For Humans: What This Means (Technical)

It converts "Runtime Code" into "Static Data". It is the bridge between the PHP execution engine and the Container's
planning logic.

## Architecture Role

- **Lives in**: `Features/Think/Analyze`
- **Role**: Data Extraction and Normalization.
- **Goal**: To produce a perfect `ServicePrototype`.

### For Humans: What This Means (Architecture)

It is the "Input Specialist" of the Intelligence Layer.

## What Belongs Here

- Classes that use `ReflectionClass`, `ReflectionMethod`, etc.
- Logic that parses Attributes.
- Type-to-string normalization logic.

### For Humans: What This Means (Belongs)

The "Eyes" of the container live here.

## What Does NOT Belong Here

- **Caching the blueprints**: (lives in `Think/Cache`).
- **Verifying if the blueprint is valid**: (lives in `Think/Verify`).
- **Executing the construction**: (lives in `Actions/Instantiate`).

### For Humans: What This Means (Not Belongs)

This folder only **Looks**. It doesn't remember (Cache), it doesn't judge (Verify), and it doesn't build (Action).

## How Files Collaboration

The `ReflectionTypeAnalyzer` provides the "Microscope", and the `PrototypeAnalyzer` uses it to write the "Lab Report" (
The Prototype). Together, they provide the intelligence that makes the container "Smart".

### For Humans: What This Means

The **Translator** (TypeAnalyzer) and the **Analyst** (PrototypeAnalyzer) work together to create the instruction manual
for your code.
