# CompiledPrototypeDumper

## Quick Summary

- This file serves as the "Freezer" for container plans.
- It exists to take the "Liquid" configuration of your app and freeze it into a solid PHP file that can be loaded
  instantly in production.
- It removes the need for **Runtime Reflection**—one of the slowest parts of any dependency injection container.

### For Humans: What This Means (Summary)

This class is the **Book Publisher**. During development, you are writing and changing your "Story" (your classes and
attributes) every minute. But when you go to production, the story is finished. This class "Prints the Book" (the
Compiled File) so that whenever someone wants to read it, they don't have to watch you write it again—they just grab the
book and start reading.

## Terminology (MANDATORY, EXPANSIVE)

- **Ahead-of-Time (AOT) Compilation**: Doing the hard work of calculation *before* the application starts (usually
  during the deployment process).
    - In this file: The result of the `dump()` method.
    - Why it matters: It shifts the performance cost from the user's request time to the developer's build time.
- **Opcache-Friendly**: Code that is designed to be easily read and remembered by PHP's built-in memory system.
    - In this file: Using `var_export()` to create a raw PHP array.
    - Why it matters: PHP can load a static array from memory much faster than it can parse a JSON file or regenerate
      reflection data.
- **Metadata Envelope**: A wrapper around the actual data that contains info about when and how the data was created.
    - In this file: The `generated_at` timestamp.
    - Why it matters: Helps developers verify if they are looking at a fresh version or an old, "Stale" version of the
      compiled container.

### For Humans: What This Means (Terminology)

The Dumper uses **AOT Compilation** (Pre-printing) to create an **Opcache-Friendly** (Fast-loading) file wrapped in a *
*Metadata Envelope** (Date-stamped wrapper).

## Think of It

Think of a **Pre-Compiled Tax Return**:

1. **Development**: You gather receipts, calculate totals, and check rules (Reflection/Analysis).
2. **Compilation (Dumper)**: You fill out the official form and print it to a PDF.
3. **Production**: When someone asks for your taxes, you just hand them the PDF. You don't have to find the receipts and
   redo the math every time.

### For Humans: What This Means (Analogy)

The Dumper saves the "Final Result" of all the container's hard work so it never has to do the "Mental Math" again.

## Story Example

You have an enterprise application with 5,000 classes. On every request, the container would spend 20ms just reflecting
classes and checking `#[Inject]` tags. That’s 20ms of wasted time for EVERY SINGLE USER. You run the *
*CompiledPrototypeDumper** as part of your `git push` script. It generates a file called `container.php`. Now, when a
user visits your site, the container loads that one file in 0.5ms. You just saved 19.5ms on every request, making your
site feel significantly faster.

### For Humans: What This Means (Story)

It turns your container into a "Zero-Cost" abstraction. You get all the benefits of clean, autowired code without any of
the performance penalties in your production environment.

## For Dummies

Imagine you're making IKEA furniture.

1. **Read**: You read the 50-page manual to figure out where the screws go. (`Analysis`)
2. **Note**: You write a 1-page "Cheat Sheet" that says "Screw A goes in Hole B". (`Dumper`)
3. **Production**: When you build the next 10 chairs, you only look at the 1-page sheet. (`Compiled Result`)

### For Humans: What This Means (Walkthrough)

It’s a "Plan-to-Snippet" tool. It converts big, complex plans into tiny, fast snippets.

## How It Works (Technical)

The `CompiledPrototypeDumper` implements a "Serialization-to-PHP" pipeline:

1. **Data Extraction**: It pulls the raw `ServiceDefinition` objects from the `DefinitionStore`.
2. **Decomposition**: It calls `toArray()` on each definition. This converts complex objects (like `ParameterPrototype`)
   into nested PHP arrays.
3. **Envelope Wrapping**: it adds a `time()` stamp so the container knows when the "Snapshot" was taken.
4. **Var Export**: It uses PHP's `var_export()` function. This is preferred over `json_encode` because the result is
   valid PHP code that can be `included`. This allows PHP to cache the data in **Opcache**, which is significantly
   faster than parsing JSON at runtime.
5. **Output**: It returns a full string starting with `<?php return [...]`.

### For Humans: What This Means (Technical)

It takes the "Live" objects in the container's memory and "Freezes" them into a static string of code. This string is
then saved to a file that the container can "Include" later to instantly restore its memory.

## Architecture Role

- **Lives in**: `Features/Think/Prototype`
- **Role**: Production Serialization Specialist.
- **Source**: `DefinitionStore`.

### For Humans: What This Means (Architecture)

It is the "Export Agent" for the container's knowledge.

## Methods

### Method: dump()

#### Technical Explanation: dump

The main execution method. Transforms the internal definition store into a static PHP payload.

#### For Humans: What This Means

"Freeze all current plans into a single, fast file."

## Risks & Trade-offs

- **Stale Data**: If you change your code but forget to "Re-dump" the container, the container will still use the old
  plans. This can lead to "Impossible Bugs" where your code looks right but acts wrong. Always automate the dumping
  process in production.
- **Deployment Complexity**: You now have a "Build Step". You can't just copy files to the server; you have to run the
  dumper too.

### For Humans: What This Means (Risks)

"Don't forget to update!" If you use this, you must make sure that every time you update your code, you also update
the "Printed Book" (the Compiled File). Otherwise, the container will be reading an old version of your story.

## Related Files & Folders

- `DefinitionStore.php`: The "Brain" that the dumper reads from.
- `ServiceDefinition.php`: The "Pages" of the book being printed.
- `CompileCommand`: The CLI tool that usually triggers this dumper.

### For Humans: What This Means (Relationships)

The **Dumper** reads the **Store** to print **Definitions** into a file.
