# Settings

## Quick Summary

The Settings class provides a lightweight, hierarchical configuration storage system with dot notation support, enabling structured access to configuration data throughout the container ecosystem. It serves as the foundation for configuration management, allowing components to store and retrieve settings in a type-safe, organized manner. This abstraction eliminates the need for global variables or scattered configuration arrays, providing a clean API for configuration access that supports complex nested structures.

### For Humans: What This Means (Summary)

Imagine you have a bunch of settings scattered around your code like random notes on a desk. The Settings class is like a well-organized filing cabinet where everything has its proper place and you can easily find what you need. Instead of hunting through code for where you stored a particular setting, you just ask the cabinet using a clear address like "app.database.host", and it gives you exactly what you need.

## Terminology (MANDATORY, EXPANSIVE)

**Dot Notation**: A hierarchical addressing scheme using periods to separate nested configuration levels, allowing access to deeply nested values with simple string keys. In this file, methods like `get()`, `set()`, and `has()` use dot notation to navigate configuration hierarchies. It matters because it provides a flat string interface to complex nested data structures.

**Configuration Hierarchy**: A nested structure of configuration data organized in parent-child relationships, where each level can contain both values and sub-levels. In this file, the `$items` array stores this hierarchical data. It matters because it allows logical grouping of related settings without requiring complex data structures from users.

**Key Segments**: Individual components of a dot-notated key that represent navigation steps through the configuration hierarchy. In this file, the `explode('.', $key)` operation splits keys into segments for traversal. It matters because it enables the recursive navigation logic that makes dot notation work.

**Reference Assignment**: A PHP mechanism using the `&` operator to create aliases to array elements, allowing modification of nested structures. In this file, the `set()` method uses `&$target` to modify deep array structures. It matters because it enables efficient in-place updates of nested configuration without rebuilding entire structures.

**Type Safety**: The practice of ensuring configuration values maintain their intended types during storage and retrieval. In this file, `mixed` types allow flexibility while preserving type information. It matters because it prevents type-related bugs and maintains data integrity.

### For Humans: What This Means (Terms)

These are the building blocks that make configuration management work smoothly. Dot notation is like having street addresses instead of just knowing someone lives "somewhere in the city." Configuration hierarchies are like organizing your files into folders and subfolders. Key segments are the individual words in an address that guide you to the right location. Together, they create a system where you can store complex settings and retrieve them easily without getting lost in the details.

## Think of It

Picture a well-organized office filing system where every document has a precise location. The Settings class is the filing cabinet with clearly labeled drawers and folders. When you need to find the database connection settings, you don't rummage through piles of papers—you go directly to "app.database.host" in drawer "app", folder "database". The system automatically creates the right folders when you add new documents and tells you exactly where to find things when you need them.

### For Humans: What This Means (Think)

This analogy shows why Settings exists: to bring order to configuration chaos. Instead of having settings scattered throughout your code or stored in inconsistent ways, everything has a predictable home. It's like the difference between a messy desk where you can never find anything and a perfectly organized filing system where everything is exactly where it should be.

## Story Example

Before the Settings class existed, developers stored configuration in global arrays or constants scattered across files. To change a database timeout, you might have to hunt through multiple files, update magic numbers, and hope you didn't miss any references. With Settings, you store all configuration in one place with clear hierarchical organization. When you need to adjust the database timeout, you simply call `$settings->set('database.timeout', 30)` and the entire application automatically uses the new value.

### For Humans: What This Means (Story)

This story illustrates the real problem Settings solves: configuration drift and inconsistency. Without centralized management, settings become like whispered secrets passed between developers—some get the message, others don't. Settings creates a single source of truth where configuration changes are reliable and immediate across the entire system.

## For Dummies

Let's break this down like organizing a kitchen pantry:

1. **The Problem**: Ingredients (settings) are scattered everywhere—some in the fridge, some on counters, some expired and forgotten.

2. **Settings' Job**: It's a smart pantry with labeled shelves and containers, where everything has its place.

3. **How You Use It**: Instead of remembering where you put the sugar, you ask the pantry: "give me kitchen.sweets.sugar".

4. **What Happens Inside**: The pantry automatically creates shelves and containers as needed, and remembers exactly where everything goes.

5. **Why It's Helpful**: When you reorganize (change settings), everything automatically knows the new locations.

