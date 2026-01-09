# ContainerWithInfrastructure

## Quick Summary
- This file defines a tiny composite object: config + cache manager + logger factory.
- It exists to bundle operational infrastructure dependencies together with configuration.
- It removes the complexity of passing multiple related values separately through bootstrap.

### For Humans: What This Means
It’s a “bundle” so you can carry config, cache, and logging together like one package.

## Terminology (MANDATORY, EXPANSIVE)
- **Composite object**: An object that groups multiple related things.
  - In this file: config + cache + logging are grouped.
  - Why it matters: fewer parameters and fewer wiring mistakes.
- **Infrastructure**: External runtime services (cache, logging) used by the container.
  - In this file: represented as `mixed` to keep it flexible.
  - Why it matters: different environments may use different implementations.

### For Humans: What This Means
This is “the kit” you bring to bootstrap: settings + tools.

## Think of It
Think of it like a toolbox plus a manual in one bag.

### For Humans: What This Means
You don’t want to carry tools in one hand and the manual in another and drop one.

## Story Example
Your bootstrap code reads a profile, creates a cache manager and logger factory, and wants to pass them around with config. Instead of passing three parameters everywhere, it passes one `ContainerWithInfrastructure`.

### For Humans: What This Means
It makes bootstrap code easier to read and harder to mess up.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

It’s just a readonly object with three public fields.

## How It Works (Technical)
This is a `final readonly` DTO storing `ContainerConfig $config` and two `mixed` infrastructure references. It has only a constructor.

### For Humans: What This Means
Nothing magical—just a neat package.

## Architecture Role
- Why it lives here: it’s an operational wiring helper.
- What depends on it: bootstrappers/integrations that want config + infrastructure together.
- What it depends on: the `ContainerConfig` DTO.
- System-level reasoning: grouping reduces parameter clutter and wiring errors.

### For Humans: What This Means
Cleaner bootstrap code means fewer subtle startup bugs.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ContainerConfig $config, mixed $cacheManager, mixed $loggerFactory)

#### Technical Explanation
Stores configuration and infrastructure references immutably.

##### For Humans: What This Means
It bundles “settings + real objects” into one container.

##### Parameters
- `ContainerConfig $config`
- `mixed $cacheManager`
- `mixed $loggerFactory`

##### Returns
- Returns nothing.

##### Throws
- No explicit exceptions.

##### When to Use It
- When you need to pass these three values together.

##### Common Mistakes
- Treating `mixed` values as guaranteed types; callers must know what they passed.

## Risks, Trade-offs & Recommended Practices
- Risk: `mixed` types reduce static safety.
  - Why it matters: wrong type passed can fail later.
  - Design stance: keep it flexible, but validate at boundaries.
  - Recommended practice: validate infrastructure types when assembling this object.

### For Humans: What This Means
Flexibility is nice, but you still need to check you packed the right tools.

## Related Files & Folders
- `docs_md/Features/Operate/Config/ContainerConfig.md`: The config held inside this composite.

### For Humans: What This Means
This class mainly exists to carry `ContainerConfig` alongside the runtime objects it needs.

