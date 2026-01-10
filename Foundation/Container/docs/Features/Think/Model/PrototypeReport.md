# PrototypeReport

## Quick Summary

- This file serves as the **Clinical Laboratory** for class blueprints.
- It exists to perform a deep medical checkup on a `ServicePrototype`, providing a detailed "Health Report" (metadata, complexity, dependencies) in a format that humans can read.
- It removes the mystery of "What is the container thinking?" by visualizing the container's internal mental models.

### For Humans: What This Means (Summary)

This is the **X-Ray Machine**. If a blueprint is a drawing of a house, this class is the machine that scans the drawing and tells you exactly how many nails, boards, and pipes you'll need, and whether the house is too complicated to build safely.

## Terminology (MANDATORY, EXPANSIVE)

- **Dependency Graph Analysis**: Mapping out exactly which class depends on which other class.
  - In this file: Handled by `extractDependencies()`.
  - Why it matters: Helps you see the "Butterfly Effect"—if you change one class, how many others will be affected?
- **Complexity Heuristics**: A rule-of-thumb calculation that scores a class based on how many dependencies it has.
  - In this file: The `calculateComplexity()` method.
  - Why it matters: If a class has 20 dependencies, it's probably "Doing too much" (violating the Single Responsibility Principle). This score highlights that problem to you.
- **Bulk Auditing**: Inspecting the entire container at once to see global trends.
  - In this file: The `generateBulkReport()` method.
  - Why it matters: Allows you to see if your application is getting "Heavy" as a whole.
- **Serialization (to JSON)**: Converting the analysis into a text format that other tools can use.
  - In this file: The `toJson()` method.
  - Why it matters: Allows you to export your container's "Brain" into a web dashboard or a log file for later viewing.

### For Humans: What This Means (Terminology)

The Report performs **Dependency Graph Analysis** (Mapping) and calculates **Complexity Heuristics** (Difficulty scores) for **Bulk Auditing** (Full scans), which it then **Serializes to JSON** (Prints to text).

## Think of It

Think of a **Car's Diagnostic Computer**:

1. **Scanner**: The mechanic plugs a computer into the car (The Prototype).
2. **Report**: The computer shows:
    - 4 Spark Plugs (Dependencies).
    - Oil Level: Good (Instantiable).
    - complexity: High (Too many parts).
3. **Printout**: You get a piece of paper with all the data. (PrototypeReport).

### For Humans: What This Means (Analogy)

The Report doesn't fix the car; it just tells the mechanic exactly what's inside and what might be wrong.

## Story Example

You are trying to figure out why your app's startup time is slow. You run `avax container:inspect` on your `HeavyController`. The **PrototypeReport** reveals that the controller has 18 dependencies across 3 different injection methods. By looking at the "Complexity: Complex" score, you realize you should probably split that controller into smaller pieces. You make the change, and the report now shows "Complexity: Simple". Your app is now faster and easier to maintain.

### For Humans: What This Means (Story)

It gives you "Visibility". It’s like turning the lights on in a dark room—suddenly you can see every dependency and every injection point in your entire application.

## For Dummies

Imagine you're checking a shopping list.

1. **Count**: "Wow, that's 50 items!" (Complexity)
2. **Group**: "20 items are for the kitchen, 30 are for the garden". (Breakdown)
3. **Check**: "Can we actually buy these at this store?" (Instantiable)
4. **Summary**: "This is going to be an expensive trip." (toSummary)

### For Humans: What This Means (Walkthrough)

It's a "List Reviewer". It reads your blueprints and gives you a one-page summary of the facts.

## How It Works (Technical)

The `PrototypeReport` is a data-transformation utility:

1. **Extraction**: It traverses the `ServicePrototype` tree. It goes into the `MethodPrototype` and then into each `ParameterPrototype`, pulling out only the strings and booleans.
2. **Aggregation**: It builds a unique list of every class name found in the entire prototype. This is your "Dependency List".
3. **Scoring**: It applies a simple scoring rule: 0-5 points is "Simple", 6-10 is "Moderate", and 11+ is "Complex". This is based on the total count of constructor params, properties, and methods.
4. **Formatting**: It uses `json_encode` with `JSON_PRETTY_PRINT` to ensure that when it’s printed to a terminal, a human can actually read it.
5. **Caching**: It uses an internal `analysisCache` so that if you ask for the same report twice in a loop, it doesn't have to redo the calculations.

### For Humans: What This Means (Technical)

It is a "Logic-Heavy Formatter". It does the math, groups the data, and prints it in a pretty way so the researcher (The Developer) doesn't have to do it manually.

## Architecture Role

- **Lives in**: `Features/Think/Model`
- **Role**: Diagnostic Reporting and Visual Audit.
- **Collaborator**: Reads `ServicePrototype`.

### For Humans: What This Means (Architecture)

It is the "Auditor" of the Intelligence Layer.

## Methods

### Method: generateForPrototype(ServicePrototype $prototype)

#### Technical Explanation: generateForPrototype

The core engine. Performs the deep-dive analysis and returns a massive associative array of metadata.

#### For Humans: What This Means

"Give me every single detail about this one class blueprint."

### Method: calculateComplexity(ServicePrototype $prototype)

#### Technical Explanation: calculateComplexity

Calculates various injection-point counts and assigns one of three complexity labels.

#### For Humans: What This Means

"Tell me how 'Messy' or 'Clean' this class is."

### Method: toSummary(array $report)

#### Technical Explanation: toSummary

Converts a massive data array into a short, 5-line text summary for quick reading.

#### For Humans: What This Means

"Give me the 'Too Long; Didn't Read' version of the report."

## Risks & Trade-offs

- **Performance**: Generating a report for 5,000 classes at once (Bulk Report) can be slow and use a lot of memory. Use it for debugging, not in your production request path!
- **Subjective Complexity**: The "Simple/Moderate/Complex" labels are just opinions. A class with 6 dependencies might still be very clean, even if the report says it's "Moderate".

### For Humans: What This Means (Risks)

"It's just data". Use the report as a guide, not as a law. It helps you find problems, but you still have to use your brain to decide if they are actually problems.

## Related Files & Folders

- `ServicePrototype.php`: The data source for the report.
- `DesignFlow.php`: The workflow that often uses these reports to validate designs.
- `PrototypeRegistry.php`: The place where the report-generator gets its batch of blueprints.

### For Humans: What This Means (Relationships)

The **Auditor** (this class) reads the **Blueprints** to give the **Developer** a report.
