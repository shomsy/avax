# ReflectionTypeAnalyzer

## Quick Summary

- This file serves as the "Universal Translator" for PHP's complex type system.
- It exists to provide a single, high-performance way to understand what a PHP `type-hint` actually means in plain English (strings).
- It removes the complexity of manually handling Union Types, Intersection Types, and Enums by normalizing them into a format the container can use.

### For Humans: What This Means (Summary)

This is the **Linguist** of the container. PHP has many ways of saying "I need a specific type of object" (e.g. `logger`, `?logger`, `FileLogger|CloudLogger`). The Linguist translates all those different ways into a simple "Shopping List" that the container can read and follow.

## Terminology (MANDATORY, EXPANSIVE)

- **Reflection Object**: A special PHP object that lets you "Hold" a piece of code (like a class or method) in your hand and look at its private details.
  - In this file: The `reflectionCache` stores these objects.
  - Why it matters: Using Reflection is the only way to build Autowiring, but it’s slow. Caching these objects is the only way to make it fast.
- **Normalized Type**: A complex PHP type converted into a simple string (e.g. `ReflectionUnionType` converted to `"Logger|FileLogger"`).
  - In this file: Handled by the `formatType()` method.
  - Why it matters: It allows the rest of the container to work with simple strings instead of complex PHP objects.
- **Instantiability**: Whether a class actually has a "Physical Body" that can be built.
  - In this file: The `isInstantiable()` method.
  - Why it matters: Prevents you from trying to "Build a Ghost" (an abstract class or an interface).
- **Injection Attribute Scanning**: Looking for the special `#[Inject]` sticker on code elements.
  - In this file: The `findInjectAttributes()` method.
  - Why it matters: This is the "Secret Language" you use to tell the container exactly how you want your objects to be filled.

### For Humans: What This Means (Terminology)

The Analyzer uses **Reflection** (Its eyes) to see **Instantiability** (Can it be built?) and **Normalizes** (Translates) the types so they can be easily understood, while searching for **Injection Attributes** (Special stickers).

## Think of It

Think of a **Custom Parts Catalog**:

1. **Reflection**: Looking at a physical bolt on a machine.
2. **Normalization**: Measuring the bolt and naming it "M8 x 40mm Steel Bolt" so anyone can order it.
3. **Analyzer**: The person who goes through the machine and creates the list of every bolt, nut, and washer it needs and gives them all standardized names.

### For Humans: What This Means (Analogy)

Instead of every worker having to measure the bolts themselves, the Analyzer (Linguist) measurements once and writes down the names so everyone can use the same terminology.

## Story Example

You have a method `save(User|Admin $user)`. This is a PHP "Union Type". When the container tries to call this method, it asks the **ReflectionTypeAnalyzer** what `$user` is. Specialized logic in `formatType()` sees it's a Union Type, iterates through the parts, and tells the container: "This needs either a `User` or an `Admin` service." The container then looks for those in its registry. Without this analyzer, the container wouldn't know how to handle the `|` symbol in your code.

### For Humans: What This Means (Story)

It makes the container compatible with the latest PHP features. You can use modern coding styles, and this class "Translates" them into something the container can handle.

## For Dummies

Imagine you're sorting mail.

1. **Check the Envelope**: Is this a real address? (`isInstantiable`)
2. **Translate**: The address is in French. Translate it to English so the postman understands. (`formatType`)
3. **Check for Stickers**: Does the envelope have an "URGENT" or "FRAGILE" sticker? (`#[Inject]`)
4. **Save in Log**: Write down the translation so if another letter comes to the same address, you don't have to translate it again. (`reflectionCache`)

### For Humans: What This Means (Walkthrough)

It's a "Clean and Cache" system for your code's metadata.

## How It Works (Technical)

The `ReflectionTypeAnalyzer` is a utility class focused on data transformation:

1. **Reflection Cache**: It maintains an internal array of `ReflectionClass` objects. This prevents the overhead of rebuilding reflection for the same class multiple times in a single request.
2. **Type Formatting**: The `formatType()` method is recursive. If it encounters a Union Type, it calls itself for each member. It adds the `?` prefix for nullable types. This ensures that a complex tree of types is always flattened into a predictable string.
3. **Discovery**: It provides specialized methods (`getInjectableProperties`, `getInjectableMethods`) that act as higher-level wrappers around the `#[Inject]` attribute discovery logic. It decomposes these elements into raw arrays (maps) of metadata.
4. **Enums/Interfaces**: It includes logic to correctly identify if a string is a class, an interface, or a PHP 8.1+ Enum, allowing the container to treat them appropriately.

### For Humans: What This Means (Technical)

It converts "Living PHP Objects" (Reflection) into "Static Data Maps" (Arrays/Strings). It is the translation layer between the language runtime and the container's logic.

## Architecture Role

- **Lives in**: `Features/Think/Analyze`
- **Role**: Reflection Utility and Type Normalization.
- **Consumer**: Used by `PrototypeAnalyzer` and `PropertyInjector`.

### For Humans: What This Means (Architecture)

It is the "Foundational Tool" that all other analysis classes rely on.

## Methods

### Method: reflectClass(string $className)

#### Technical Explanation: reflectClass

The primary caching method. Returns a `ReflectionClass` object, building it only if it’s missing from the internal cache.

#### For Humans: What This Means

"Get me the physical blueprint of this class."

### Method: formatType(ReflectionType $type)

#### Technical Explanation: formatType

The complex normalization engine. It flattens Union, Intersection, and Named types into a standardized string representation.

#### For Humans: What This Means

"Translate this complex PHP type into a simple name."

### Method: canResolveType(string $type)

#### Technical Explanation: canResolveType

A sanity check that determines if a given string refers to something the container CAN actually build (Classes/Interfaces).

#### For Humans: What This Means

"Is this something the container knows how to handle?"

## Risks & Trade-offs

- **Memory Usage**: Storing many `ReflectionClass` objects in the cache can consume several megabytes of memory in very large apps.
- **ReadOnly properties**: While it discovers properties, it cannot bypass the PHP engine's protection on `readonly` properties after they are initialized.

### For Humans: What This Means (Risks)

It’s a bit of a memory hog if you have thousands of classes, but it saves a massive amount of CPU time. Just remember that it’s a "Look, don't touch" tool—it can see everything, but it still has to follow PHP's rules.

## Related Files & Folders

- `PrototypeAnalyzer.php`: The "Chef" who uses these "Linguistics" tools to build class blueprints.
- `Inject.php`: The attribute that this analyzer helps find.
- `ResolutionException.php`: The error thrown when a class cannot be translated correctly.

### For Humans: What This Means (Relationships)

The **Linguist** (this class) translates the code so the **Architect** (PrototypeAnalyzer) can draw the plans.
