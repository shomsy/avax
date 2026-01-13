# ServicePrototypeFactory

## Quick Summary

- This file serves as the "Manager" for all class blueprints (prototypes).
- It exists to coordinate between the **Analyzer** (who creates plans) and the **Cache** (who remembers them), ensuring
  the container never does the same work twice.
- It removes the complexity of managing prototype lifecycles by providing a simple `createFor()` method.

### For Humans: What This Means (Summary)

This is the **Librarian** of the container's blueprints. When you need a blueprint for a class, you ask the Librarian.
They check the shelf (the Cache) first. If it’s there, they hand it to you instantly. If not, they call the Architect (
the Analyzer) to draw a new one, put a copy on the shelf, and then hand it to you.

## Terminology (MANDATORY, EXPANSIVE)

- **Cache-First Strategy**: Attempting to find data in a fast storage area before performing a "Heavy" calculation.
    - In this file: Handled by the check in `createFor()`.
    - Why it matters: This is the single most important performance optimization in the container.
- **Service Prototype**: The final, immutable "Instruction Sheet" for a class.
    - In this file: The result of `createFor()`.
    - Why it matters: It represents the complete understanding of how to build a service.
- **Delegation**: Passing a difficult task to a specialist.
    - In this file: The factory delegates exploration to the `PrototypeAnalyzer`.
    - Why it matters: Keeps the Factory simple and focused on "Orchestration" rather than "Reflecting".

### For Humans: What This Means (Terminology)

The Factory uses a **Cache-First** (Memory) plan to retrieve a **Service Prototype** (Blueprint) by **Delegating** (
Passing) the work to an Analyzer when needed.

## Think of It

Think of a **Post-it Note on a Recipe Folder**:

1. **Request**: You want to cook a "Beef Stew".
2. **Factory (Librarian)**: Looks in the folder.
3. **Cache**: Sees a Post-it note with the simplified ingredients list.
4. **Analyzer (Researcher)**: If no note is there, the librarian reads the 50-page recipe book and writes down a new
   1-page Post-it note for the future.

### For Humans: What This Means (Analogy)

The librarian makes sure you always have a "Cheat Sheet" (Prototype) ready so you don't have to read the whole book
every time you're hungry.

## Story Example

Your app is processing 1,000 requests per second. Each request needs the `UserService`. If the container had to reflect
the `UserService` class 1,000 times a second, your server would crash. Instead, the first request calls *
*ServicePrototypeFactory**. It sees the cache is empty, reflects the class, and saves the result. The next 999 requests
see the result in the cache and skip the reflection entirely. Your server stays cool, and your app stays fast.

### For Humans: What This Means (Story)

It’s the "Brain" that prevents the container from being forgetful. It ensures that hard work is only done once and then
shared across the entire application's life.

## For Dummies

Imagine you're a builder.
1.**Check the Drawer**: Do I already have the blueprints for this house? (`cache->get`)
2.**Call the Office**: If no, call the blueprint office and have them draw a new set. (`analyzer->analyze`)
3.**File the Copy**: Put a copy of the new blueprints in the drawer for next time. (`cache->set`)
4.**Build**: Use the blueprints and get to work.

### For Humans: What This Means (Walkthrough)

It's a 3-step "Check Drawer -> Make New -> Save Copy" process that ensures you're always prepared.

## How It Works (Technical)

The `ServicePrototypeFactory` implements a strict orchestration flow:

1. **Cache Lookup**: It calls `get()` on the `PrototypeCache`. The cache might return an object from memory (RAM) or
   from a compiled file on disk.
2. **Analysis**: If the cache returns `null`, it calls the `analyze()` method on the `PrototypeAnalyzer`. This triggers
   a deep dive into the class's reflection.
3. **Persistence**: The resulting `ServicePrototype` is immediately handed back to the cache via `set()`.
4. **Return**: The factory returns the same prototype instance to the caller.
   Because the factory is `readonly` and returns immutable DTOs, it is completely thread-safe and safe to use as a
   shared service.

### For Humans: What This Means (Technical)

It is the "Middleman" between the analyzer and the cache. It hides the complexity of "Knowing when to think" vs "Knowing
when to remember".

## Architecture Role

- **Lives in**: `Features/Think/Prototype`
- **Role**: Blueprint Lifecycle Orchestrator.
- **Collaborator**: `PrototypeCache`, `PrototypeAnalyzer`.

### For Humans: What This Means (Architecture)

It is the "Controller" of the Planning phase.

## Methods

### Method: createFor(string $class)

#### Technical Explanation: createFor

The primary method. Implements the cache lookup and fallback-to-analysis logic.

#### For Humans: What This Means

"Get me the blueprint for this class, quickly."

### Method: hasPrototype(string $class)

#### Technical Explanation: hasPrototype

A simple check to see if a class has already been analyzed and cached.

#### For Humans: What This Means

"Do we already have this blueprint on file?"

## Risks & Trade-offs

- **Cache Poisoning**: If the cache is corrupted or stores incorrect data, the factory will keep returning that
  incorrect data. Always ensure your cache can be cleared easily.
- **Memory Consumption**: If you cache prototypes for every single class in a massive project, the memory usage will
  grow. Use a disk-based cache for very large projects.

### For Humans: What This Means (Risks)

It’s very fast, but it’s "Stubborn"—once it remembers something, it keeps returning it until the cache is cleared.
Ensure your development environment clears the cache when you change your code!

## Related Files & Folders

- `PrototypeCache.php`: The "Drawers" where blueprints are stored.
- `PrototypeAnalyzer.php`: The "Architect" who draws new blueprints.
- `ServicePrototype.php`: The "Blueprint" itself.

### For Humans: What This Means (Relationships)

The **Factory** checks the **Cache** for a **Prototype**, falling back to the **Analyzer** if it’s missing.
