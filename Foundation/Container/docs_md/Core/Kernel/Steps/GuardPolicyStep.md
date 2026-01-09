# GuardPolicyStep

## Quick Summary
- Enforces security and policy rules before resolving a service.
- Delegates policy checks to `GuardResolution` and blocks resolution on violations.
- Writes policy-check metadata to the context so diagnostics can confirm enforcement occurred.

### For Humans: What This Means
It’s the bouncer at the door: before the container builds anything, it checks whether you’re allowed to resolve that service.

## Terminology
- **Policy enforcement**: Rules that decide whether a service may be resolved.
- **GuardResolution**: Component that evaluates policies for a given service identifier.
- **ErrorDTO**: Structured error result returned by guard checks.
- **ContainerException**: Exception thrown when policy violations should block resolution.

### For Humans: What This Means
Policies are the rules; GuardResolution applies them; ErrorDTO is a “not allowed” result; ContainerException is the hard stop.

## Think of It
Like airport security: you don’t get to the gate (resolution) until you pass the checkpoint (guard). If you fail, you’re stopped before you waste everyone’s time.

### For Humans: What This Means
The step prevents forbidden resolutions early and cheaply.

## Story Example
A production system forbids resolving certain internal services directly. When code tries to resolve one, `GuardPolicyStep` calls the guard, gets an `ErrorDTO`, and throws `ContainerException` with a clear message. No instance construction happens.

### For Humans: What This Means
You get a clear “not allowed” error before the container does any expensive work.

## For Dummies
1. Skip if this is an injection-target resolution (special case).
2. Ask the guard to check the service ID.
3. If the guard returns an error, throw a container exception.
4. Otherwise, mark in metadata that policy checks ran.

Common misconceptions:
- “This validates dependencies.” It validates *permission to resolve*, not correctness of dependency graphs.

### For Humans: What This Means
It’s about access control, not about building the object correctly.

## How It Works (Technical)
`__invoke` calls `GuardResolution::check`. If the result is an `ErrorDTO`, it throws `ContainerException` with the service ID and guard message. On success, it writes `policy.checked` and `policy.check_time` metadata.

### For Humans: What This Means
It asks the guard “is this allowed?” and either stops or records success.

## Architecture Role
Runs early in the kernel pipeline as a security gate. Depends on `GuardResolution` from the Guard subsystem and communicates outcomes via exceptions and metadata.

### For Humans: What This Means
It’s the security checkpoint of the container’s resolution pipeline.

## Methods


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(GuardResolution $guard)

#### Technical Explanation
Stores the guard policy evaluator used during enforcement.

##### For Humans: What This Means
Keeps the policy checker this step will call.

##### Parameters
- `GuardResolution $guard`: Guard policy evaluator.

##### Returns
- `void`

##### Throws
- None.

##### When to Use It
Constructed by the container when assembling kernel steps.

##### Common Mistakes
Injecting a guard implementation that doesn’t match your policy expectations.

### Method: __invoke(KernelContext $context)

#### Technical Explanation
Checks whether the requested service can be resolved. Throws `ContainerException` if guard reports an `ErrorDTO`. Records policy metadata on success.

##### For Humans: What This Means
If the service isn’t allowed, it stops resolution immediately.

##### Parameters
- `KernelContext $context`: Contains the service ID and metadata.

##### Returns
- `void`

##### Throws
- `ContainerException` when policy validation fails.

##### When to Use It
Executed automatically during resolution.

##### Common Mistakes
Assuming it will run for injection-target operations (it explicitly skips those).

## Risks, Trade-offs & Recommended Practices
- **Risk: Overblocking**. Too strict policies can break legitimate resolutions; test policies in CI.
- **Risk: Underblocking**. Too permissive policies can expose internals; review attack surfaces.
- **Practice: Keep error messages safe**. Don’t leak sensitive service details in exceptions.
- **Practice: Record metadata**. Use `policy.*` metadata for audit and diagnostics.

### For Humans: What This Means
Balance security with usability, don’t leak secrets in errors, and keep enforcement observable.

## Related Files & Folders
- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Guard/Enforce/GuardResolution.md`: Guard evaluator used here.
- `docs_md/Features/Core/DTO/ErrorDTO.md`: Error structure returned by the guard.
- `docs_md/Features/Core/Exceptions/ContainerException.md`: Exception thrown on policy violations.

### For Humans: What This Means
Read GuardResolution to understand the rules, and check the ErrorDTO/ContainerException docs to see how violations are represented.
