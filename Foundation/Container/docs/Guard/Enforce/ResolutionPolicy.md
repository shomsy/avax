# ResolutionPolicy

## Quick Summary
- This file defines the contract for deciding whether a service id/abstract is allowed to be resolved.
- It exists so you can plug security and governance into resolution without hardcoding rules inside the resolver.
- It removes the complexity of “where do we enforce rules?” by making policy enforcement a first-class interface.

### For Humans: What This Means (Summary)
It’s a bouncer at the door: before the container gives you a service, the policy decides if you’re allowed.

## Terminology (MANDATORY, EXPANSIVE)
- **Resolution**: The act of turning a service identifier into a concrete instance.
  - In this file: resolution is represented by the `isAllowed()` decision.
  - Why it matters: resolution is the control point where security and governance can be applied.
- **Policy**: A rule or set of rules that decide what’s permitted.
  - In this file: `ResolutionPolicy` is the interface for those rules.
  - Why it matters: policies are easier to test, swap, and compose than hardcoded conditionals.
- **Abstract**: A service identifier or contract name (often an interface/class name).
  - In this file: `$abstract` is the input key being guarded.
  - Why it matters: the container often resolves by “abstract” to implementation.

### For Humans: What This Means (Terms)
This interface is the “yes/no question” your security layer asks before doing work.

## Think of It
Think of it like a gatekeeper function in an event venue: you tell it the ticket id (abstract), it answers “let them in” or “deny”.

### For Humans: What This Means (Think)
The container shouldn’t be the place you improvise security rules. A dedicated gatekeeper makes it predictable.

## Story Example
You have a `database` service that should only be available in trusted contexts. You implement `ResolutionPolicy` that checks environment or caller context. Your resolver calls `isAllowed('database')` before returning the service. If it returns false, you deny access and log the attempt.

### For Humans: What This Means (Story)
Instead of discovering security issues in production, you put the rule in one clear place.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. A policy is just “allowed or not allowed”.
2. The container calls the policy before giving out a service.
3. You can swap policies, combine policies, and test policies easily.

## How It Works (Technical)
`ResolutionPolicy` declares a single method, `isAllowed(string $abstract): bool`. Implementations can be simple (strict class existence), complex (role-based rules), or composable (composite policy). This keeps enforcement decoupled from how services are actually resolved.

### For Humans: What This Means (How)
It’s one method that unlocks a whole security/governance layer.

## Architecture Role
- Why this file lives in `Guard/Enforce`: this is where “enforcement rules” belong, not in resolution logic.
- What depends on it: strict policies, composite policies, and any secure resolver layer.
- What it depends on: nothing—it’s intentionally minimal.
- System-level reasoning: a stable interface lets you evolve security without rewriting container internals.

### For Humans: What This Means (Role)
If you change the rules, you shouldn’t have to rewrite the whole container.

## Methods 


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: isAllowed(…)

#### Technical Explanation (isAllowed)
Returns whether resolution is permitted for the given abstract.

##### For Humans: What This Means (isAllowed)
It answers: “Can I get this service right now?”

##### Parameters (isAllowed)
- `$abstract`: The service identifier or contract name you want to resolve.

##### Returns (isAllowed)
- `true` if resolution is allowed; otherwise `false`.

##### Throws (isAllowed)
- Implementations may throw, but the interface itself does not require exceptions.

##### When to Use It (isAllowed)
- Inside resolution guards or secure resolution wrappers.

##### Common Mistakes (isAllowed)
- Treating this as an authorization system without context; many policies need request/user context provided elsewhere.

## Risks, Trade-offs & Recommended Practices
- Risk: Policies without context are often too weak.
  - Why it matters: “allowed” frequently depends on who/what is calling.
  - Design stance: keep the interface minimal, but let implementations accept injected context providers.
  - Recommended practice: implement policies as services with injected context (environment, auth, request info).

### For Humans: What This Means (Risks)
The interface is simple on purpose—but your policy implementation shouldn’t be naive.

## Related Files & Folders
- `docs_md/Guard/Enforce/CompositeResolutionPolicy.md`: Combines multiple policies into one.
- `docs_md/Guard/Enforce/StrictResolutionPolicy.md`: A basic policy with strict existence rules.
- `docs_md/Guard/Enforce/SecureServiceResolver.md`: Uses policy checks as part of secure resolution.

### For Humans: What This Means (Related)
This is the contract; other files are different ways to implement and use it.

