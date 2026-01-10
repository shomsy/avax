# PrototypeRegistry

## Quick Summary

- This file serves as the **Fast-Access Memory (RAM)** for class blueprints.
- It exists to provide instant access to blueprints that have already been loaded, preventing the container from even having to read from the disk cache.
- It removes the risk of memory bloat in long-running servers by automatically "Forgetting" old blueprints when it gets too full.

### For Humans: What This Means (Summary)

This is the **Librarian's Open Desk**. When a librarian (ServicePrototypeFactory) finds a blueprint, they keep it right on their desk instead of putting it back on the shelf. This way, if someone asks for it again 2 seconds later, they can just hand it over instantly. But the desk has a limited size—if it gets too cluttered, the librarian puts the oldest, most-ignored blueprints back on the shelf (The Disk Cache) to make room for new ones.

## Terminology (MANDATORY, EXPANSIVE)

- **L1 Cache (In-Memory)**: The first and fastest layer of storage. It lives in your server's RAM.
  - In this file: The `prototypes` array.
  - Why it matters: RAM is thousands of times faster than a hard drive. Using an L1 cache is what makes the container feel "Instant".
- **LRU Eviction (Least Recently Used)**: A strategy for cleaning up memory by removing the items that haven't been touched for the longest time.
  - In this file: Handled by `enforceMemoryLimit()`.
  - Why it matters: If you run a server for a month, it might analyze 10,000 different classes. Without LRU, your server would eventually run out of memory and crash.
- **Monotonic Timestamp**: A counter that only ever goes up, used to track the order of events.
  - In this file: The `$timestamp` property.
  - Why it matters: It’s a fast way to know exactly which blueprint was used most recently without needing to ask the operating system for the real time.
- **O(1) Lookup**: A technical term meaning "Instant retrieval regardless of size".
  - In this file: PHP's internal array optimization.
  - Why it matters: Whether you have 100 or 1,000 blueprints, finding one takes the same tiny amount of time.

### For Humans: What This Means (Terminology)

The Registry uses an **L1 Cache** (RAM) with **O(1) Lookup** (Instant search) and **LRU Eviction** (Memory cleaning) powered by a **Monotonic Timestamp** (Activity tracker).

## Think of It

Think of a **Chef's Prep Station**:

1. **Prep Station (Registry)**: The small table right next to the stove.
2. **Mise en Place (Prototypes)**: The pre-cut veggies and sauces sitting on the table.
3. **Pantry (Disk Cache)**: The big room in the back where the bulk ingredients are stored.
4. **The Rule**: The table only holds 10 bowls. If you need an 11th bowl, you have to move the one you haven't touched in the longest time back to the pantry.

### For Humans: What This Means (Analogy)

The Registry is the prep station. It’s where the ingredients are "Ready to go" so the chef doesn't have to walk to the pantry for every single order.

## Story Example

You are running a high-traffic Laravel Octane or Swoole server. The application stays alive for hours. Over time, different parts of your app are used. Suddenly, a rare "Admin Report" is run. The container loads the `AdminReport` blueprint into the **PrototypeRegistry**. Millions of other requests continue to use the `ProductService` and `AuthService`. Because the `AdminReport` isn't used again, the registry eventually sees it’s the "Oldest" item and evicts it to keep the RAM usage low. The `ProductService`, however, stays "Hot" and stays in RAM forever.

### For Humans: What This Means (Story)

It makes your container "Self-Cleaning". It prioritizes the most important (most frequently used) parts of your app and ensures they are always as fast as possible, while protecting your server from running out of memory.

## For Dummies

Imagine you have a stack of books on your nightstand.

1. **Found Item**: You find a book you like and put it on the stack. (`set`)
2. **Instant Read**: You want to read it again? It's right there. (`get`)
3. **The Stack Limit**: You only allow 5 books on the nightstand. (`maxSize`)
4. **Cleaning**: To add a new book, you take the one at the very bottom of the stack and put it back in the library. (`enforceMemoryLimit`)

### For Humans: What This Means (Walkthrough)

It's an "Active Workspace" protector. It keeps what you're working on close by and cleans up the rest.

## How It Works (Technical)

The `PrototypeRegistry` is an intelligent wrapper around a standard PHP array:

1. **Fast Lookup**: It uses the class name as the array key, allowing PHP's hash-map optimization to provide near-instant retrieval.
2. **Heat Mapping**: Every time `get()` or `set()` is called, it increments a local counter (`timestamp`) and assigns it to that class name. This creates a "Paper Trail" of activity.
3. **Automatic Eviction**: Whenever a new item is added, the registry checks the total count. If it exceeds `maxSize`, it sorts the activity ledger, identifies the "Coldest" entries, and calls `unset()` on them.
4. **Stats/Introspection**: It provides a `getStats()` method that returns the "Utilization Percentage", which is vital for DevOps engineers to know if they need to increase the memory limit.

### For Humans: What This Means (Technical)

It is a "Tracked Array". It doesn't just store data; it stores the "Usage History" of that data so it can make smart decisions about what to keep in RAM.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: High-Performance In-Memory Cache (L1).
- **Collaborator**: Used by `ServicePrototypeFactory`.

### For Humans: What This Means (Architecture)

It is the "Front Line" of the Intelligence Layer's performance system.

## Methods

### Method: get(string $class)

#### Technical Explanation: get

Retrieves a prototype and updates its LRU position (makes it "Hot").

#### For Humans: What This Means

"Grab a blueprint from the desk and move it to the front of the pile."

### Method: set(string $class, ServicePrototype $prototype)

#### Technical Explanation: set

Stores a prototype and triggers the memory limit check.

#### For Humans: What This Means

"Put a new blueprint on the desk and throw away the oldest one if the desk is full."

### Method: getStats()

#### Technical Explanation: getStats

Calculates the count vs. maxSize ratio to provide a utilization metric.

#### For Humans: What This Means

"Tell me how full the desk is."

## Risks & Trade-offs

- **Static Persistence**: In a standard FPM request (where the script dies at the end), this registry is wiped every time. Its true power is only realized in long-running environments like Swoole or RoadRunner.
- **Sorting Overhead**: If you set the `maxSize` to 10,000, calling `asort()` on every insertion might eventually cause a small performance dip. Keep your `maxSize` at a reasonable level (1,000 - 2,000).

### For Humans: What This Means (Risks)

"It's only as good as your server". If your server restarts on every page load, this class won't help much. Its "Magic" happens when your application stays alive for a long time.

## Related Files & Folders

- `ServicePrototypeFactory.php`: The "Manager" who talks to this registry.
- `PrototypeCache.php`: The "Backup" (L2 cache) where prototypes go when they are evicted from here.
- `ServicePrototype.php`: The objects being stored.

### For Humans: What This Means (Relationships)

The **Manager** (Factory) checks the **Desk** (this class) first, then checks the **Shelf** (Cache) if the desk is empty.
