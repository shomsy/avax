# StrictResolutionPolicy

## Quick Summary
- This file provides a simple default `ResolutionPolicy` that can enforce “strict mode” rules (like class existence).
- It exists so you can switch the container into a “fail fast” posture when you want maximum safety.
- It removes the complexity of ad-hoc strict checks by centralizing the strict decision in one place.

### For Humans: What This Means
It’s the “no mystery meat” mode: if a class doesn’t exist, you don’t get to resolve it.

## Terminology (MANDATORY, EXPANSIVE)
- **Strict mode**: A configuration stance that prefers early failure over permissive behavior.
  - In this file: strictness is read from `ContainerPolicy`.
  - Why it matters: strict mode catches errors earlier and makes failures more predictable.
- **ContainerPolicy**: A settings object controlling guard behavior.
  - In this file: `$policy->strict` gates strict enforcement.
  - Why it matters: policies let you change behavior without rewriting code.
- **Class existence**: Whether `class_exists($abstract)` is true.
  - In this file: strict mode denies resolution when the abstract doesn’t exist as a class.
  - Why it matters: it prevents resolving typos or miswired identifiers.

### For Humans: What This Means
When strict mode is on, this policy is your “typo detector”.

## Think of It
Think of it like a compiler flag that turns warnings into errors. You might be more annoyed at first, but you stop shipping broken builds.

### For Humans: What This Means
It’s annoying only until you realize it saved you from a production outage.

## Story Example
In development, you want permissive behavior while prototyping. In production, you want strict behavior so misconfigured services fail immediately and loudly. You enable strict policy, and `StrictResolutionPolicy` denies resolving unknown classes, forcing you to fix the config instead of silently getting weird runtime behavior.

### For Humans: What This Means
It makes problems obvious and early, which is exactly what you want in production.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. Strict mode means “be picky”.
2. This policy checks if the class exists when strict mode is enabled.
3. If it doesn’t exist, it says “not allowed”.

## How It Works (Technical)
The policy reads a `ContainerPolicy` instance (injected), checks `$policy->strict`, and if strict mode is enabled it denies resolution when `class_exists($abstract)` is false. Otherwise it allows resolution.

### For Humans: What This Means
It’s a tiny rule with big consequences: fail fast instead of fail weird.

## Architecture Role
- Why this file lives in `Guard/Enforce`: it’s enforcement logic, not container resolution logic.
- What depends on it: composite policies and secure resolution wrappers.
- What it depends on: `ContainerPolicy` configuration.
- System-level reasoning: strictness should be configurable and centralized to avoid inconsistent behavior.

### For Humans: What This Means
You want one “strictness knob”, not ten scattered checks.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Injects the `ContainerPolicy` used to read strictness configuration.

##### For Humans: What This Means
It gets the rulebook it should follow.

##### Parameters
- `$policy`: Policy configuration source.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- When wiring the guard/enforce layer.

##### Common Mistakes
- Injecting a policy with strict mode enabled in environments where you expect permissive behavior.

### Method: isAllowed(…)

#### Technical Explanation
Denies resolution when strict mode is enabled and the abstract does not exist as a class.

##### For Humans: What This Means
It blocks “resolve something that isn’t real”.

##### Parameters
- `$abstract`: The requested abstract identifier (often a class name).

##### Returns
- `true` if allowed; `false` otherwise.

##### Throws
- None.

##### When to Use It
- As one layer in your policy stack.

##### Common Mistakes
- Assuming interface names will pass `class_exists()`; strict rules may need interface support depending on your design.

## Risks, Trade-offs & Recommended Practices
- Risk: Over-strict checks can block valid resolution patterns.
  - Why it matters: some containers resolve by interface or by string ids that aren’t class names.
  - Design stance: strictness is valuable, but the check must match your identifier strategy.
  - Recommended practice: if your container resolves interfaces, extend the policy to allow `interface_exists()` too (or document your conventions clearly).

### For Humans: What This Means
Strict mode should match your actual service-id conventions, otherwise it becomes a foot-gun.

## Related Files & Folders
- `docs_md/Guard/Rules/ContainerPolicy.md`: Defines the `strict` flag.
- `docs_md/Guard/Enforce/CompositeResolutionPolicy.md`: Often composes strict policy with others.

### For Humans: What This Means
This policy is just one layer. The full story is usually “strict + other rules”.

