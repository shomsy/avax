# NullPrototypeCache

## Quick Summary

- This file defines a **"Safety Plug"**—it is a cache that does absolutely nothing.
- It exists to prevent the container from crashing when no physical storage folder is configured.
- It removes the need for "if-statements" throughout the container code by providing a valid but empty implementation of the cache interface.

### For Humans: What This Means (Summary)

This is the **Invisible Cabinet**. The container expects a cabinet (Cache) to put its blueprints in. If you don't give it a real one, it gets this invisible one. The container goes through the motions of putting things in and taking things out, but because the cabinet is invisible, everything just falls to the floor and disappears. This is perfectly fine during testing or development when you want the container to "Forget" everything anyway.

## Terminology (MANDATORY, EXPANSIVE)

- **Null Object Pattern**: A design pattern where an object does nothing instead of using a `null` value.
  - In this file: The entire `NullPrototypeCache` class.
  - Why it matters: It stops "Call to member function get() on null" errors. The code just works, even if the result is "Nothing".
- **No-Op (No Operation)**: A instruction that does nothing.
  - In this file: Methods like `set()`, `delete()`, and `clear()`.
  - Why it matters: It saves CPU cycles by immediately returning instead of trying to talk to the hard drive.
- **Statelessness**: Having no internal memory of previous actions.
  - In this file: No properties are stored in this class.
  - Why it matters: It makes the class incredibly lightweight and "Safe"—it can't get corrupted because it has no data.

### For Humans: What This Means (Terminology)

This class uses the **Null Object Pattern** (The Empty Box) to provide **No-Ops** (Doing Nothing) while maintaining **Statelessness** (No Memory).

## Think of It

Think of a **Fake Camera in a Store**:

1. **Appearance**: It looks like a real camera to everyone (The Container).
2. **Action**: It doesn't actually record anything (No persistence).
3. **Purpose**: It fulfills the requirement of "Having a camera" without the cost or complexity of a real one.

### For Humans: What This Means (Analogy)

It’s a "Place-holder". It makes the system feel complete even when a feature (Caching) is turned off.

## Story Example

You are running unit tests on your machine. You don't want the tests to create hundreds of cache files in your temporary folders. Because your test config has `cacheDir: null`, the **CacheManagerIntegration** gives the container a **NullPrototypeCache**. The container "Tries" to save its work after every test, but nothing happens. Your hard drive stays clean, and your tests run perfectly.

### For Humans: What This Means (Story)

It keeps your environment clean. It allows you to run the container in "Volatile Mode" where everything it learns is forgotten as soon as the script ends.

## For Dummies

Imagine a trash can with a hole in the bottom.

1. **Put in**: You drop a file in. It falls through the hole and is gone. (`set`)
2. **Look for**: You look inside. It's empty. (`get`)
3. **Result**: You can keep "Using" the trash can, but it never fills up.

### For Humans: What This Means (Walkthrough)

It’s the "Off Switch" for caching, but in the form of an object.

## How It Works (Technical)

`NullPrototypeCache` is a pure implementation of the `PrototypeCache` interface:

1. **Return Values**: All "Read" methods returning objects return `null`. All methods returning integers return `0`. All methods returning booleans return `false`.
2. **Side Effects**: There are zero side effects (no filesystem access, no memory writes).
3. **Memory Footprint**: Extremely low. Since it’s a `readonly` class with no properties, PHP can handle it very efficiently.

### For Humans: What This Means (Technical)

It is "Hard-coded silence". It is the fastest possible cache because it never does any work.

## Architecture Role

- **Lives in**: `Features/Think/Cache`
- **Role**: Nullable Fallback Implementation.
- **Goal**: To provide a safe default when caching is disabled.

### For Humans: What This Means (Architecture)

It is the "Safety Net" for the Intelligence Layer.

## Methods

### Method: get(string $class)

#### Technical Explanation: get

Always returns `null`.

#### For Humans: What This Means (get)

"I checked the archive, and I don't have that blueprint." (Even though I didn't actually look).

### Method: set(string $class, ServicePrototype $prototype)

#### Technical Explanation: set

Internal no-op.

#### For Humans: What This Means (set)

"I'll take that blueprint and 'save' it." (Throws it in the shredder).

### Method: has(string $class)

#### Technical Explanation: has

Always returns `false`.

#### For Humans: What This Means (has)

"Nope, never seen it."

## Risks & Trade-offs

- **Hidden Slowness**: If you accidentally use this in production, your app will be slow because it will re-analyze every class on every request, but you won't see any "Errors". Always check your logs!

### For Humans: What This Means (Risks)

"Silent Slowdown". It won't break your site, but it will make it "Less Smart" and "Less Fast" if you leave it on by mistake.

## Related Files & Folders

- `PrototypeCache.php`: The interface this class follows.
- `CacheManagerIntegration.php`: The one who decides to use this class.

### For Humans: What This Means (Relationships)

The **Interface** says what it is, and the **Integration** decides when to use this "Empty" version.
