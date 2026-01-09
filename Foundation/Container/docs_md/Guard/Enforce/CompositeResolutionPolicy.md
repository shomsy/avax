# CompositeResolutionPolicy

## Quick Summary
- This file combines multiple `ResolutionPolicy` implementations into one policy that must approve as a group.
- It exists so you can enforce layered security (e.g., strict existence + allowlist + environment checks) without writing one huge policy.
- It removes the complexity of “how do I apply multiple rules?” by using the Composite pattern.

### For Humans: What This Means
It’s a checklist: every rule must say “yes” before the container proceeds.

## Terminology (MANDATORY, EXPANSIVE)
- **Composite pattern**: A way to treat a group of objects like a single object.
  - In this file: multiple policies are treated as a single `ResolutionPolicy`.
  - Why it matters: you can add/remove policies without changing the caller.
- **Sub-policy**: One policy inside the composite.
  - In this file: `$policies` holds policy instances.
  - Why it matters: each sub-policy can focus on one concern.
- **All-of semantics**: A rule where all checks must pass.
  - In this file: `isAllowed()` returns false on the first denial.
  - Why it matters: it’s easy to reason about and safe by default.

### For Humans: What This Means
Instead of one mega-rule, you stack small rules like Lego blocks.

## Think of It
Think of it like airport security: you pass ID check, then baggage scan, then boarding pass scan. One failure stops the process.

### For Humans: What This Means
Security is usually layered. This file lets you model that layering cleanly.

## Story Example
You want to resolve services only if (1) the abstract exists, (2) it’s not on a denylist, and (3) you’re in an allowed environment. You implement three small policies and combine them with `CompositeResolutionPolicy::with(...)`. Now the resolver enforces all three without caring what they are.

### For Humans: What This Means
You keep rules simple and still get strong overall behavior.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You have multiple “should we allow this?” rules.
2. This class runs them one by one.
3. If any rule says “no”, the answer is “no”.
4. If all rules say “yes”, the answer is “yes”.

## How It Works (Technical)
The constructor receives a list of policies, filters out non-`ResolutionPolicy` values, and stores them. `isAllowed()` iterates policies and returns false on the first denied policy (short-circuit). `with()` is a convenience factory for variadic policy lists.

### For Humans: What This Means
It’s a simple loop that turns many rules into one reliable decision.

## Architecture Role
- Why this file lives in `Guard/Enforce`: it’s enforcement glue, not resolution logic.
- What depends on it: secure resolvers and any “policy stack” wiring.
- What it depends on: only the `ResolutionPolicy` contract.
- System-level reasoning: composability keeps security rules maintainable over time.

### For Humans: What This Means
If you can add a rule without rewriting code, your security system stays sane.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Accepts a list of policies and stores only those that implement `ResolutionPolicy`.

##### For Humans: What This Means
It won’t crash if someone accidentally passes something that isn’t a policy—it just ignores it.

##### Parameters
- `$policies`: A list/array of policies.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- When wiring policy stacks from configuration or container bindings.

##### Common Mistakes
- Assuming non-policy entries will cause an error; they’re filtered out.

### Method: isAllowed(…)

#### Technical Explanation
Returns true only if every sub-policy returns true.

##### For Humans: What This Means
It’s “all rules must agree”.

##### Parameters
- `$abstract`: The service id or contract name being checked.

##### Returns
- `true` when all policies allow it; otherwise `false`.

##### Throws
- Any exception thrown by a sub-policy (if sub-policies don’t handle errors internally).

##### When to Use It
- As the single policy a resolver calls.

##### Common Mistakes
- Forgetting that ordering can matter if policies have side effects (they shouldn’t).

### Method: with(…)

#### Technical Explanation
Convenience factory for creating a composite policy from variadic policies.

##### For Humans: What This Means
It’s a nicer way to build the composite without manually creating an array.

##### Parameters
- `$policies`: Policies to include.

##### Returns
- A `CompositeResolutionPolicy`.

##### Throws
- None.

##### When to Use It
- Inline wiring: `CompositeResolutionPolicy::with($a, $b, $c)`.

##### Common Mistakes
- Passing uninitialized policies; you’ll get filtered-out entries and a weaker composite.

## Risks, Trade-offs & Recommended Practices
- Risk: One weak policy weakens the entire enforcement story.
  - Why it matters: a composite is only as strong as its weakest member.
  - Design stance: keep sub-policies small and test them thoroughly.
  - Recommended practice: add explicit tests and configuration validation for policy stacks.
- Trade-off: “all-of” semantics can be too strict.
  - Why it matters: some scenarios need “any-of” semantics (e.g., allow if role A OR role B).
  - Design stance: prefer strict-by-default; create a separate “any-of” composite when needed.
  - Recommended practice: keep naming explicit (`AllOfPolicy`, `AnyOfPolicy`) to avoid confusion.

### For Humans: What This Means
This is strict on purpose. If you need looser rules, build a different composite and name it clearly.

## Related Files & Folders
- `docs_md/Guard/Enforce/ResolutionPolicy.md`: The contract being composed.
- `docs_md/Guard/Enforce/StrictResolutionPolicy.md`: A simple policy often used as one layer.
- `docs_md/Guard/Enforce/SecureServiceResolver.md`: A consumer of policy decisions in secure resolution.

### For Humans: What This Means
This file is the “combine rules” tool used by the rest of the guard/enforce layer.

