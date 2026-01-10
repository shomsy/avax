# PrototypeAnalyzer

## Quick Summary

- This file serves as the "Detective" that investigates your PHP classes to see how they are built.
- It exists to extract metadata (blueprints) from your source code so the container knows what objects your class needs without you telling it manually.
- It removes the complexity of manual configuration by using **Autowiring**—reading your type-hints and attributes to build a plan.

### For Humans: What This Means (Summary)

This is the **Architect** of the container. When you write a class, the Architect studies it and says: "I see you need a Database, a Logger, and a Config object. I've noted that down in a blueprint so we can build you correctly later."

## Terminology (MANDATORY, EXPANSIVE)

- **Instantiable Check**: Verifying if a class *can* be created (e.g., it’s not abstract and not an interface).
  - In this file: The `isInstantiable()` check at the start of `analyze()`.
  - Why it matters: Prevents the container from trying to build things that can't be built.
- **Blueprint Mapping**: Converting complex PHP Reflection objects into simple, lightweight "Prototype" objects.
  - In this file: Turning `ReflectionParameter` into `ParameterPrototype`.
  - Why it matters: Makes the information easy to store, cache, and read by the rest of the container.
- **Attribute Discovery**: Finding special markers like `#[Inject]` on your properties or methods.
  - In this file: Handled in `analyzeProperties()` and `analyzeMethods()`.
  - Why it matters: This is how you give "Special Instructions" to the container about how to fill your class variables.
- **Type Normalization**: Figuring out exactly what class an argument needs, even if it uses complex PHP features like Union Types (`ClassA|ClassB`).
  - In this file: The `resolveType()` method.
  - Why it matters: Ensures the container doesn't get confused by modern PHP type-hinting syntax.

### For Humans: What This Means (Terminology)

The Analyzer performs a **Sanity Check** (Instantiable), creates a **Lightweight Blueprint** (Mapping), looks for **Special Markers** (Attributes), and handles **Modern PHP Types** (Normalization).

## Think of It

Think of a **Structural Engineer inspecting a building**:

1. **Walkthrough**: They walk through the building (`Reflection`).
2. **Measurements**: They note down where the plugs, pipes, and supports are (`Attributes` and `Parameters`).
3. **Drafting**: They draw a 1-page summary of the building's infrastructure (`ServicePrototype`).
4. **Filing**: They hand that summary to the factory so they can build an identical building somewhere else.

### For Humans: What This Means (Analogy)

The Engineer (Analyzer) doesn't build the house; they just write the report that tells the builder (Instantiator) how to build it correctly.

## Story Example

You create a `PaymentService` class. You add a `#[Inject('stripe.api.key')]` attribute to a `$apiKey` property and type-hint `LoggerInterface $logger` in your constructor. You ask the container for the service. The **PrototypeAnalyzer** runs. It "Sees" the logger in the constructor and adds it to the blueprint's "Shopping List". It "Sees" the attribute on the property and adds it to the "Post-Construction list". It combines everything into a single `ServicePrototype` and hands it to the container. The container now has everything it needs to build you a perfect `PaymentService`.

### For Humans: What This Means (Story)

It’s the "Magic" behind autowiring. You just write your code naturally, and this class "Reads your mind" (by reading your code) to figure out what you want.

## For Dummies

Imagine you're at a Lego store.

1. **Peek**: You look inside a pre-built Lego car. (`Reflection`)
2. **Identify**: You see it has 4 wheels, 2 seats, and a steering wheel. (`Parameters`)
3. **Note**: You find a special sticker on the trunk that says "Put a spare tire here". (`Attribute`)
4. **Plan**: You write down the list of bricks needed to build that exact car. (`Prototype`)

### For Humans: What This Means (Walkthrough)

It's a "Look and Plan" system. It does all the difficult work of peering into your code so the rest of the container can stay fast and simple.

## How It Works (Technical)

The `PrototypeAnalyzer` operates as a factory for `ServicePrototype` objects.

1. It uses `ReflectionClass` to gain access to the target's internal structure.
2. **Constructor**: It pulls the constructor parameters and maps each one into a `ParameterPrototype`, capturing type-hints, default values, and nullability.
3. **Properties**: It scans all properties. If it finds the `#[Inject]` attribute, it extracts the target service ID (from the attribute or the type-hint) and creates a `PropertyPrototype`. It skips `readonly` properties because they cannot be injected after the object is built.
4. **Methods**: It scans all other methods. Any method with an `#[Inject]` attribute is mapped into a `MethodPrototype` (setter injection).
5. **Normalization**: It uses a separate `ReflectionTypeAnalyzer` to ensure that complex types (like Union or Named types) are correctly resolved into service identifiers.

### For Humans: What This Means (Technical)

It is a comprehensive "Class Scanner". It examines every possible way a dependency could enter your class and records it in a standardized format.

## Architecture Role

- **Lives in**: `Features/Think/Analyze`
- **Role**: Static Metadata Extraction.
- **Collaborator**: `ReflectionTypeAnalyzer`, `ServicePrototype`.

### For Humans: What This Means (Architecture)

It is the "Input Specialist" for the container's internal intelligence system.

## Methods

### Method: analyze(string $class)

#### Technical Explanation: analyze

The main entry point. Orchestrates the full analysis of a class and returns the final `ServicePrototype`.

#### For Humans: What This Means

"Study this class and give me the blueprint."

### Method: analyzeProperties(...)

#### Technical Explanation: analyzeProperties

Specialized scanner for finding and mapping `#[Inject]` attributes on class variables.

#### For Humans: What This Means

"Find all the variables that need data injected into them."

### Method: analyzeParameter(...)

#### Technical Explanation: analyzeParameter

Translates a single PHP parameter reflector into a container-friendly prototype.

#### For Humans: What This Means

"Understand what one single variable needs."

## Risks & Trade-offs

- **Reflection is Expensive**: Calling the Analyzer on every request would be very slow. It **MUST** be used in conjunction with a Cache (see `PrototypeCache`).
- **Private Access**: The Analyzer can see private properties and methods, which is why property injection works. However, this means it "Breaks" your encapsulation intentionally to help you.
- **Readonly Conflict**: It intentionally ignores `readonly` properties for post-construction injection, because PHP will crash if you try to set those after `__construct()`.

### For Humans: What This Means (Risks)

It’s a powerful but "Heavy" tool. Don't run it more than you have to (use caching!). And remember that `readonly` variables can ONLY be filled in the constructor.

## Related Files & Folders

- `ReflectionTypeAnalyzer.php`: The helper that understands PHP's complex type-hinting system.
- `ServicePrototype.php`: The "Blueprint" object produced by this analyzer.
- `Inject.php`: The attribute that this analyzer is looking for.

### For Humans: What This Means (Relationships)

The **Analyzer** reads the **Attributes** using the **Type Specialist** to produce the **Blueprint**.
