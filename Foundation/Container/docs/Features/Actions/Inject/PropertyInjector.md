# PropertyInjector

## Quick Summary

- This file defines the specialist logic for finding a value for a single object property.
- It exists to separate the "Decision making" (what value goes here?) from the "Physical setting" (using reflection to set the variable).
- It removes the complexity of property-level dependency resolution by checking overrides, container services, and fallbacks in a predictable order.

### For Humans: What This Means (Summary)

This is the **Sourcing Agent** specifically for class variables (properties). When the `InjectDependencies` class sees a variable that needs data, it asks this specialist to find that data. The specialist checks your manual input first, then looks in the container's registry.

## Terminology (MANDATORY, EXPANSIVE)

- **Property Prototype**: A blueprint of a specific variable, containing its name, type, and whether it's required.
  - In this file: The `$property` object.
  - Why it matters: It tells the injector what "Goal" it’s trying to achieve for a single variable.
- **Property Resolution (DTO)**: A specialized "Envelope" that contains the result of an injection attempt.
  - In this file: The `PropertyResolution` class.
  - Why it matters: It distinguishes between "I found Null" and "I didn't find anything at all," which helps the container decide whether to skip the property or throw an error.
- **Type Analyzability**: Checking if a property's type (e.g., `LoggerInterface`) is something the container knows how to handle.
  - In this file: Checked via `$this->typeAnalyzer->canResolveType()`.
  - Why it matters: Prevents the container from wasting time trying to resolve types like `string` or `int` that aren't usually in the registry.
- **Owner Class**: The name of the class that the property belongs to.
  - In this file: Used for detailed error messages.
  - Why it matters: When something fails, it’s much easier to debug if the error says "Failed to inject into [MyClass]" instead of just "Failed to inject".

### For Humans: What This Means (Terminology)

The injector uses a **Prototype** (Description) of a variable, performs **Type Analysis** (Sanity check) on its type, and returns a **Property Resolution** (Result) while keeping track of the **Owner Class** (The parent).

## Think of It

Think of a **Personal Assistant filling out a form**:

1. **Form Field**: The property.
2. **Explicit Override**: You gave the assistant a sticky note with the answer. They use that immediately.
3. **Container Registry**: You didn't give a note, so the assistant looks in your digital contacts or filing cabinet for the answer.
4. **Fallback**: If they can't find it, they check if the form says "Optional" or has a default answer, and they just leave it or use that.

### For Humans: What This Means (Analogy)

The assistant (PropertyInjector) is very thorough: they check "My Bag" -> "My Files" -> "The Instructions" until they find the answer for that specific line on the form.

## Story Example

You have a `Mailer` class with a `#[Inject] private LoggerInterface $logger` property. The **PropertyInjector** is asked to find a value for `$logger`. It checks if you provided a custom logger in your `make()` call overrides. If not, it sees the type is `LoggerInterface`, so it looks in the container for a matching service. It finds the `FileLogger`, wraps it in a `PropertyResolution` object, and hands it back to the `InjectDependencies` class, which then physically "injects" it into the property.

### For Humans: What This Means (Story)

It automates the tedious work of finding and verifying dependencies for individual variables, so you don't have to write any setup code for them.

## For Dummies

Imagine you're assigning seats at a wedding.

1. **Check the V.I.P List**: Did the bride give a specific seat for this person? (`Overrides`)
2. **Check the Database**: Is this person a member of the "Family" group that has a reserved table? (`Type resolution`)
3. **Check the General Seating**: Is there a default table for everyone else? (`Default Values`)
4. **Empty Seat**: Can the seat be left empty? (`Nullable`)
5. **Problem**: If the seat MUST be filled but nobody is found, call the wedding planner. (`ResolutionException`)

### For Humans: What This Means (Walkthrough)

It's a step-by-step search for a value, from most specific to most general.

## How It Works (Technical)

The `PropertyInjector` follows a strict 5-step priority flow:

1. **Overrides**: Checks the `$overrides` array first. This allows developers to manually satisfy an injection point.
2. **Type Resolution**: If the property is typed (e.g., `interface` or `class`), it attempts to resolve that type via the container. It uses `resolveContext()` if available to ensure circular dependency guards are active.
3. **Default Values**: If the property has a hard-coded default (e.g., `private int $limit = 10`), the injector returns "Unresolved," which tells the main hydrator to "Skip this and leave the default alone."
4. **Nullability**: If the property allows nulls, it returns `null` as the resolved value.
5. **Required Check**: If the property is marked as "Required" and none of the above worked, it throws a `ResolutionException` with a detailed error message including the class and property name.

### For Humans: What This Means (Technical)

It ensures that "Implicit" settings (like defaults) are respected and that "Explicit" settings (like overrides) win. It’s the gatekeeper of what data is allowed to enter a class property.

## Architecture Role

- **Lives in**: `Features/Actions/Inject`
- **Role**: Unit-level Property Resolution Specialist.
- **Collaborator**: Used by `InjectDependencies`.

### For Humans: What This Means (Architecture)

It is the "Brain" behind property injection, while `InjectDependencies` is the "Hands".

## Methods

### Method: setContainer(ContainerInterface $container)

#### Technical Explanation: setContainer

Wires the container facade into the injector. Property types (like interfaces) are resolved using this container.

#### For Humans: What This Means

"Gives the specialist the key to the registry."

### Method: resolve(PropertyPrototype $property, array $overrides, KernelContext $context, string $ownerClass)

#### Technical Explanation: resolve

The main evaluation method. It implements the prioritized search logic for a single property and returns a `PropertyResolution` DTO.

#### For Humans: What This Means

"Find the best value for this specific variable."

## Risks & Trade-offs

- **Visibility**: This component helps set private variables, which can make testing harder if you don't use a container in your tests.
- **Performance**: Many small properties can lead to many container lookups. Grouping dependencies into the constructor is generally better for performance.

### For Humans: What This Means (Risks)

It’s a powerful shortcut, but don't over-use it. Use it for "Optional helpers" (like loggers) rather than for every single piece of data in your class.

## Related Files & Folders

- `InjectDependencies.php`: The orchestrator who actually sets the value on the object.
- `PropertyPrototype.php`: The data model of the property being analyzed.
- `PropertyResolution.php`: The result object returned by this class.

### For Humans: What This Means (Relationships)

The **Prototype** describes the variable, this **Injector** finds the value, and **InjectDependencies** performs the final injection.
