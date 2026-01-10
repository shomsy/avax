# ParameterPrototype

## Quick Summary

- This file defines the "Smallest Unit" of the container's knowledge—it describes one single variable in a method.
- It exists to provide the **Dependency Resolver** with all the facts about an argument (Type, Name, Default value) so it can find the right value to plug in.
- It removes the need for the container to ask PHP "What is this variable?" during every single injection.

### For Humans: What This Means (Summary)

This is the **Ingredient Tag**. If a method is a recipe, and a `MethodPrototype` is a step in that recipe, a `ParameterPrototype` is the little tag on one specific jar of spices. The tag says exactly what the spice is (Type), what it's called (Name), and what to do if you run out (Default Value).

## Terminology (MANDATORY, EXPANSIVE)

- **Type-Hint Resolution**: Using the class name in the code to find a matching service in the container.
  - In this file: The `$type` property.
  - Why it matters: This is how the container knows to give you a `Database` object when your variable says `Database $db`.
- **Variadic Flag**: A marker for parameters that accept multiple values (e.g. `...$args`).
  - In this file: The `$isVariadic` property.
  - Why it matters: Variadic parameters are special—they consume all remaining arguments. The container needs to know this so it doesn't try to fill the next variable with the same data.
- **Nullability**: Whether it’s okay to pass `null` to this variable.
  - In this file: The `$allowsNull` property.
  - Why it matters: If the container can't find a dependency, but the variable is nullable, it can just pass `null` instead of throwing an error.
- **Default Fallback**: A value to use if the container has no other way to fill the variable.
  - In this file: The `$default` value.
  - Why it matters: It makes your services more flexible. You can have optional dependencies that only get filled if you want them to.

### For Humans: What This Means (Terminology)

The Parameter Prototype tracks **Type-Hint** (What is it?), **Variadic** (Is it a list?), **Nullability** (Is null okay?), and **Default Fallback** (What is the backup plan?).

## Think of It

Think of a **Component on a Circuit Board**:

1. **Type**: "10k Ohm Resistor".
2. **Name**: "R12".
3. **Required**: "Yes" (The board won't work without it).
4. **Special**: "Optional jumper" (Default value).

### For Humans: What This Means (Analogy)

The `ParameterPrototype` describes the exact slot on the board. The `DependencyResolver` is the person who looks at the slot and picks the right component from the drawer to plug it in.

## Story Example

You have a constructor `__construct(Logger $logger, string $level = 'info')`. The container creates two **ParameterPrototypes**.

- **Prototype 1**: Sees `Logger`, knows to check the container for a service.
- **Prototype 2**: Sees `string`, sees a default value `'info'`, and knows it doesn't need to find a service unless an override is provided.
Because it had these prototypes, the container was able to build your class perfectly without needing to ask PHP redundant questions about your code's structure.

### For Humans: What This Means (Story)

It makes "Intelligent Decisions". It allows the container to be smart enough to know when to ask for help (Services) and when to use what's already there (Defaults).

## For Dummies

Imagine you're setting up a board game.

1. **The Slot**: "Player 1 Name". (`$name`)
2. **The Piece**: "A Blue Token". (`$type`)
3. **Backup**: "If no name is given, use 'Guest'". (`$default`)
4. **Constraint**: "You MUST have a color". (`$required`)

### For Humans: What This Means (Walkthrough)

It's a "Data Slot". It describes what should go in the slot and what happens if you don't have it.

## How It Works (Technical)

The `ParameterPrototype` is the leaf-node of the metadata hierarchy:

1. **Granular Metadata**: It stores 7 distinct facts about a parameter. This data is extracted from `ReflectionParameter`.
2. **Normalization**: The `$type` is normalized into a string by the `ReflectionTypeAnalyzer` before being stored here. This ensures that even complex Union types are represented predictably.
3. **Required Check**: The `$required` flag is a pre-calculated convenience. It is `true` if `hasDefault` is false AND `allowsNull` is false. This allows the `DependencyResolver` to fail fast without checking multiple flags.
4. **Serialization**: It supports `toArray()` and `fromArray()`. This is essential because parameters are the most frequent items in a serialized container file.

### For Humans: What This Means (Technical)

It is a "Leaf Node". It’s tiny, lightweight, and contains only the facts. It is the raw data that the high-level logic uses to make complex resolution decisions.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Individual parameter metadata model.
- **Collaborator**: Contained by `MethodPrototype`.

### For Humans: What This Means (Architecture)

It is the "Atom" of the intelligence system.

## Methods

### Method: fromArray(array $data)

#### Technical Explanation: fromArray

Hydrates the parameter metadata from a flat array. Used for cache restoration.

#### For Humans: What This Means (fromArray)

"Re-read the ingredient tag from the log file."

### Method: toArray()

#### Technical Explanation: toArray

Converts the 7 metadata facts into a simple array for storage.

#### For Humans: What This Means (toArray)

"Write down the ingredient tag so we can save it."

## Risks & Trade-offs

- **Mixed Types**: If a parameter has no type-hint (`mixed`), the `$type` will be `null`. The container will have to rely solely on the variable name or a default value to figure out what to do.
- **Data Redundancy**: If you have 100 methods that all take a `Logger`, you will have 100 `ParameterPrototype` objects that are almost identical. However, the memory cost is negligible compared to the speed gain.

### For Humans: What This Means (Risks)

"Don't forget the names". If you don't give your variables clear types, the container has to do more guessing. Clear type-hints make these prototypes much more effective.

## Related Files & Folders

- `MethodPrototype.php`: The collection that holds these parameters.
- `DependencyResolver.php`: The "Agent" that reads these prototypes to fulfill dependencies.
- `ReflectionTypeAnalyzer.php`: The "Agent" that prepares the data for this model.

### For Humans: What This Means (Relationships)

The **Linguist** (TypeAnalyzer) prepares the info, the **Tag** (this class) holds the info, and the **Fulfiller** (Resolver) uses the info.
