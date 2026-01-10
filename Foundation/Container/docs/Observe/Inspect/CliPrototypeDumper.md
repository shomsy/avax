# CliPrototypeDumper

## Quick Summary
- This file formats a `ServicePrototype` into CLI-friendly, human-readable text for debugging and inspection.
- It exists so you can “see” what the container thinks it will inject without stepping through runtime resolution.
- It removes the complexity of reading raw prototype objects by rendering them as a clean, hierarchical view.

### For Humans: What This Means (Summary)
It prints the container’s “plan” in a way your eyes can understand in a terminal.

## Terminology (MANDATORY, EXPANSIVE)
- **Prototype**: A precomputed description of how to build a service.
  - In this file: `ServicePrototype` (and `MethodPrototype`, `PropertyPrototype`, `ParameterPrototype`) are formatted.
  - Why it matters: prototypes let you debug “wiring” without actually instantiating services.
- **CLI-friendly**: Output optimized for terminals (plain text, indentation).
  - In this file: output is a list of lines with indentation.
  - Why it matters: terminal output is still the fastest debugging tool in many workflows.
- **Constructor signature**: Method name + parameter list that shows what will be injected.
  - In this file: constructor is printed via `formatMethod()`.
  - Why it matters: it tells you why a service fails to instantiate.
- **Injected properties / methods**: Non-constructor injections the container intends to perform.
  - In this file: printed as “Properties:” and “Methods:” blocks.
  - Why it matters: property/method injection can be easy to forget when debugging.

### For Humans: What This Means (Terms)
You get a readable “injection overview” instead of a pile of nested objects.

## Think of It
Think of it like a flight plan printout. The prototype is the plan; this dumper prints it in a structured way you can skim quickly.

### For Humans: What This Means (Think)
It lets you debug the plan before the flight takes off.

## Story Example
You run a `container:inspect` command and it shows a service is not instantiable and has a constructor parameter with an unknown type. Without this dumper you’d need to dig into prototype internals. With it, you immediately see the constructor signature and the list of injections and you fix the missing binding.

### For Humans: What This Means (Story)
It shortens the “why doesn’t this resolve?” loop from minutes to seconds.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You give it a `ServicePrototype`.
2. It prints the class name and whether it’s instantiable.
3. It prints constructor parameters if present.
4. It prints injected properties and injected methods.

## How It Works (Technical)
`dump()` builds an array of lines, formatting the constructor as a `MethodPrototype` when available, then iterates `injectedProperties` and `injectedMethods` and formats each. Helpers `formatMethod()`, `formatParameter()`, and `formatProperty()` produce compact signatures with type hints and default values.

### For Humans: What This Means (How)
It’s just string formatting, but organized to match how you think about injection.

## Architecture Role
- Why this file lives in `Observe/Inspect`: it’s not part of resolution; it’s part of observability and developer tooling.
- What depends on it: CLI inspection commands and debugging workflows.
- What it depends on: prototype model DTOs and (optionally) compiled prototype dumpers.
- System-level reasoning: introspection tools turn the container from “magic” into “mechanics”.

### For Humans: What This Means (Role)
If you can’t see what the container is doing, you’ll never fully trust it.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: dump(…)

#### Technical Explanation (dump)
Renders a `ServicePrototype` into a newline-delimited string with key sections.

##### For Humans: What This Means (dump)
It prints the “what will be injected” plan.

##### Parameters (dump)
- `$prototype`: The prototype to render.

##### Returns (dump)
- A formatted string.

##### Throws (dump)
- None.

##### When to Use It (dump)
- CLI inspect commands, debug output, dev tooling.

##### Common Mistakes (dump)
- Using it as a structured format; it’s meant for humans, not machines.

## Risks, Trade-offs & Recommended Practices
- Trade-off: Text output is easy to read but hard to parse.
  - Why it matters: automated tooling might need JSON instead.
  - Design stance: keep CLI output human-first; add separate JSON dumpers when needed.
  - Recommended practice: pair with a structured dumper for machine consumption.

### For Humans: What This Means (Risks)
This is for your eyes. If a script needs it, give the script a real data format.

## Related Files & Folders
- `docs_md/Features/Think/Model/ServicePrototype.md`: The model being rendered.
- `docs_md/Features/Think/Prototype/DependencyInjectionPrototypeFactory.md`: Produces prototypes to inspect.
- `docs_md/Observe/Inspect/Inspector.md`: A higher-level inspector that can include prototype summaries.

### For Humans: What This Means (Related)
Prototypes are the raw plan; this file prints them; inspectors decide what to show and when.