Common misconceptions:

- "It's just a wrapper around arrays" - Yes, but it adds structure, safety, and convenience that raw arrays lack.
- "It's slow for large configurations" - The dot notation traversal is optimized and typically faster than manual nested array access.
- "I can just use JSON files" - Settings provides programmatic access and modification that files alone can't offer.

### For Humans: What This Means (Dummies)

Settings isn't magic—it's smart organization. It takes the chaos of configuration management and turns it into a predictable, reliable system. You don't need to be a configuration expert; you just need to know what you want to store and where to find it later.

## How It Works (Technical)

The Settings class maintains an internal `$items` array that stores configuration data. The `get()` method traverses this array using dot-separated key segments, returning the value at the final segment or a default if not found. The `set()` method creates nested array structures as needed, using reference assignment to modify deep levels efficiently. The `has()` method performs similar traversal to check existence without retrieval. The `all()` method provides direct access to the complete configuration array.

### For Humans: What This Means (How)

Under the hood, it's like a tree where each branch can have leaves (values) or more branches (nested settings). When you ask for "app.database.host", it starts at the root, goes to the "app" branch, then "database", and finally picks the "host" leaf. If any branch doesn't exist, it either creates it (for setting) or returns a default (for getting). It's a clever way to make complex nested data feel simple and flat.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(array $items = []): void

#### Technical Explanation (Construct)

Initializes a new Settings instance with optional initial configuration data stored in the internal `$items` array.

##### For Humans: What This Means (Construct)

This is the setup method that creates your configuration storage. You can optionally pass in some initial settings when creating the Settings object.

##### Parameters (__construct)
- `array $items`: Optional initial configuration values as a nested array. If not provided, starts with empty configuration.

##### Returns (__construct)
- `void`: This constructor doesn't return anything; it just sets up the object.

##### Throws (__construct)
- None. Constructor is designed to be safe.

##### When to Use It (__construct)
- When creating a new configuration instance
- When you have initial configuration data to preload
- When setting up application configuration at startup

##### Common Mistakes (__construct)
- Passing invalid data types that aren't arrays
- Assuming the constructor validates configuration (it just stores what you give it)

### Method: get(string $key, mixed $default = null): mixed

#### Technical Explanation (Get)

Retrieves a configuration value using dot notation by traversing the nested `$items` array. Splits the key on dots and navigates through array levels, returning the found value or the provided default if the key doesn't exist.

##### For Humans: What This Means (Get)

This is how you ask for configuration values. You give it a dotted path like "database.host" and it finds that value in the nested configuration, or gives you a fallback if it doesn't exist.

##### Parameters (get)
- `string $key`: The configuration key using dot notation (e.g., "app.database.host").
- `mixed $default`: Value to return if the key doesn't exist.

##### Returns (get)
- `mixed`: The configuration value if found, otherwise the default value.

##### Throws (get)
- None. This method handles missing keys gracefully by returning defaults.

##### When to Use It (get)
- When you need to read configuration values in your application
- When implementing settings-dependent behavior
- When providing fallback values for optional configuration

##### Common Mistakes (get)
- Using empty string as key (returns default)
- Assuming nested paths exist without checking
- Not providing sensible defaults for required settings

### Method: set(string $key, mixed $value): void

#### Technical Explanation (Set)

Stores a configuration value using dot notation by creating nested array structures as needed. Uses reference assignment (&) to modify deep array levels efficiently, automatically creating intermediate arrays for new nested paths.

##### For Humans: What This Means (Set)

This is how you store configuration values. You give it a dotted path and a value, and it creates all the necessary nested structure automatically. Like organizing files into folders— it creates the folder structure as needed.

##### Parameters (set)
- `string $key`: The configuration key using dot notation (e.g., "app.database.host").
- `mixed $value`: The value to store at that key.

##### Returns (set)
- `void`: This method doesn't return anything; it just stores the value.

##### Throws (set)
- None. Setting values is designed to be safe and will create necessary structure.

##### When to Use It (set)
- When storing configuration values programmatically
- When building configuration from user input or environment
- When modifying existing configuration at runtime

##### Common Mistakes (set)
- Using empty string as key (gets ignored)
- Overwriting existing nested structures unintentionally
- Not understanding that it modifies the original Settings instance

### Method: has(string $key): bool

#### Technical Explanation (Has)

