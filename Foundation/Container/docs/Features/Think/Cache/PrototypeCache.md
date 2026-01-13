# PrototypeCache

## Quick Summary

- This file defines the **Interface for Persistent Memory**—it explains how the container should save and load class
  blueprints.
- It exists to decouple the storage logic (Files, Redis, etc.) from the logic that creates the blueprints.
- It removes the need for repeated code analysis (Reflection), making the application significantly faster.

### For Humans: What This Means (Summary)

This is the **Filing Protocol**. It’s a list of rules for how to put a blueprint into a folder and how to find it again.
It doesn't tell the container WHERE to store them (that's what the implementation does), but it sets the "Language" used
for storing and retrieving. This way, if you decide to move your blueprints from a hard drive to a cloud database, you
don't have to change any of the container's core intelligence.

## Terminology (MANDATORY, EXPANSIVE)

- **Persistence Layer**: A way to save data so that it stays there even after the computer restarts or the script
  finishes.
    - In this file: The core purpose of the interface.
    - Why it matters: Without persistence, the container would have to "Re-learn" your entire application every single
      time a page is loaded, which is very slow.
- **Deserialization Overhead**: The "Time Cost" of turning a saved string back into a working PHP object.
    - In this file: Mentioned as a reason for `prototypeExists()`.
    - Why it matters: In high-performance apps, even the act of reading a file can be "Slow". Optimized methods like
      `prototypeExists` help avoid this work when it's not needed.
- **Atomic Operation**: A task that either finishes completely or doesn't start at all, preventing "Half-finished" or "
  Broken" data.
    - In this file: Required for `set()`.
    - Why it matters: If two people try to save a blueprint at the same exact time, an atomic operation ensures the file
      doesn't get corrupted.
- **Cache Invalidation**: The process of "Forgetting" or deleting old data when the source code changes.
    - In this file: Handled by `delete()` and `clear()`.
    - Why it matters: If you change your constructor but the container still uses the old cached blueprint, your app
      will crash.

### For Humans: What This Means (Terminology)

The Cache provides a **Persistence Layer** (Long-term memory) while avoiding **Deserialization Overhead** (Hard work) by
using **Atomic Operations** (Safe writes) and supporting **Cache Invalidation** (Clearing old memory).

## Think of It

Think of a **Set of Blueprints for a Modular Space Station**:

1. **The Drawer (PrototypeCache)**: This interface describes the drawer where the blueprints are kept. It says the
   drawer must have a "Put away" slot and a "Take out" slot.
2. **The Analyst (PrototypeAnalyzer)**: The person who draws the blueprints.
3. **The Archive (FilePrototypeCache)**: A specific physical drawer made of metal (a hard drive).

### For Humans: What This Means (Analogy)

The `PrototypeCache` is the "Interface" of the Archive. It defines the handles and buttons, but not the physical
material of the walls.

## Story Example

You have a large e-commerce site with 500 services.

1. **Request 1**: The container analyzes all 500 services. This takes 200ms of CPU time. It uses `set()` to save all 500
   blueprints to the disk.
2. **Request 2**: The container uses `get()` to load the blueprints from the disk. This takes only 5ms.
   By having this cache interface, the developer was able to swap out the "Local Disk" storage for "Shared Network
   Storage" when they decided to move the site to multiple servers, without changing a single line of code in the
   `Container` itself.

### For Humans: What This Means (Story)

It turns "Slow Intelligence" into "Fast Memory". It bridges the gap between the complex work of code analysis and the
high-speed requirements of a production server.

## For Dummies

Imagine you're solving a complex puzzle.

1. **First time**: It takes you 2 hours to learn where all the pieces go.
2. **The Sheet**: You write down exactly where every piece goes on a piece of paper. (This is `set`).
3. **Next time**: You just look at the paper. It takes 5 minutes. (This is `get`).
4. **Mistake**: You realize you wrote the wrong thing for piece #5. You erase that line. (This is `delete`).

### For Humans: What This Means (Walkthrough)

It's any "Quick Reference" sheet. It helps you avoid doing the same hard work twice.

## How It Works (Technical)

`PrototypeCache` is a standard ISP (Interface Segregation Principle) compliant contract:

1. **Key-Value Semantics**: It follows a simple `$class => $prototype` mapping.
2. **Serialization Independence**: It does not dictate HOW the prototype is turned into a string. The implementation
   handles whether to use `serialize()`, `json_encode()`, or `var_export()`.
3. **Performance Hooks**: It includes `prototypeExists()` to allow the system to check for presence without triggering
   heavy disk reads or un-pickling of objects.
4. **Management Ops**: It provides `count()` and `clear()` for administrative tasks and health monitoring.

### For Humans: What This Means (Technical)

It is a "Storage Protocol". It ensures that no matter what technology we use to save files, the rest of the container
can always find its blueprints using the same simple commands.

## Architecture Role

- **Lives in**: `Features/Think/Cache`
- **Role**: Blueprint Persistence Contract.
- **Collaborator**: Used by `ServicePrototypeFactory`.

### For Humans: What This Means (Architecture)

It is the "Archivist's Protocol" for the Intelligence Layer.

## Methods

### Method: get(string $class)

#### Technical Explanation: get

Retrieves and reconstructs a `ServicePrototype` for the given class name.

#### For Humans: What This Means (get)

"Find the saved plan for this class and give it to me."

### Method: set(string $class, ServicePrototype $prototype)

#### Technical Explanation: set

Saves a blueprint into the persistent store. Implementation must ensure atomicity.

#### For Humans: What This Means (set)

"Save this plan for later so we don't have to draw it again."

### Method: clear()

#### Technical Explanation: clear

Wipes the entire storage. Essential for development environments.

#### For Humans: What This Means (clear)

"Throw away all the saved plans and start fresh."

## Risks & Trade-offs

- **Synchronization**: If you have multiple servers, a local file cache might get out of sync. You should use a shared
  cache (like Redis) in those cases.
- **Corrupted Data**: If a server crashes while writing a file, the cache might be broken. Implementations MUST use
  atomic renames to avoid this.

### For Humans: What This Means (Risks)

"Watch out for old news". If your cache isn't cleared when you update your code, your application will be running on "
Old Rules".

## Related Files & Folders

- `FilePrototypeCache.php`: The standard way of saving these plans to files.
- `ServicePrototype.php`: The "Plan" that is being saved.
- `PrototypeAnalyzer.php`: The "Author" who writes the plans.

### For Humans: What This Means (Relationships)

The **Author** (Analyzer) creates a **Plan** (Prototype), and the **Cache** (this class) decides how it's saved.
