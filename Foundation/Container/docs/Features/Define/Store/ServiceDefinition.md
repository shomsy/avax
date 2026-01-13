# ServiceDefinition

## Quick Summary

- This file defines a high-performance Data Transfer Object (DTO) that stores the "Blueprint" for a single service.
- It exists to encapsulate all registration metadata (lifetime, tags, arguments, concrete class) into a single,
  predictable structure.
- It removes the complexity of managing loose arrays of configuration by providing a typed, formal model for service
  metadata.

### For Humans: What This Means (Summary)

This is the **Instruction Manual** for one specific object in your application. It contains everything the container
needs to know: "Is it a singleton?", "Does it have special tags?", and "What specific class should I build?"

## Terminology (MANDATORY, EXPANSIVE)

- **Abstract**: The unique identifier for a service.
    - In this file: The `abstract` property set in the constructor.
    - Why it matters: It’s the "Key" you use to ask for the service (e.g., `Psr\Log\LoggerInterface`).
- **Concrete**: The actual thing to be built.
    - In this file: The `concrete` property.
    - Why it matters: It could be a class name, a function (closure), or a pre-built object.
- **Service Lifetime**: A rule defining how long an object lives.
    - In this file: The `lifetime` property (uses `ServiceLifetime` enum).
    - Why it matters: It determines if you get a new instance every time or share one forever.
- **Constructor Arguments**: Custom data to be passed into the class constructor.
    - In this file: The `arguments` array.
    - Why it matters: It allows you to "hardcode" specific settings for a class during registration.
- **Hydration (__set_state)**: Re-creating an object from a exported array.
    - In this file: The `__set_state` method.
    - Why it matters: It allows the container to be "Cachable"—PHP can save this structure to disk and load it instantly
      later.

### For Humans: What This Means (Terminology)

Everything about a service—its **Name** (Abstract), its **implementation** (Concrete), its **Longevity** (Lifetime), and
its **Extra Settings** (Arguments)—is packed into this one object.

## Think of It

Think of it like an **Order Form** at a custom furniture shop:

- **Abstract**: The item name ("The Kitchen Table").
- **Concrete**: The material used ("Oak wood").
- **Lifetime**: How many we make ("One for the showroom, or one for every customer?").
- **Arguments**: Special requests ("Must have beveled edges").

### For Humans: What This Means (Analogy)

The Order Form (this class) is what the workshop employees (the Resolver) read to know exactly what they are supposed to
build for you.

## Story Example

You want to register a `DatabaseConnection`. You create a `ServiceDefinition` for it. You set the `concrete` to
`MySqlConnection::class`, the `lifetime` to `Singleton`, and you add some `arguments` like the host and username. When
the app starts, the container picks up this "blueprint" and knows exactly how to build your database connection without
asking any more questions.

### For Humans: What This Means (Story)

It turns "Vague Ideas" about services into "Solid Plans" that the container can execute perfectly every time.

## For Dummies

Imagine a recipe card in a cookbook.

1. **Header**: What the dish is called.
2. **Ingredients**: What classes or values we need.
3. **Servings**: Whether it's a "Family Size" (shared) or "Single Serving" (new every time).
4. **Notes**: Extra instructions for the chef.

### For Humans: What This Means (Walkthrough)

If the `DefinitionStore` is the cookbook, the `ServiceDefinition` is the individual page for one single recipe.

## How It Works (Technical)

This is a pure data structure with almost no internal logic. Its only "magical" property is `__set_state()`, which is a
native PHP hook for `var_export`. This is used during the container's compilation phase to generate high-performance
static cache files. By using a formal class instead of an array, we benefit from IDE autocomplete and type safety
throughout the codebase.

### For Humans: What This Means (Technical)

It's a "Smart Box". It doesn't do anything itself; it just holds data in a very organized way so that PHP can read it
and save it to disk extremely fast.

## Architecture Role

- **Lives in**: `Features/Define/Store`
- **Role**: Data Model / Blueprint.
- **Contract**: It must be serializable and lightweight.

### For Humans: What This Means (Architecture)

It is the basic unit of information in the container system. Everything in the container revolves around creating,
storing, and following these blueprints.

## Methods

### Method: __construct(string $abstract)

#### Technical Explanation: __construct

Initializes a new blueprint with a mandatory abstract identifier. All other properties (concrete, tags, etc.) are
defaulted to their most common values (Transient, empty arrays).

#### For Humans: What This Means

Creates a new, empty blueprint with a name.

### Method: __set_state(array $array)

#### Technical Explanation: __set_state

Reconstructs a hydrated instance from a raw array. This is critical for PHP's opcode caching and `var_export`
compatibility.

#### For Humans: What This Means

The "Resurrection" method. It takes a saved list of data from a cache file and turns it back into a real PHP object.

## Risks & Trade-offs

- **Flexibility**: Because it's a DTO, adding new features to service definitions (like "Method Injection") requires
  adding new properties to this class, which can grow it over time.
- **Serialization**: Any values put into `$arguments` or `$concrete` (like complex objects or closures) must be
  serializable if the container is meant to be cached.

### For Humans: What This Means (Risks)

It's a simple object, but you have to be careful what you put in it. If you try to save something "impossible" (like a
database connection handle) into its arguments, the container won't be able to save itself to the cache.

## Related Files & Folders

- `DefinitionStore.php`: The library that holds these blueprints.
- `BindingBuilder.php`: The fluent API used to fill out these blueprints.
- `ServiceLifetime.php`: The list of options for how long a service lives.

### For Humans: What This Means (Relationships)

The **Builder** fills out the blueprint, the **Store** files it away, and the **Resolver** reads it to do the work.