Checks for the existence of a configuration key using dot notation by traversing the nested `$items` array. Performs the same traversal logic as `get()` but only checks for key presence without retrieving values.

##### For Humans: What This Means (Has)

This lets you check if a configuration setting exists before trying to use it. Like checking if a file exists before trying to read it—prevents errors from accessing non-existent configuration.

##### Parameters (has)
- `string $key`: The configuration key to check using dot notation.

##### Returns (has)
- `bool`: True if the key exists, false otherwise.

##### Throws (has)
- None. Checking existence is designed to be safe.

##### When to Use It (has)
- When you need to conditionally use configuration
- When validating that required settings are present
- When implementing defensive configuration access

##### Common Mistakes (has)
- Using `has()` when you actually need the value (just call `get()` with a default)
- Assuming `has()` is faster than `get()` (they're similar performance)
- Not understanding that it checks the exact key path

### Method: all(): array

#### Technical Explanation (All)

Returns the complete internal configuration array, providing direct access to all stored settings as a nested array structure.

##### For Humans: What This Means (All)

This gives you access to the entire configuration as one big array. Like dumping all your files and folders onto your desk so you can see everything at once.

##### Parameters (all)
- None.

##### Returns (all)
- `array`: The complete configuration as a nested associative array.

##### Throws (all)
- None.

##### When to Use It (all)
- When you need to inspect or serialize all configuration
- When implementing configuration export/import features
- When debugging configuration state

##### Common Mistakes (all)
- Modifying the returned array directly (it won't affect the Settings instance)
- Using `all()` for performance when you only need specific values
- Assuming the structure matches your expectations (it preserves the nested structure)

## Architecture Role

Settings sits at the configuration layer of the container architecture, providing the data storage foundation that other components build upon. It doesn't make decisions about what settings are valid or how they're loaded—that responsibility belongs to higher-level configuration components. Instead, it focuses on providing reliable, efficient access to configuration data regardless of source or structure.

### For Humans: What This Means (Role)

In the container's architecture, Settings is the reliable storage unit—the hard drive that remembers everything. Other parts of the system decide what to store and how to interpret it, but Settings just makes sure the information is safely kept and quickly retrievable. It's the foundation that everything else builds on.

## Risks, Trade-offs & Recommended Practices

**Risk**: Over-reliance on dot notation can lead to deeply nested configurations that become hard to manage.

**Why it matters**: Extremely deep hierarchies can make configuration files unwieldy and error-prone.

**Design stance**: Prefer shallow to medium-depth hierarchies (3-4 levels max).

**Recommended practice**: Use logical grouping like "app.database", "app.cache", rather than "app.services.database.connections.primary".

**Risk**: Modifying the internal array directly bypasses validation and type checking.

**Why it matters**: External modifications can corrupt configuration state or introduce inconsistencies.

**Design stance**: Always use the provided API methods for configuration access.

**Recommended practice**: Keep the `$items` array private and only expose configuration through the public interface.

**Risk**: Large configuration arrays can consume significant memory.

**Why it matters**: In memory-constrained environments, configuration bloat can impact performance.

**Design stance**: Load only necessary configuration and consider lazy loading for optional settings.

**Recommended practice**: Use configuration providers that can selectively load configuration sections based on need.

### For Humans: What This Means (Risks)

Like any storage system, Settings has limits. It's great for organized configuration but can become a burden if you try to store everything in it. Think of it as a well-organized closet: perfect for your clothes, but not the place to store your entire house. Use it wisely, and it will serve you well; abuse it, and you'll create new problems.

## Related Files & Folders

**ContainerConfig**: Uses Settings to store container-wide configuration options that affect resolution behavior. You encounter it when configuring container performance or feature toggles. It depends on Settings for persistent storage of configuration values.

**Core/**: Container core components access settings through Settings instances for behavioral customization. You modify settings when tuning container operation. These components read from Settings to adapt their behavior.

**Config/**: Other configuration components in this folder may extend or wrap Settings functionality. You use these when you need advanced configuration features like file loading or validation. They build upon the basic storage provided by Settings.

### For Humans: What This Means (Related)

Settings doesn't work alone—it's part of a larger configuration ecosystem. The container's brain uses it to remember how it should behave, and other configuration tools add features like loading from files or checking values. When you adjust container settings, you're usually working with Settings indirectly through these related components.
